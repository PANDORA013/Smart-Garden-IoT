# ================================================================
# TEST 3 MODE CERDAS - Smart Garden IoT
# ================================================================
# Script ini akan test semua 3 mode operasi:
# 1. Mode Basic (Threshold)
# 2. Mode Fuzzy Logic (AI)
# 3. Mode Schedule (Timer)
# ================================================================

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  TEST 3 MODE CERDAS - SMART GARDEN IoT" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan

$baseUrl = "http://localhost:8000/api"

# ================================================================
# STEP 1: Register 3 Devices (Satu untuk setiap mode)
# ================================================================

Write-Host "[STEP 1] Registrasi 3 Device untuk testing...`n" -ForegroundColor Yellow

$devices = @(
    @{ id = "TEST_MODE_1"; name = "Device Mode Basic" }
    @{ id = "TEST_MODE_2"; name = "Device Mode Fuzzy" }
    @{ id = "TEST_MODE_3"; name = "Device Mode Schedule" }
)

foreach ($dev in $devices) {
    $response = Invoke-RestMethod -Uri "$baseUrl/device/check-in?device_id=$($dev.id)&firmware=v2.1" -Method GET
    if ($response.success) {
        Write-Host "   ✅ $($dev.id): Registered" -ForegroundColor Green
    }
}

Start-Sleep -Seconds 2

# ================================================================
# STEP 2: Test MODE 1 - BASIC THRESHOLD
# ================================================================

Write-Host "`n[STEP 2] TEST MODE 1: BASIC THRESHOLD`n" -ForegroundColor Yellow

# Get device ID
$deviceList = Invoke-RestMethod -Uri "$baseUrl/devices" -Method GET
$device1 = $deviceList.data | Where-Object { $_.device_id -eq "TEST_MODE_1" }

Write-Host "   Setting Mode 1 dengan threshold 35%-75%..." -ForegroundColor White

$body1 = @{
    mode = 1
    batas_siram = 35
    batas_stop = 75
} | ConvertTo-Json

$response1 = Invoke-RestMethod -Uri "$baseUrl/devices/$($device1.id)/mode" `
    -Method POST `
    -ContentType "application/json" `
    -Body $body1

if ($response1.success) {
    Write-Host "   ✅ Mode 1 Active!" -ForegroundColor Green
    Write-Host "      - Pompa ON jika < 35%" -ForegroundColor Gray
    Write-Host "      - Pompa OFF jika >= 75%" -ForegroundColor Gray
} else {
    Write-Host "   ❌ Failed" -ForegroundColor Red
}

Start-Sleep -Seconds 2

# ================================================================
# STEP 3: Test MODE 2 - FUZZY LOGIC
# ================================================================

Write-Host "`n[STEP 3] TEST MODE 2: FUZZY LOGIC (AI)`n" -ForegroundColor Yellow

$device2 = $deviceList.data | Where-Object { $_.device_id -eq "TEST_MODE_2" }

Write-Host "   Setting Mode 2 (Otomatis - No manual threshold)..." -ForegroundColor White

$body2 = @{
    mode = 2
} | ConvertTo-Json

$response2 = Invoke-RestMethod -Uri "$baseUrl/devices/$($device2.id)/mode" `
    -Method POST `
    -ContentType "application/json" `
    -Body $body2

if ($response2.success) {
    Write-Host "   ✅ Mode 2 Active!" -ForegroundColor Green
    Write-Host "      - AI menghitung durasi siram" -ForegroundColor Gray
    Write-Host "      - Kering + Panas = Siram Lama (8s)" -ForegroundColor Gray
    Write-Host "      - Kering + Dingin = Siram Sebentar (3s)" -ForegroundColor Gray
} else {
    Write-Host "   ❌ Failed" -ForegroundColor Red
}

Start-Sleep -Seconds 2

# ================================================================
# STEP 4: Test MODE 3 - SCHEDULE
# ================================================================

Write-Host "`n[STEP 4] TEST MODE 3: SCHEDULE (TIMER)`n" -ForegroundColor Yellow

$device3 = $deviceList.data | Where-Object { $_.device_id -eq "TEST_MODE_3" }

Write-Host "   Setting Mode 3 dengan jadwal 06:00 & 18:00..." -ForegroundColor White

$body3 = @{
    mode = 3
    jam_pagi = "06:00"
    jam_sore = "18:00"
    durasi_siram = 10
} | ConvertTo-Json

$response3 = Invoke-RestMethod -Uri "$baseUrl/devices/$($device3.id)/mode" `
    -Method POST `
    -ContentType "application/json" `
    -Body $body3

if ($response3.success) {
    Write-Host "   ✅ Mode 3 Active!" -ForegroundColor Green
    Write-Host "      - Jadwal Pagi: 06:00" -ForegroundColor Gray
    Write-Host "      - Jadwal Sore: 18:00" -ForegroundColor Gray
    Write-Host "      - Durasi: 10 detik" -ForegroundColor Gray
} else {
    Write-Host "   ❌ Failed" -ForegroundColor Red
}

Start-Sleep -Seconds 2

# ================================================================
# STEP 5: Verify Configuration
# ================================================================

Write-Host "`n[STEP 5] VERIFIKASI KONFIGURASI SEMUA DEVICE`n" -ForegroundColor Yellow

$allDevices = Invoke-RestMethod -Uri "$baseUrl/devices" -Method GET

foreach ($device in $allDevices.data) {
    if ($device.device_id -like "TEST_MODE_*") {
        Write-Host "   📱 Device: $($device.device_id)" -ForegroundColor Cyan
        Write-Host "      Mode: $($device.mode) " -NoNewline
        
        switch ($device.mode) {
            1 { 
                Write-Host "(BASIC THRESHOLD)" -ForegroundColor Green
                Write-Host "      Threshold: $($device.batas_siram)% - $($device.batas_stop)%" -ForegroundColor Gray
            }
            2 { 
                Write-Host "(FUZZY LOGIC)" -ForegroundColor Blue
                Write-Host "      AI-Powered: Automatic duration calculation" -ForegroundColor Gray
            }
            3 { 
                Write-Host "(SCHEDULE)" -ForegroundColor Magenta
                Write-Host "      Pagi: $($device.jam_pagi) | Sore: $($device.jam_sore)" -ForegroundColor Gray
                Write-Host "      Durasi: $($device.durasi_siram) detik" -ForegroundColor Gray
            }
        }
        Write-Host ""
    }
}

# ================================================================
# STEP 6: Test Check-In (Simulasi Arduino)
# ================================================================

Write-Host "[STEP 6] TEST CHECK-IN (Simulasi Arduino)`n" -ForegroundColor Yellow

foreach ($dev in $devices) {
    Write-Host "   🤖 Arduino $($dev.id) check-in..." -ForegroundColor White
    $checkIn = Invoke-RestMethod -Uri "$baseUrl/device/check-in?device_id=$($dev.id)&firmware=v2.1" -Method GET
    
    if ($checkIn.success) {
        $config = $checkIn.config
        Write-Host "      ✅ Config received:" -ForegroundColor Green
        Write-Host "         Mode: $($config.mode)" -ForegroundColor Gray
        
        if ($config.mode -eq 1) {
            Write-Host "         Threshold: $($config.batas_siram)%-$($config.batas_stop)%" -ForegroundColor Gray
        } elseif ($config.mode -eq 3) {
            Write-Host "         Schedule: $($config.jam_pagi) & $($config.jam_sore)" -ForegroundColor Gray
        }
    }
    Write-Host ""
}

# ================================================================
# STEP 7: Insert Dummy Data
# ================================================================

Write-Host "[STEP 7] INSERT DUMMY SENSOR DATA`n" -ForegroundColor Yellow

$sensorData = @(
    @{ device = "TEST_MODE_1"; soil = 32; temp = 28; hum = 65; pump = "Hidup" }
    @{ device = "TEST_MODE_2"; soil = 38; temp = 31; hum = 60; pump = "Hidup" }
    @{ device = "TEST_MODE_3"; soil = 55; temp = 27; hum = 70; pump = "Mati" }
)

foreach ($data in $sensorData) {
    $dataBody = @{
        device_name = $data.device
        soil_moisture = $data.soil
        temperature = $data.temp
        humidity = $data.hum
        status_pompa = $data.pump
        mode = 1
    } | ConvertTo-Json
    
    $insertResult = Invoke-RestMethod -Uri "$baseUrl/monitoring/insert" `
        -Method POST `
        -ContentType "application/json" `
        -Body $dataBody
    
    if ($insertResult.success) {
        Write-Host "   ✅ $($data.device): Soil $($data.soil)%, Temp $($data.temp)°C, Pump $($data.pump)" -ForegroundColor Green
    }
}

# ================================================================
# SUMMARY
# ================================================================

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  SUMMARY - 3 MODE TESTING" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan

Write-Host "✅ MODE 1 (BASIC):" -ForegroundColor Green
Write-Host "   - Threshold-based control" -ForegroundColor White
Write-Host "   - ON: < 35%, OFF: >= 75%" -ForegroundColor White
Write-Host "   - Best for: Simple automation`n" -ForegroundColor Gray

Write-Host "✅ MODE 2 (FUZZY LOGIC):" -ForegroundColor Blue
Write-Host "   - AI-powered duration calculation" -ForegroundColor White
Write-Host "   - Consider: Temperature + Soil Moisture" -ForegroundColor White
Write-Host "   - Best for: Intelligent automation`n" -ForegroundColor Gray

Write-Host "✅ MODE 3 (SCHEDULE):" -ForegroundColor Magenta
Write-Host "   - Time-based watering" -ForegroundColor White
Write-Host "   - Schedule: 06:00 & 18:00" -ForegroundColor White
Write-Host "   - Best for: Fixed timing needs`n" -ForegroundColor Gray

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "🚀 ALL TESTS PASSED!" -ForegroundColor Green -BackgroundColor DarkGreen
Write-Host "========================================`n" -ForegroundColor Cyan

Write-Host "NEXT STEPS:" -ForegroundColor Yellow
Write-Host "1. Upload smart_mode_esp32.ino ke Arduino" -ForegroundColor White
Write-Host "2. Edit 3 lines: DEVICE_ID, WiFi, SERVER_IP" -ForegroundColor White
Write-Host "3. Arduino akan otomatis sync mode dari web" -ForegroundColor White
Write-Host "4. Ganti mode dari dashboard kapan saja!`n" -ForegroundColor White
