# =============================================================================
# TEST API MANUAL - Kirim Data Sample ke Server
# =============================================================================
# Script sederhana untuk test API endpoint
# =============================================================================

Write-Host "`n🧪 TEST API MANUAL`n" -ForegroundColor Cyan

# Konfigurasi
$SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert"

# Data sample (ganti sesuai kebutuhan)
$data = @{
    device_id = "PICO_CABAI_01"
    temperature = 29.5
    soil_moisture = 55.0
    raw_adc = 3000
    relay_status = $true
    ip_address = "192.168.18.100"
} | ConvertTo-Json

Write-Host "📤 Mengirim data ke: $SERVER_URL" -ForegroundColor Yellow
Write-Host "Data:" -ForegroundColor Gray
Write-Host $data -ForegroundColor Gray

try {
    $response = Invoke-RestMethod -Uri $SERVER_URL -Method POST -Body $data -ContentType "application/json"
    
    Write-Host "`n✅ BERHASIL!" -ForegroundColor Green
    Write-Host "Response:" -ForegroundColor Cyan
    Write-Host ($response | ConvertTo-Json -Depth 10) -ForegroundColor Gray
    
} catch {
    Write-Host "`n❌ GAGAL!" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
