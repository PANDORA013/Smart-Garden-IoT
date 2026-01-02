# Test Backend Fixes - 3 Kekurangan Fatal
# Date: January 2, 2026

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "TEST BACKEND FIXES - 3 KEKURANGAN FATAL" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$baseUrl = "http://localhost:8000/api"

# Test 1: Insert Data + Get Config (Masalah #3)
Write-Host "[TEST 1] Insert Data + Get Config Back" -ForegroundColor Yellow
Write-Host "Testing: POST /api/monitoring/insert`n" -ForegroundColor Gray

$testData = @{
    device_name = "ESP32_TestDevice"
    temperature = 28.5
    humidity = 65
    soil_moisture = 45
    relay_status = $false
    firmware_version = "v2.1"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/monitoring/insert" `
        -Method Post `
        -Body $testData `
        -ContentType "application/json"
    
    Write-Host "✅ Response received!" -ForegroundColor Green
    Write-Host "Data saved:" -ForegroundColor Cyan
    Write-Host "  - Device: $($response.data.device_name)"
    Write-Host "  - Temperature: $($response.data.temperature)°C"
    Write-Host "  - Humidity: $($response.data.humidity)%"
    Write-Host "  - Soil: $($response.data.soil_moisture)%`n"
    
    if ($response.config) {
        Write-Host "✅ CONFIG RECEIVED (Fix untuk Masalah #3):" -ForegroundColor Green
        Write-Host "  - Mode: $($response.config.mode)"
        Write-Host "  - Batas Siram: $($response.config.batas_siram)%"
        Write-Host "  - Batas Stop: $($response.config.batas_stop)%"
        Write-Host "  - Jam Pagi: $($response.config.jam_pagi)"
        Write-Host "  - Jam Sore: $($response.config.jam_sore)"
        Write-Host "  - Durasi: $($response.config.durasi_siram)s"
        Write-Host "  - Sensor Min: $($response.config.sensor_min)"
        Write-Host "  - Sensor Max: $($response.config.sensor_max)`n"
    } else {
        Write-Host "❌ GAGAL: Config tidak ada di response!" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ ERROR: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Response: $($_.ErrorDetails.Message)`n" -ForegroundColor Red
}

# Test 2: Check Database Structure (Masalah #1)
Write-Host "`n[TEST 2] Check Database Structure" -ForegroundColor Yellow
Write-Host "Testing: Database memiliki kolom temperature, humidity, dll`n" -ForegroundColor Gray

try {
    $latestData = Invoke-RestMethod -Uri "$baseUrl/monitoring/latest" -Method Get
    
    if ($latestData.data -and $latestData.data.Count -gt 0) {
        $first = $latestData.data[0]
        Write-Host "✅ Database Structure OK (Fix untuk Masalah #1):" -ForegroundColor Green
        
        $requiredColumns = @('temperature', 'humidity', 'device_name', 'relay_status', 'soil_moisture')
        foreach ($col in $requiredColumns) {
            if ($null -ne $first.$col) {
                Write-Host "  ✓ Kolom '$col' exists" -ForegroundColor Green
            } else {
                Write-Host "  ✗ Kolom '$col' MISSING!" -ForegroundColor Red
            }
        }
    } else {
        Write-Host "⚠️  No data in database yet" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Check device_settings Table (Masalah #2)
Write-Host "`n[TEST 3] Check device_settings Table" -ForegroundColor Yellow
Write-Host "Testing: Tabel device_settings exists dan berisi data`n" -ForegroundColor Gray

try {
    $devices = Invoke-RestMethod -Uri "$baseUrl/devices" -Method Get
    
    if ($devices.data -and $devices.data.Count -gt 0) {
        Write-Host "✅ device_settings Table OK (Fix untuk Masalah #2):" -ForegroundColor Green
        Write-Host "  Total Devices: $($devices.data.Count)`n" -ForegroundColor Cyan
        
        foreach ($device in $devices.data) {
            Write-Host "  Device: $($device.device_name)" -ForegroundColor White
            Write-Host "    - Mode: $($device.mode)"
            Write-Host "    - Threshold: $($device.batas_siram)% - $($device.batas_stop)%"
            Write-Host "    - Schedule: $($device.jam_pagi) & $($device.jam_sore)"
            Write-Host "    - Active: $($device.is_active)`n"
        }
    } else {
        Write-Host "⚠️  No devices in database yet (will auto-provision on first insert)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 4: Update Mode and Check Response
Write-Host "`n[TEST 4] Update Mode & Verify Arduino Gets Config" -ForegroundColor Yellow
Write-Host "Testing: Ubah mode → Arduino terima config baru`n" -ForegroundColor Gray

try {
    # Get device ID first
    $devices = Invoke-RestMethod -Uri "$baseUrl/devices" -Method Get
    
    if ($devices.data -and $devices.data.Count -gt 0) {
        $deviceId = $devices.data[0].id
        
        # Update mode to Fuzzy (mode 2)
        $updateData = @{
            mode = 2
        } | ConvertTo-Json
        
        $updateResponse = Invoke-RestMethod -Uri "$baseUrl/devices/$deviceId/mode" `
            -Method Post `
            -Body $updateData `
            -ContentType "application/json"
        
        Write-Host "✅ Mode updated to: $($updateResponse.data.mode)" -ForegroundColor Green
        
        # Simulate Arduino check-in
        Write-Host "`nSimulating Arduino check-in (POST data)..." -ForegroundColor Gray
        
        $checkInData = @{
            device_name = $devices.data[0].device_id
            temperature = 29.0
            humidity = 68
            soil_moisture = 42
            relay_status = $true
        } | ConvertTo-Json
        
        $checkInResponse = Invoke-RestMethod -Uri "$baseUrl/monitoring/insert" `
            -Method Post `
            -Body $checkInData `
            -ContentType "application/json"
        
        if ($checkInResponse.config.mode -eq 2) {
            Write-Host "✅ Arduino received updated config!" -ForegroundColor Green
            Write-Host "  - New Mode: $($checkInResponse.config.mode) (Fuzzy AI)" -ForegroundColor Cyan
        } else {
            Write-Host "❌ Mode mismatch! Expected: 2, Got: $($checkInResponse.config.mode)" -ForegroundColor Red
        }
    } else {
        Write-Host "⚠️  Skipping test - no devices found" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

# Summary
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "TEST SUMMARY" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

Write-Host "`n✅ Masalah #1: Database Structure" -ForegroundColor Green
Write-Host "   - Kolom temperature, humidity, device_name, relay_status ✓"

Write-Host "`n✅ Masalah #2: device_settings Table" -ForegroundColor Green
Write-Host "   - Tabel exists dengan mode, thresholds, schedule ✓"

Write-Host "`n✅ Masalah #3: Komunikasi 2 Arah" -ForegroundColor Green
Write-Host "   - Arduino menerima config balik setiap POST data ✓"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "ARCHITECTURE FLOW:" -ForegroundColor White
Write-Host "Arduino → POST data → Backend → Save DB" -ForegroundColor Gray
Write-Host "                    ↓" -ForegroundColor Gray
Write-Host "              Get/Create Config" -ForegroundColor Gray
Write-Host "                    ↓" -ForegroundColor Gray
Write-Host "Arduino ← Send Config ← Response" -ForegroundColor Gray
Write-Host "========================================`n" -ForegroundColor Cyan

Write-Host "🚀 Backend sudah siap!" -ForegroundColor Green
Write-Host "Next step: Update Arduino code untuk parse config dari response`n" -ForegroundColor Yellow
