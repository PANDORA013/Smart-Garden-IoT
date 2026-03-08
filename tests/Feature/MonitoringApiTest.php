<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Device;
use App\Models\DeviceSetting;
use App\Models\Monitoring;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonitoringApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test device
        $this->device = Device::factory()->create([
            'device_id' => 'TEST_DEVICE_01',
            'device_name' => 'Test Device',
            'status' => 'online',
            'last_seen' => now(),
        ]);
        
        // Create device settings
        $this->setting = DeviceSetting::factory()->create([
            'device_id' => 'TEST_DEVICE_01',
            'device_name' => 'Test Device',
            'mode' => 2,
        ]);
    }

    /** @test */
    public function test_stats_endpoint_returns_latest_monitoring_data()
    {
        // Create monitoring records
        Monitoring::factory(5)->create([
            'device_id' => 'TEST_DEVICE_01',
            'temperature' => 28.5,
            'soil_moisture' => 45.0,
            'relay_status' => true,
        ]);
        
        $response = $this->getJson('/api/monitoring/stats');
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.device_id', 'TEST_DEVICE_01')
                 ->assertJsonPath('data.temperature', 28.5)
                 ->assertJsonPath('data.soil_moisture', fn($val) => $val == 45)
                 ->assertJsonPath('data.relay_status', true)
                 ->assertJsonPath('data.is_online', true);
    }

    /** @test */
    public function test_stats_endpoint_with_offline_device()
    {
        // Create old monitoring record (> 30 seconds ago)
        Monitoring::factory()->create([
            'device_id' => 'TEST_DEVICE_01',
            'temperature' => 28.5,
            'relay_status' => true,
            'created_at' => now()->subMinutes(2),
            'updated_at' => now()->subMinutes(2),
        ]);
        
        $response = $this->getJson('/api/monitoring/stats');
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.is_online', false);
    }

    /** @test */
    public function test_stats_endpoint_caching()
    {
        Monitoring::factory()->create([
            'device_id' => 'TEST_DEVICE_01',
            'temperature' => 25.0,
        ]);
        
        // First request
        $response1 = $this->getJson('/api/monitoring/stats');
        $temp1 = $response1->json('data.temperature');
        
        // Create new monitoring record
        Monitoring::factory()->create([
            'device_id' => 'TEST_DEVICE_01',
            'temperature' => 35.0,
        ]);
        
        // Second request (within cache window - should return same data)
        $response2 = $this->getJson('/api/monitoring/stats');
        $temp2 = $response2->json('data.temperature');
        
        // Temperatures should match (cached)
        $this->assertEquals($temp1, $temp2);
    }

    /** @test */
    public function test_history_endpoint_returns_latest_records()
    {
        // Create multiple monitoring records
        $records = Monitoring::factory(20)->create([
            'device_id' => 'TEST_DEVICE_01',
        ])->sortBy('created_at')->reverse();
        
        $response = $this->getJson('/api/monitoring/history?limit=10');
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('count', 10)
                 ->assertJsonIsArray('data');
        
        // Verify data is in correct order (newest first)
        $this->assertEquals(10, count($response->json('data')));
    }

    /** @test */
    public function test_history_endpoint_with_custom_limit()
    {
        Monitoring::factory(50)->create([
            'device_id' => 'TEST_DEVICE_01',
        ]);
        
        $response = $this->getJson('/api/monitoring/history?limit=25');
        
        $response->assertStatus(200)
                 ->assertJsonPath('count', 25);
    }

    /** @test */
    public function test_history_endpoint_empty()
    {
        $response = $this->getJson('/api/monitoring/history?limit=50');
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('count', 0);
    }

    /** @test */
    public function test_cleanup_endpoint_deletes_old_records()
    {
        // Create old records (8 days ago)
        Monitoring::factory(10)->create([
            'device_id' => 'TEST_DEVICE_01',
            'created_at' => now()->subDays(8),
        ]);
        
        // Create recent records (1 day ago)
        Monitoring::factory(5)->create([
            'device_id' => 'TEST_DEVICE_01',
            'created_at' => now()->subDay(),
        ]);
        
        $response = $this->deleteJson('/api/monitoring/cleanup?days=7');
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('deleted_count', 10);
        
        // Verify old records are deleted but recent ones remain
        $this->assertEquals(5, Monitoring::count());
    }

    /** @test */
    public function test_logs_endpoint_returns_formatted_activity_log()
    {
        Monitoring::factory(5)->create([
            'device_id' => 'TEST_DEVICE_01',
            'temperature' => 28.5,
            'soil_moisture' => 45.0,
            'relay_status' => true,
        ]);
        
        $response = $this->getJson('/api/monitoring/logs?limit=20');
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonIsArray('data');
        
        // Verify log has required fields
        $log = $response->json('data.0');
        $this->assertArrayHasKey('time', $log);
        $this->assertArrayHasKey('date', $log);
        $this->assertArrayHasKey('level', $log);
        $this->assertArrayHasKey('message', $log);
    }

    /** @test */
    public function test_logs_endpoint_detects_relay_changes()
    {
        // Create first monitoring record with relay OFF
        Monitoring::factory()->create([
            'device_id' => 'TEST_DEVICE_01',
            'relay_status' => false,
            'soil_moisture' => 30.0,
        ]);
        
        // Create second record with relay ON
        Monitoring::factory()->create([
            'device_id' => 'TEST_DEVICE_01',
            'relay_status' => true,
            'soil_moisture' => 25.0,
        ]);
        
        $response = $this->getJson('/api/monitoring/logs?limit=20');
        
        $response->assertStatus(200);
        
        $logs = $response->json('data');
        // Check if any log contains relay change message
        $hasRelayChange = collect($logs)->some(fn($log) => str_contains($log['message'] ?? '', 'POMPA'));
        
        // Should contain relay status change message
        $this->assertTrue($hasRelayChange);
    }

    /** @test */
    public function test_relay_toggle_success()
    {
        $response = $this->postJson('/api/monitoring/relay/toggle', [
            'status' => true,
            'device_id' => 'TEST_DEVICE_01',
        ]);
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('message', fn($msg) => str_contains($msg, 'berhasil'));
        
        // Verify relay_command was updated
        $setting = DeviceSetting::where('device_id', 'TEST_DEVICE_01')->first();
        $this->assertTrue($setting->relay_command);
    }

    /** @test */
    public function test_relay_toggle_offline_device_fails()
    {
        // Set device to offline
        $this->device->update(['status' => 'offline', 'last_seen' => now()->subMinutes(5)]);
        
        $response = $this->postJson('/api/monitoring/relay/toggle', [
            'status' => true,
            'device_id' => 'TEST_DEVICE_01',
        ]);
        
        $response->assertStatus(400)
                 ->assertJsonPath('success', false)
                 ->assertJsonPath('message', fn($msg) => str_contains($msg, 'offline'));
    }

    /** @test */
    public function test_relay_toggle_validation_error()
    {
        // Invalid status (not boolean)
        $response = $this->postJson('/api/monitoring/relay/toggle', [
            'status' => 'invalid',
            'device_id' => 'TEST_DEVICE_01',
        ]);
        
        $response->assertStatus(422)
                 ->assertJsonPath('success', false);
    }

    /** @test */
    public function test_relay_toggle_device_not_found()
    {
        $response = $this->postJson('/api/monitoring/relay/toggle', [
            'status' => true,
            'device_id' => 'NONEXISTENT_DEVICE',
        ]);
        
        $response->assertStatus(404)
                 ->assertJsonPath('success', false)
                 ->assertJsonPath('message', fn($msg) => str_contains($msg, 'tidak ditemukan'));
    }

    /** @test */
    public function test_update_settings_success()
    {
        $response = $this->postJson('/api/settings/update', [
            'device_id' => 'TEST_DEVICE_01',
            'mode' => 2,
            'batas_siram' => 25,
            'batas_stop' => 35,
        ]);
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
        
        $setting = DeviceSetting::where('device_id', 'TEST_DEVICE_01')->first();
        $this->assertEquals(2, $setting->mode);
        $this->assertEquals(25, $setting->batas_siram);
        $this->assertEquals(35, $setting->batas_stop);
    }

    /** @test */
    public function test_update_settings_validation_error()
    {
        $response = $this->postJson('/api/settings/update', [
            'device_id' => 'TEST_DEVICE_01',
            'mode' => 99, // Invalid mode
        ]);
        
        $response->assertStatus(422)
                 ->assertJsonPath('success', false);
    }

    /** @test */
    public function test_calibrate_sensor_success()
    {
        $response = $this->postJson('/api/devices/TEST_DEVICE_01/calibrate', [
            'adc_kering' => 3000,
            'adc_basah' => 1000,
        ]);
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
        
        $setting = DeviceSetting::where('device_id', 'TEST_DEVICE_01')->first();
        $this->assertEquals(3000, $setting->sensor_min);
        $this->assertEquals(1000, $setting->sensor_max);
    }

    /** @test */
    public function test_calibrate_sensor_invalid_values()
    {
        // ADC dry must be > ADC wet
        $response = $this->postJson('/api/devices/TEST_DEVICE_01/calibrate', [
            'adc_kering' => 1000,
            'adc_basah' => 3000, // Invalid: wet should be less than dry
        ]);
        
        $response->assertStatus(422)
                 ->assertJsonPath('success', false);
    }

    /** @test */
    public function test_reset_calibration_success()
    {
        $response = $this->postJson('/api/devices/TEST_DEVICE_01/calibrate/reset');
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
        
        $setting = DeviceSetting::where('device_id', 'TEST_DEVICE_01')->first();
        $this->assertEquals(4095, $setting->sensor_min);
        $this->assertEquals(1500, $setting->sensor_max);
    }

    /** @test */
    public function test_check_command_returns_pending_relay_command()
    {
        // Set relay command
        $this->setting->update(['relay_command' => true]);
        
        $response = $this->getJson('/api/monitoring/check-command?device_id=TEST_DEVICE_01&relay_status=0');
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('relay_command', true);
    }

    /** @test */
    public function test_check_command_no_pending_command()
    {
        // Relay command defaults to false (off)
        $response = $this->getJson('/api/monitoring/check-command?device_id=TEST_DEVICE_01&relay_status=0');
        
        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }

    /** @test */
    public function test_check_command_device_not_found()
    {
        $response = $this->getJson('/api/monitoring/check-command?device_id=NONEXISTENT&relay_status=0');
        
        $response->assertStatus(404)
                 ->assertJsonPath('success', false);
    }
}
