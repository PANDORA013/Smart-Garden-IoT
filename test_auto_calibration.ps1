# Test Auto-Calibration System
Write-Host "`n╔═══════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   TEST AUTO-CALIBRATION SYSTEM                ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════╝" -ForegroundColor Cyan

$deviceId = "PICO_CABAI_01"
$serverUrl = "http://192.168.18.35:8000"

function Test-Calibration {
    param(
        [int]$rawAdc,
        [int]$soilMoisture,
        [string]$scenario
    )
    
    Write-Host "`n┌─────────────────────────────────────────────┐" -ForegroundColor Yellow
    Write-Host "│ SCENARIO: $scenario" -ForegroundColor Yellow
    Write-Host "│ Raw ADC: $rawAdc" -ForegroundColor Yellow
    Write-Host "│ Soil Moisture: ${soilMoisture}%" -ForegroundColor Yellow
    Write-Host "└─────────────────────────────────────────────┘" -ForegroundColor Yellow
    
    $body = @{
        device_id = $deviceId
        temperature = 28
        soil_moisture = $soilMoisture
        raw_adc = $rawAdc
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
        $response = Invoke-RestMethod -Uri "$serverUrl/api/monitoring/insert" `
            -Method POST -ContentType "application/json" -Body $body
        
        $calib = $response.calibration_status
        
        if ($calib) {
            $validIcon = if ($calib.valid) { "✅" } else { "❌" }
            $updateIcon = if ($calib.updated) { "🔧" } else { "⏸️" }
            
            Write-Host "`n  $validIcon Valid: $($calib.valid)" -ForegroundColor $(if($calib.valid){"Green"}else{"Red"})
            Write-Host "  $updateIcon Updated: $($calib.updated)" -ForegroundColor $(if($calib.updated){"Yellow"}else{"Gray"})
            Write-Host "  📝 Message: $($calib.message)" -ForegroundColor Cyan
            if ($calib.reason) {
                Write-Host "  ⚠️  Reason: $($calib.reason)" -ForegroundColor DarkYellow
            }
            
            # Tampilkan config terbaru
            $config = $response.config
            Write-Host "`n  📊 Current Calibration:" -ForegroundColor White
            Write-Host "     ADC Min (Dry): $($config.adc_min)" -ForegroundColor Gray
            Write-Host "     ADC Max (Wet): $($config.adc_max)" -ForegroundColor Gray
        }
    } catch {
        Write-Host "`n  ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    Start-Sleep -Seconds 1
}

# Reset kalibrasi ke default dulu
Write-Host "`n[0] Reset kalibrasi ke default..." -ForegroundColor White
php artisan tinker --execute="DB::table('device_settings')->where('device_id', 'PICO_CABAI_01')->update(['sensor_min' => 4095, 'sensor_max' => 1500]); echo 'Reset done';" | Out-Null
Write-Host "   ✓ Calibration reset to: Min=4095, Max=1500" -ForegroundColor Green

# ========== TEST SCENARIOS ==========

Write-Host "`n╔═══════════════════════════════════════════════╗" -ForegroundColor Magenta
Write-Host "║   TEST 1: SENSOR DISCONNECT DETECTION        ║" -ForegroundColor Magenta
Write-Host "╚═══════════════════════════════════════════════╝" -ForegroundColor Magenta

Test-Calibration -rawAdc 50 -soilMoisture 0 -scenario "ADC Too Low (Short Circuit)"
Test-Calibration -rawAdc 65000 -soilMoisture 0 -scenario "ADC Too High (Floating Pin)"

Write-Host "`n╔═══════════════════════════════════════════════╗" -ForegroundColor Magenta
Write-Host "║   TEST 2: NORMAL OPERATION                    ║" -ForegroundColor Magenta
Write-Host "╚═══════════════════════════════════════════════╝" -ForegroundColor Magenta

Test-Calibration -rawAdc 25000 -soilMoisture 45 -scenario "Normal Reading (Mid Range)"
Test-Calibration -rawAdc 30000 -soilMoisture 35 -scenario "Normal Reading (Dry)"

Write-Host "`n╔═══════════════════════════════════════════════╗" -ForegroundColor Magenta
Write-Host "║   TEST 3: AUTO-CALIBRATION EXTENSION          ║" -ForegroundColor Magenta
Write-Host "╚═══════════════════════════════════════════════╝" -ForegroundColor Magenta

Test-Calibration -rawAdc 5000 -soilMoisture 5 -scenario "Very Dry (Should extend DRY range)"
Test-Calibration -rawAdc 500 -soilMoisture 95 -scenario "Very Wet (Should extend WET range)"

Write-Host "`n╔═══════════════════════════════════════════════╗" -ForegroundColor Magenta
Write-Host "║   TEST 4: VERIFY AUTO LOGIC SKIP              ║" -ForegroundColor Magenta
Write-Host "╚═══════════════════════════════════════════════╝" -ForegroundColor Magenta

Write-Host "`nKirim data INVALID, pastikan auto logic TIDAK kirim command..." -ForegroundColor Gray
Test-Calibration -rawAdc 65500 -soilMoisture 0 -scenario "Invalid Sensor - Check No Command Sent"

Write-Host "`n╔═══════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║              TEST COMPLETED                   ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════╝" -ForegroundColor Cyan

Write-Host "`nCek log untuk detail auto-calibration:" -ForegroundColor White
Write-Host "  Get-Content 'storage\logs\laravel.log' -Tail 30 | Where-Object {\$_ -match 'CALIB'}" -ForegroundColor Gray

Write-Host "`nCek kalibrasi final:" -ForegroundColor White
Write-Host "  php artisan tinker --execute=`"echo json_encode(\App\Models\DeviceSetting::where('device_id', 'PICO_CABAI_01')->first(['sensor_min', 'sensor_max']));`"" -ForegroundColor Gray
