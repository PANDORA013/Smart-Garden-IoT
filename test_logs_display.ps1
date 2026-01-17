# Test script untuk melihat logs dengan perubahan status
# Simulasi perubahan relay dari OFF ke ON, kemudian kembali ke OFF

$baseUrl = "http://127.0.0.1:8000/api"

Write-Host "`n=== TEST LOGS DISPLAY - Simulasi Perubahan Status ===" -ForegroundColor Cyan
Write-Host "Kita akan simulasi: Relay OFF -> ON -> OFF" -ForegroundColor Yellow

# 1. Kirim data dengan relay OFF (soil 18% - sangat kering)
Write-Host "`n1. Kirim data: Soil 18% (kering), Relay OFF..." -ForegroundColor Green
$data1 = @{
    device_id = "PICO_CABAI_01"
    device_name = "Smart Garden Cabai"
    temperature = 31.5
    soil_moisture = 18
    relay_status = $false
    raw_adc = 38000
    ip_address = "192.168.1.100"
} | ConvertTo-Json

Invoke-RestMethod -Uri "$baseUrl/monitoring/insert" -Method Post -Body $data1 -ContentType "application/json" | Out-Null
Start-Sleep -Seconds 2

# 2. Kirim data dengan relay ON (soil 19%, pompa menyala)
Write-Host "2. Kirim data: Soil 19% (kering), Relay ON (POMPA NYALA)..." -ForegroundColor Green
$data2 = @{
    device_id = "PICO_CABAI_01"
    device_name = "Smart Garden Cabai"
    temperature = 31.5
    soil_moisture = 19
    relay_status = $true
    raw_adc = 37500
    ip_address = "192.168.1.100"
} | ConvertTo-Json

Invoke-RestMethod -Uri "$baseUrl/monitoring/insert" -Method Post -Body $data2 -ContentType "application/json" | Out-Null
Start-Sleep -Seconds 2

# 3. Kirim data dengan relay ON (soil naik karena siram)
Write-Host "3. Kirim data: Soil 25% (naik), Relay masih ON..." -ForegroundColor Green
$data3 = @{
    device_id = "PICO_CABAI_01"
    device_name = "Smart Garden Cabai"
    temperature = 31.0
    soil_moisture = 25
    relay_status = $true
    raw_adc = 32000
    ip_address = "192.168.1.100"
} | ConvertTo-Json

Invoke-RestMethod -Uri "$baseUrl/monitoring/insert" -Method Post -Body $data3 -ContentType "application/json" | Out-Null
Start-Sleep -Seconds 2

# 4. Kirim data dengan relay OFF (soil 32%, pompa mati)
Write-Host "4. Kirim data: Soil 32% (cukup lembab), Relay OFF (POMPA MATI)..." -ForegroundColor Green
$data4 = @{
    device_id = "PICO_CABAI_01"
    device_name = "Smart Garden Cabai"
    temperature = 30.5
    soil_moisture = 32
    relay_status = $false
    raw_adc = 28000
    ip_address = "192.168.1.100"
} | ConvertTo-Json

Invoke-RestMethod -Uri "$baseUrl/monitoring/insert" -Method Post -Body $data4 -ContentType "application/json" | Out-Null
Start-Sleep -Seconds 1

# 5. Ambil logs dan tampilkan
Write-Host "`n=== HASIL LOGS (5 terbaru) ===" -ForegroundColor Cyan
$logs = Invoke-RestMethod -Uri "$baseUrl/monitoring/logs?limit=5" -Method Get

Write-Host "Total logs: $($logs.count)" -ForegroundColor Yellow
Write-Host "`nLog Details:" -ForegroundColor White

foreach ($log in $logs.data) {
    $levelColor = switch ($log.level) {
        "SUCCESS" { "Green" }
        "WARN" { "Yellow" }
        "ERROR" { "Red" }
        default { "White" }
    }
    
    Write-Host "`n[$($log.time)] [$($log.level)]" -ForegroundColor $levelColor -NoNewline
    Write-Host " $($log.device)" -ForegroundColor Cyan
    Write-Host "  Message: $($log.message)" -ForegroundColor White
    if ($log.details) {
        Write-Host "  Details: $($log.details)" -ForegroundColor Gray
    }
}

Write-Host "`n=== Test Selesai! ===" -ForegroundColor Green
Write-Host "Sekarang buka dashboard di browser: http://127.0.0.1:8000" -ForegroundColor Yellow
Write-Host "Klik 'Riwayat Log' untuk melihat perubahan status!" -ForegroundColor Yellow
