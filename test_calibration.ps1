# Test Auto-Calibration System
Write-Host "`n╔════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║    TEST AUTO-CALIBRATION SYSTEM            ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════╝" -ForegroundColor Cyan

$deviceId = "PICO_CABAI_01"
$serverUrl = "http://192.168.18.35:8000"

Write-Host "`n[1] Reset kalibrasi ke default (trigger auto-calibration)..." -ForegroundColor Yellow
try {
    $resetResult = Invoke-RestMethod -Uri "$serverUrl/api/devices/$deviceId/calibrate/reset" -Method POST
    Write-Host "   ✓ $($resetResult.message)" -ForegroundColor Green
} catch {
    Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    exit
}

Write-Host "`n[2] Kirim 25 sample data dengan variasi ADC..." -ForegroundColor Yellow
Write-Host "   Simulasi: Sensor di udara (kering) = ADC tinggi (2000-2500)" -ForegroundColor Gray
Write-Host "   Simulasi: Sensor di air (basah) = ADC rendah (35000-38000)" -ForegroundColor Gray

$samples = @(
    # Dry samples (ADC tinggi)
    2000, 2100, 2050, 2200, 2150, 2300, 2250, 2100, 2000, 2400,
    # Wet samples (ADC rendah)  
    35000, 36000, 35500, 37000, 36500, 38000, 37500, 35200, 36800, 37200,
    # Medium samples
    20000, 21000, 19500, 22000, 20500
)

foreach ($i in 0..($samples.Count - 1)) {
    $adc = $samples[$i]
    
    # Hitung soil_moisture dummy (server akan re-calculate setelah kalibrasi)
    $soilMoisture = [math]::Round((($adc - 2000) / (38000 - 2000)) * 100)
    if ($soilMoisture -lt 0) { $soilMoisture = 0 }
    if ($soilMoisture -gt 100) { $soilMoisture = 100 }
    
    $body = @{
        device_id = $deviceId
        temperature = 28
        soil_moisture = $soilMoisture
        raw_adc = $adc
        relay_status = 0
        ip_address = "192.168.18.41"
        hardware_status = @{
            dht11 = $true
            soil_sensor = $true
            relay = $false
            lcd = $true
        }
    } | ConvertTo-Json -Depth 10
    
    try {
        $response = Invoke-RestMethod -Uri "$serverUrl/api/monitoring/insert" -Method POST -ContentType "application/json" -Body $body
        
        $calibStatus = $response.config.calibration_status
        $isCalibrated = $response.config.is_calibrated
        
        Write-Host "   Sample $($i+1)/25: ADC=$adc, Status=$calibStatus" -ForegroundColor $(if($isCalibrated){"Green"}else{"Yellow"})
        
        if ($isCalibrated) {
            Write-Host "`n   ✅ AUTO-CALIBRATION COMPLETED!" -ForegroundColor Green
            Write-Host "   📊 Calibrated Values:" -ForegroundColor Cyan
            Write-Host "      - sensor_min (kering): $($response.config.adc_min)" -ForegroundColor White
            Write-Host "      - sensor_max (basah):  $($response.config.adc_max)" -ForegroundColor White
            break
        }
        
        Start-Sleep -Milliseconds 100
    } catch {
        Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`n[3] Cek log Laravel untuk detail kalibrasi..." -ForegroundColor Yellow
$calibLogs = Get-Content "storage\logs\laravel.log" -Tail 10 | Where-Object {$_ -match "CALIBRATION|calibration"}
if ($calibLogs) {
    $calibLogs | ForEach-Object { Write-Host "   $_" -ForegroundColor Gray }
} else {
    Write-Host "   (Tidak ada log kalibrasi)" -ForegroundColor DarkGray
}

Write-Host "`n[4] Verifikasi nilai kalibrasi di database..." -ForegroundColor Yellow
$dbCheck = php artisan tinker --execute="echo json_encode(\App\Models\DeviceSetting::where('device_id', 'PICO_CABAI_01')->first(['sensor_min', 'sensor_max']));" | ConvertFrom-Json
Write-Host "   sensor_min (kering): $($dbCheck.sensor_min)" -ForegroundColor White
Write-Host "   sensor_max (basah):  $($dbCheck.sensor_max)" -ForegroundColor White

$range = $dbCheck.sensor_min - $dbCheck.sensor_max
Write-Host "   Range: $range" -ForegroundColor $(if($range -gt 5000){"Green"}else{"Red"})

Write-Host "`n╔════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║        TEST CALIBRATION COMPLETED          ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════╝" -ForegroundColor Cyan

Write-Host "`n📝 Manual Calibration (opsional):" -ForegroundColor White
Write-Host "   Jika ingin set manual:" -ForegroundColor Gray
Write-Host '   Invoke-RestMethod -Uri "http://192.168.18.35:8000/api/devices/PICO_CABAI_01/calibrate" `' -ForegroundColor DarkGray
Write-Host '     -Method POST -ContentType "application/json" `' -ForegroundColor DarkGray
Write-Host '     -Body ''{"adc_kering":2000,"adc_basah":35000}''' -ForegroundColor DarkGray
