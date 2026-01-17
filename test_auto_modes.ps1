# Test Auto Logic untuk 3 Mode
Write-Host "`n╔════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║    TEST AUTO LOGIC - 3 MODE OPERASI       ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════╝" -ForegroundColor Cyan

$deviceId = "PICO_CABAI_01"
$serverUrl = "http://192.168.18.35:8000"

function Test-Mode {
    param(
        [int]$mode,
        [string]$modeName,
        [int]$soilMoisture,
        [int]$temperature = 28
    )
    
    Write-Host "`n┌─────────────────────────────────────────┐" -ForegroundColor Yellow
    Write-Host "│ TEST: $modeName (Mode $mode)" -ForegroundColor Yellow
    Write-Host "│ Soil Moisture: ${soilMoisture}%" -ForegroundColor Yellow
    Write-Host "│ Temperature: ${temperature}°C" -ForegroundColor Yellow
    Write-Host "└─────────────────────────────────────────┘" -ForegroundColor Yellow
    
    # 1. Set mode
    Write-Host "`n[1] Setting mode ke $mode..." -ForegroundColor White
    $updateMode = @{
        device_id = $deviceId
        mode = $mode
    } | ConvertTo-Json
    
    $modeResult = Invoke-RestMethod -Uri "$serverUrl/api/devices/$deviceId/mode" -Method POST -ContentType "application/json" -Body $updateMode
    Write-Host "   ✓ Mode updated: $($modeResult.message)" -ForegroundColor Green
    
    # 2. Simulasi request dari Pico W dengan soil_moisture tertentu
    Write-Host "`n[2] Simulasi Pico W kirim data..." -ForegroundColor White
    $picoData = @{
        device_id = $deviceId
        temperature = $temperature
        soil_moisture = $soilMoisture
        raw_adc = 25000
        relay_status = 0
        ip_address = "192.168.18.41"
        hardware_status = @{
            dht11 = $true
            soil_sensor = $true
            relay = $false
            lcd = $true
        }
    } | ConvertTo-Json
    
    try {
        $response = Invoke-RestMethod -Uri "$serverUrl/api/monitoring/insert" -Method POST -ContentType "application/json" -Body $picoData
        
        if ($response.relay_command -ne $null) {
            $relayStatus = if ($response.relay_command) { "ON" } else { "OFF" }
            Write-Host "   ✓ Server Response: relay_command = $relayStatus" -ForegroundColor Green
        } else {
            Write-Host "   ℹ Server Response: No relay_command (status maintained)" -ForegroundColor Cyan
        }
    } catch {
        Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    Start-Sleep -Seconds 1
}

# ========== TEST SCENARIOS ==========

Write-Host "`n╔════════════════════════════════════════════╗" -ForegroundColor Magenta
Write-Host "║   SCENARIO 1: MODE 1 - BASIC THRESHOLD    ║" -ForegroundColor Magenta
Write-Host "╚════════════════════════════════════════════╝" -ForegroundColor Magenta
Write-Host "Logika: < 20% = ON, >= 30% = OFF, 20-30% = Hysteresis" -ForegroundColor Gray

Test-Mode -mode 1 -modeName "Basic Threshold" -soilMoisture 15
Write-Host "   Expected: Pompa ON (Soil 15% < 20%)" -ForegroundColor DarkYellow

Test-Mode -mode 1 -modeName "Basic Threshold" -soilMoisture 35
Write-Host "   Expected: Pompa OFF (Soil 35% >= 30%)" -ForegroundColor DarkYellow

Test-Mode -mode 1 -modeName "Basic Threshold" -soilMoisture 25
Write-Host "   Expected: Maintain current status (Hysteresis zone)" -ForegroundColor DarkYellow

Write-Host "`n╔════════════════════════════════════════════╗" -ForegroundColor Magenta
Write-Host "║   SCENARIO 2: MODE 2 - FUZZY LOGIC AI     ║" -ForegroundColor Magenta
Write-Host "╚════════════════════════════════════════════╝" -ForegroundColor Magenta
Write-Host "Logika: < 30% = ON (Dry), 30-60% = ON (Medium), >= 60% = OFF (Wet)" -ForegroundColor Gray

Test-Mode -mode 2 -modeName "Fuzzy Logic" -soilMoisture 20 -temperature 32
Write-Host "   Expected: Pompa ON (Dry zone)" -ForegroundColor DarkYellow

Test-Mode -mode 2 -modeName "Fuzzy Logic" -soilMoisture 45 -temperature 28
Write-Host "   Expected: Pompa ON (Medium zone)" -ForegroundColor DarkYellow

Test-Mode -mode 2 -modeName "Fuzzy Logic" -soilMoisture 70 -temperature 25
Write-Host "   Expected: Pompa OFF (Wet zone)" -ForegroundColor DarkYellow

Write-Host "`n╔════════════════════════════════════════════╗" -ForegroundColor Magenta
Write-Host "║   SCENARIO 3: MODE 3 - SCHEDULE           ║" -ForegroundColor Magenta
Write-Host "╚════════════════════════════════════════════╝" -ForegroundColor Magenta
Write-Host "Logika: Siram di jam pagi/sore jika soil < 50%" -ForegroundColor Gray

$currentHour = Get-Date -Format "HH"
Write-Host "   Current Hour: $currentHour" -ForegroundColor Gray

Test-Mode -mode 3 -modeName "Schedule Mode" -soilMoisture 40
Write-Host "   Expected: Check if current time matches schedule" -ForegroundColor DarkYellow

Write-Host "`n╔════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║              TEST COMPLETED                ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════╝" -ForegroundColor Cyan

Write-Host "`nCek log Laravel untuk melihat decision logic:" -ForegroundColor White
Write-Host "  Get-Content 'storage\logs\laravel.log' -Tail 20" -ForegroundColor Gray
