<?php
/**
 * Script untuk insert dummy data testing
 * Jalankan: php insert_dummy_data.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DeviceSetting;
use App\Models\Monitoring;
use Illuminate\Support\Facades\DB;

echo "🧪 Inserting Dummy Data for Testing...\n\n";

// 1. Insert Device Setting (Device ID = 1)
echo "📱 Creating Device Setting...\n";

$device = DeviceSetting::updateOrCreate(
    ['device_id' => 'PICO_TEST_01'],
    [
        'device_name' => 'Smart Garden Test #1',
        'plant_type' => 'cabai',
        'mode' => 1, // Basic mode
        'batas_siram' => 40,
        'batas_stop' => 70,
        'jam_pagi' => '07:00:00',
        'jam_sore' => '17:00:00',
        'durasi_siram' => 5,
        'sensor_min' => 4095,
        'sensor_max' => 1500,
        'is_active' => true,
    ]
);

echo "✅ Device created: {$device->device_name} (Device ID: {$device->device_id})\n";
echo "   Mode: {$device->mode} | Batas Siram: {$device->batas_siram}% | Batas Stop: {$device->batas_stop}%\n\n";

// 2. Insert Monitoring Data (Sensor readings)
echo "📊 Creating Monitoring Data (10 records)...\n";

$now = now();
$monitoringData = [];

for ($i = 0; $i < 10; $i++) {
    $timestamp = $now->copy()->subMinutes($i * 3); // Data setiap 3 menit
    
    // Simulasi data sensor yang bervariasi
    $temperature = rand(25, 35) + (rand(0, 9) / 10); // 25.0 - 35.9°C
    $soilMoisture = rand(30, 80); // 30-80%
    $relayStatus = $soilMoisture < 40 ? 1 : 0; // Pompa ON jika < 40%
    
    $monitoringData[] = [
        'device_id' => 'PICO_TEST_01',
        'temperature' => $temperature,
        'soil_moisture' => $soilMoisture,
        'raw_adc' => rand(1500, 3500), // Nilai ADC dummy
        'relay_status' => $relayStatus,
        'ip_address' => '192.168.1.' . rand(100, 200),
        'connected_devices' => 'DHT22,Soil Sensor,Relay,Pico W',
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
    ];
}

// Insert batch
DB::table('monitorings')->insert($monitoringData);

echo "✅ Inserted 10 monitoring records\n";
echo "   Temperature range: 25-35°C\n";
echo "   Soil Moisture range: 30-80%\n";
echo "   Latest data: {$monitoringData[0]['created_at']}\n\n";

// 3. Show summary
echo "📋 Database Summary:\n";
echo "─────────────────────────────────────────────\n";

$deviceCount = DeviceSetting::count();
$monitoringCount = Monitoring::count();
$latestData = Monitoring::latest()->first();

echo "Devices: {$deviceCount}\n";
echo "Monitoring Records: {$monitoringCount}\n";

if ($latestData) {
    echo "\n📈 Latest Monitoring Data:\n";
    echo "   Device: {$latestData->device_id}\n";
    echo "   Temp: {$latestData->temperature}°C\n";
    echo "   Soil: {$latestData->soil_moisture}%\n";
    echo "   Relay: " . ($latestData->relay_status ? 'ON 🟢' : 'OFF 🔴') . "\n";
    echo "   Time: {$latestData->created_at}\n";
}

echo "\n✨ Dummy data successfully inserted!\n";
echo "🚀 You can now test the dashboard at: http://127.0.0.1:8000/\n\n";

echo "📝 Quick Test Commands:\n";
echo "─────────────────────────────────────────────\n";
echo "1. View Device: php artisan tinker\n";
echo "   >>> App\\Models\\DeviceSetting::find(1)\n\n";
echo "2. View Latest Monitoring:\n";
echo "   >>> App\\Models\\Monitoring::latest()->first()\n\n";
echo "3. View All Monitoring:\n";
echo "   >>> App\\Models\\Monitoring::orderBy('created_at', 'desc')->limit(5)->get()\n\n";
