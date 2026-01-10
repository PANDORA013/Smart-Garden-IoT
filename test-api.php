# Test API Endpoint - Simulasi Data dari Pico W
# Jalankan: php test-api.php

<?php

$serverUrl = "http://127.0.0.1:8000/api/monitoring/insert";

// Simulasi data dari Pico W
$data = [
    'device_id' => 'PICO_CABAI_01',
    'temperature' => 28.5,
    'soil_moisture' => 45.2,
    'raw_adc' => 2800,
    'relay_status' => false,
    'ip_address' => '192.168.18.100'
];

echo "🧪 Testing API Endpoint...\n";
echo "URL: $serverUrl\n";
echo "Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

// Kirim POST request
$ch = curl_init($serverUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📥 Response:\n";
echo "HTTP Code: $httpCode\n";

if ($error) {
    echo "❌ Error: $error\n";
} else {
    echo "✅ Success!\n";
    echo "Response Body:\n";
    echo json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n";
}

echo "\n🌐 Open dashboard: http://127.0.0.1:8000\n";
