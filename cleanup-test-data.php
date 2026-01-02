<?php
/**
 * Script untuk menghapus semua data test mode dari database
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DeviceSetting;
use App\Models\Monitoring;

echo "🗑️  Menghapus data test mode...\n\n";

// Hapus device settings test
$testDevices = ['TEST_MODE_1', 'TEST_MODE_2', 'TEST_MODE_3', 'AUTO_PROVISION_TEST', 'TEST_BACKWARD_COMPAT'];
$deletedSettings = DeviceSetting::whereIn('device_id', $testDevices)->delete();
echo "✅ Deleted {$deletedSettings} test device settings\n";

// Hapus monitoring data test
$testMonitoring = ['TEST_MODE_1', 'TEST_MODE_2', 'TEST_MODE_3', 'AUTO_PROVISION_TEST', 'TEST_BACKWARD_COMPAT', 'Manual Control'];
$deletedMonitoring = Monitoring::whereIn('device_name', $testMonitoring)->delete();
echo "✅ Deleted {$deletedMonitoring} test monitoring records\n";

echo "\n✨ Cleanup completed!\n";
echo "\n📊 Current database status:\n";
echo "   Device Settings: " . DeviceSetting::count() . " records\n";
echo "   Monitoring Logs: " . Monitoring::count() . " records\n";
