<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DeviceSetting;
use App\Models\Monitoring;

echo "\n========================================\n";
echo "🔍 DIAGNOSIS DATABASE\n";
echo "========================================\n\n";

// Cek DeviceSettings
$deviceCount = DeviceSetting::count();
echo "📱 Device Settings: " . $deviceCount . " devices\n";

if ($deviceCount > 0) {
    $devices = DeviceSetting::all();
    foreach ($devices as $device) {
        echo "   - " . $device->device_id . " (" . $device->device_name . ")\n";
    }
} else {
    echo "   ⚠️  Tidak ada device terdaftar!\n";
}

echo "\n";

// Cek Monitoring
$monitoringCount = Monitoring::count();
echo "📊 Monitoring Records: " . $monitoringCount . " records\n";

if ($monitoringCount > 0) {
    $latest = Monitoring::latest()->first();
    echo "   Latest Data:\n";
    echo "   - Device ID: " . $latest->device_id . "\n";
    echo "   - IP Address: " . $latest->ip_address . "\n";
    echo "   - Temperature: " . $latest->temperature . "°C\n";
    echo "   - Soil Moisture: " . $latest->soil_moisture . "%\n";
    echo "   - Pump Status: " . ($latest->relay_status ? 'ON' : 'OFF') . "\n";
    echo "   - Created At: " . $latest->created_at . "\n";
    
    // Cek unique devices in monitoring
    $uniqueDevices = Monitoring::distinct('device_id')->pluck('device_id');
    echo "\n   Unique devices in monitoring: " . $uniqueDevices->count() . "\n";
    foreach ($uniqueDevices as $devId) {
        echo "   - " . $devId . "\n";
    }
}

echo "\n========================================\n";
echo "💡 SOLUSI:\n";
echo "========================================\n";

// Cek apakah monitoring devices ada di device_settings
if ($monitoringCount > 0) {
    $uniqueDevices = Monitoring::distinct('device_id')->pluck('device_id');
    
    foreach ($uniqueDevices as $deviceId) {
        $exists = DeviceSetting::where('device_id', $deviceId)->exists();
        
        if (!$exists) {
            echo "\n⚠️  Device '$deviceId' ada di monitoring tapi tidak ada di device_settings!\n";
            echo "   Membuat auto-provisioning...\n";
            
            // Auto-provision
            $setting = DeviceSetting::create([
                'device_id' => $deviceId,
                'device_name' => $deviceId,
                'plant_type' => 'Cabai',
                'mode' => 1,
                'sensor_min' => 4095,
                'sensor_max' => 1500,
                'batas_siram' => 40,
                'batas_stop' => 70,
                'jam_pagi' => '07:00',
                'jam_sore' => '17:00',
                'durasi_siram' => 5,
                'is_active' => true,
                'last_seen' => now(),
            ]);
            
            echo "   ✅ Device '$deviceId' berhasil ditambahkan!\n";
        } else {
            echo "✅ Device '$deviceId' sudah terdaftar\n";
        }
    }
}

echo "\n========================================\n\n";
