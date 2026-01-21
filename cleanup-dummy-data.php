<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Monitoring;

echo "=== CLEAN DUMMY DATA ===\n\n";

$totalBefore = Monitoring::count();
echo "📊 Total data sebelum: {$totalBefore}\n";

// Hapus data TEST
$deletedTest = Monitoring::where('device_id', 'TEST')->delete();
echo "✅ Dummy TEST data dihapus: {$deletedTest}\n";

// Hapus SEMUA data KECUALI hari ini
$today = now()->startOfDay();
$deletedOld = Monitoring::where('created_at', '<', $today)->delete();
echo "✅ Data lama (sebelum hari ini) dihapus: {$deletedOld}\n";

$totalAfter = Monitoring::count();
$totalDeleted = $totalBefore - $totalAfter;

echo "\n📊 Total data tersisa: {$totalAfter}\n";
echo "🗑️  Total dihapus: {$totalDeleted}\n";

// Tampilkan data terbaru
echo "\n📈 Data terbaru:\n";
$latest = Monitoring::where('device_id', 'PICO_CABAI_01')
    ->latest()
    ->take(3)
    ->get(['id', 'device_id', 'raw_adc', 'temperature', 'created_at']);

foreach ($latest as $data) {
    echo "  - ID:{$data->id} | ADC:{$data->raw_adc} | Temp:{$data->temperature}°C | {$data->created_at}\n";
}

echo "\n✅ Cleanup selesai!\n";
