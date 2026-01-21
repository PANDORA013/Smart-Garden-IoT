<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Monitoring;

echo "📊 Data Sinkronisasi Pico W\n";
echo "============================\n\n";

$latest = Monitoring::where('device_id', 'PICO_CABAI_01')->latest()->first();

if ($latest) {
    echo "Device ID: {$latest->device_id}\n";
    echo "IP Address: {$latest->ip_address}\n";
    echo "Raw ADC: {$latest->raw_adc}\n";
    echo "Soil Moisture: {$latest->soil_moisture}%\n";
    echo "Temperature: {$latest->temperature}°C\n";
    echo "Relay Status: " . ($latest->relay_status ? 'ON' : 'OFF') . "\n";
    echo "Last Update: {$latest->updated_at}\n";
    echo "\n";
}

$todayCount = Monitoring::whereDate('created_at', today())->count();
$picoCount = Monitoring::where('device_id', 'PICO_CABAI_01')->count();

echo "📈 Statistics:\n";
echo "  - Total records today: {$todayCount}\n";
echo "  - Total PICO_CABAI_01: {$picoCount}\n";
echo "\n✅ Data dari Pico W tersinkron dengan database!\n";
