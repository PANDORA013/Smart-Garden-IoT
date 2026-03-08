<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Device;
use App\Models\DeviceSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RelayToggleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup test fixtures
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test device
        Device::factory()->create([
            'device_id' => 'PICO_CABAI_01',
            'status' => 'online'
        ]);
        
        // Create device setting
        DeviceSetting::factory()->create([
            'device_id' => 'PICO_CABAI_01',
            'relay_command' => false
        ]);
    }

    /**
     * Test: Toggle relay ON successfully
     */
    public function test_toggle_relay_on_when_device_online(): void
    {
        $response = $this->postJson('/api/monitoring/relay/toggle', [
            'status' => true,
            'device_id' => 'PICO_CABAI_01'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'relay_command' => true
        ]);

        // Verify database updated
        $setting = DeviceSetting::where('device_id', 'PICO_CABAI_01')->first();
        $this->assertTrue($setting->relay_command);
    }

    /**
     * Test: Toggle relay OFF successfully
     */
    public function test_toggle_relay_off_when_device_online(): void
    {
        $response = $this->postJson('/api/monitoring/relay/toggle', [
            'status' => false,
            'device_id' => 'PICO_CABAI_01'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'relay_command' => false
        ]);

        $setting = DeviceSetting::where('device_id', 'PICO_CABAI_01')->first();
        $this->assertFalse($setting->relay_command);
    }

    /**
     * Test: Cannot toggle relay when device offline
     */
    public function test_cannot_toggle_relay_when_device_offline(): void
    {
        // Update device status to offline
        Device::where('device_id', 'PICO_CABAI_01')->update(['status' => 'offline']);

        $response = $this->postJson('/api/monitoring/relay/toggle', [
            'status' => true,
            'device_id' => 'PICO_CABAI_01'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false
        ]);
    }

    /**
     * Test: Validation error - missing status
     */
    public function test_validation_error_missing_status(): void
    {
        $response = $this->postJson('/api/monitoring/relay/toggle', [
            'device_id' => 'PICO_CABAI_01'
            // status is missing
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors']);
    }

    /**
     * Test: Validation error - invalid status type
     */
    public function test_validation_error_invalid_status_type(): void
    {
        $response = $this->postJson('/api/monitoring/relay/toggle', [
            'status' => 'invalid', // Should be boolean
            'device_id' => 'PICO_CABAI_01'
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test: Device not found
     */
    public function test_device_not_found(): void
    {
        $response = $this->postJson('/api/monitoring/relay/toggle', [
            'status' => true,
            'device_id' => 'NON_EXISTENT_DEVICE'
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false
        ]);
    }
}
