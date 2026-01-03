# ==============================================================================
# TEST SCRIPT: Pico W Smart Gateway - 2-Way Communication
# ==============================================================================
# Purpose: Validate database structure, API response, and 2-way config delivery
# Date: 2026-01-03
# ==============================================================================

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  TESTING PICO W SMART GATEWAY" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$baseUrl = "http://localhost:8000"
$testsPassed = 0
$testsFailed = 0

# ==============================================================================
# TEST 1: Database Schema Validation
# ==============================================================================
Write-Host "[TEST 1] Validasi Struktur Database..." -ForegroundColor Yellow

try {
    $checkTable = Invoke-RestMethod -Uri "$baseUrl/api/monitoring/stats" -Method Get -ErrorAction Stop
    
    # Check if response has expected fields
    if ($checkTable.data.PSObject.Properties.Name -contains 'device_id' -and
        $checkTable.data.PSObject.Properties.Name -contains 'temperature' -and
        $checkTable.data.PSObject.Properties.Name -contains 'humidity' -and
        $checkTable.data.PSObject.Properties.Name -contains 'soil_moisture') {
        Write-Host "  ✅ Database schema OK - All fields present" -ForegroundColor Green
        $testsPassed++
    } else {
        Write-Host "  ❌ Database schema incomplete" -ForegroundColor Red
        $testsFailed++
    }
} catch {
    Write-Host "  ❌ Database check failed: $($_.Exception.Message)" -ForegroundColor Red
    $testsFailed++
}

Write-Host ""

# ==============================================================================
# TEST 2: Insert Data + 2-Way Config Response
# ==============================================================================
Write-Host "[TEST 2] Test Insert with Config Response..." -ForegroundColor Yellow

$testData = @{
    device_id = "TEST_PICO_01"
    temperature = 28.5
    humidity = 65.0
    soil_moisture = 42.3
    raw_adc = 3200
    relay_status = $false
    ip_address = "192.168.1.105"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/api/monitoring/insert" `
        -Method Post `
        -ContentType "application/json" `
        -Body $testData `
        -ErrorAction Stop
    
    # Validate response structure
    if ($response.success -and 
        $response.PSObject.Properties.Name -contains 'config' -and
        $response.config.PSObject.Properties.Name -contains 'mode' -and
        $response.config.PSObject.Properties.Name -contains 'adc_min' -and
        $response.config.PSObject.Properties.Name -contains 'adc_max') {
        
        Write-Host "  ✅ Insert successful" -ForegroundColor Green
        Write-Host "  ✅ Config object present in response" -ForegroundColor Green
        Write-Host "  📋 Config Details:" -ForegroundColor Cyan
        Write-Host "     - Mode: $($response.config.mode)" -ForegroundColor Gray
        Write-Host "     - ADC Min: $($response.config.adc_min)" -ForegroundColor Gray
        Write-Host "     - ADC Max: $($response.config.adc_max)" -ForegroundColor Gray
        Write-Host "     - Batas Kering: $($response.config.batas_kering)%" -ForegroundColor Gray
        Write-Host "     - Batas Basah: $($response.config.batas_basah)%" -ForegroundColor Gray
        $testsPassed++
    } else {
        Write-Host "  ❌ Config not properly returned" -ForegroundColor Red
        $testsFailed++
    }
} catch {
    Write-Host "  ❌ Insert failed: $($_.Exception.Message)" -ForegroundColor Red
    $testsFailed++
}

Write-Host ""

# ==============================================================================
# TEST 3: Auto-Provisioning (New Device)
# ==============================================================================
Write-Host "[TEST 3] Test Auto-Provisioning..." -ForegroundColor Yellow

$newDeviceData = @{
    device_id = "AUTO_PICO_02"
    temperature = 27.0
    humidity = 60.0
    soil_moisture = 50.0
    raw_adc = 2800
    relay_status = $false
    ip_address = "192.168.1.106"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/api/monitoring/insert" `
        -Method Post `
        -ContentType "application/json" `
        -Body $newDeviceData `
        -ErrorAction Stop
    
    if ($response.success -and $response.config.mode -eq 1) {
        Write-Host "  ✅ Auto-provisioning successful" -ForegroundColor Green
        Write-Host "  ✅ New device registered with default config" -ForegroundColor Green
        Write-Host "  📋 Default Config:" -ForegroundColor Cyan
        Write-Host "     - Mode: $($response.config.mode) (Basic Threshold)" -ForegroundColor Gray
        Write-Host "     - ADC Min: $($response.config.adc_min) (Default)" -ForegroundColor Gray
        Write-Host "     - ADC Max: $($response.config.adc_max) (Default)" -ForegroundColor Gray
        $testsPassed++
    } else {
        Write-Host "  ❌ Auto-provisioning failed" -ForegroundColor Red
        $testsFailed++
    }
} catch {
    Write-Host "  ❌ Auto-provisioning test failed: $($_.Exception.Message)" -ForegroundColor Red
    $testsFailed++
}

Write-Host ""

# ==============================================================================
# TEST 4: Multi-Device Stats Query
# ==============================================================================
Write-Host "[TEST 4] Test Multi-Device Stats..." -ForegroundColor Yellow

try {
    # Query without device_id (should return latest)
    $allStats = Invoke-RestMethod -Uri "$baseUrl/api/monitoring/stats" -Method Get -ErrorAction Stop
    
    # Query specific device
    $deviceStats = Invoke-RestMethod -Uri "$baseUrl/api/monitoring/stats?device_id=TEST_PICO_01" -Method Get -ErrorAction Stop
    
    if ($allStats.success -and $deviceStats.success) {
        Write-Host "  ✅ Multi-device query successful" -ForegroundColor Green
        Write-Host "  📊 Latest Device: $($allStats.data.device_id)" -ForegroundColor Cyan
        Write-Host "     - Temperature: $($allStats.data.temperature)°C" -ForegroundColor Gray
        Write-Host "     - Humidity: $($allStats.data.humidity)%" -ForegroundColor Gray
        Write-Host "     - Soil Moisture: $($allStats.data.soil_moisture)%" -ForegroundColor Gray
        Write-Host "     - Mode: $($allStats.data.mode)" -ForegroundColor Gray
        $testsPassed++
    } else {
        Write-Host "  ❌ Multi-device query failed" -ForegroundColor Red
        $testsFailed++
    }
} catch {
    Write-Host "  ❌ Stats query failed: $($_.Exception.Message)" -ForegroundColor Red
    $testsFailed++
}

Write-Host ""

# ==============================================================================
# TEST 5: Backward Compatibility (Old Format)
# ==============================================================================
Write-Host "[TEST 5] Test Backward Compatibility..." -ForegroundColor Yellow

$oldFormatData = @{
    device_id = "LEGACY_DEVICE"
    soil_moisture = 35.5
    status_pompa = "Hidup"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/api/monitoring/insert" `
        -Method Post `
        -ContentType "application/json" `
        -Body $oldFormatData `
        -ErrorAction Stop
    
    if ($response.success -and $response.data.relay_status -eq $true) {
        Write-Host "  ✅ Backward compatibility maintained" -ForegroundColor Green
        Write-Host "  ✅ Old format (status_pompa) converted to relay_status" -ForegroundColor Green
        $testsPassed++
    } else {
        Write-Host "  ❌ Backward compatibility broken" -ForegroundColor Red
        $testsFailed++
    }
} catch {
    Write-Host "  ❌ Backward compatibility test failed: $($_.Exception.Message)" -ForegroundColor Red
    $testsFailed++
}

Write-Host ""

# ==============================================================================
# TEST 6: Config Update Propagation
# ==============================================================================
Write-Host "[TEST 6] Test Config Update (Change Mode via API)..." -ForegroundColor Yellow

$updateConfig = @{
    device_id = "TEST_PICO_01"
    mode = 2
    batas_siram = 35
    batas_stop = 75
} | ConvertTo-Json

try {
    # Update settings
    $updateResponse = Invoke-RestMethod -Uri "$baseUrl/api/settings/update" `
        -Method Post `
        -ContentType "application/json" `
        -Body $updateConfig `
        -ErrorAction Stop
    
    # Insert new data to verify updated config is returned
    $verifyData = @{
        device_id = "TEST_PICO_01"
        temperature = 29.0
        humidity = 62.0
        soil_moisture = 45.0
        raw_adc = 3000
        relay_status = $false
        ip_address = "192.168.1.105"
    } | ConvertTo-Json
    
    $verifyResponse = Invoke-RestMethod -Uri "$baseUrl/api/monitoring/insert" `
        -Method Post `
        -ContentType "application/json" `
        -Body $verifyData `
        -ErrorAction Stop
    
    if ($verifyResponse.config.mode -eq 2 -and 
        $verifyResponse.config.batas_kering -eq 35 -and
        $verifyResponse.config.batas_basah -eq 75) {
        Write-Host "  ✅ Config update successful" -ForegroundColor Green
        Write-Host "  ✅ Pico will receive updated config on next check-in" -ForegroundColor Green
        Write-Host "  📋 Updated Config:" -ForegroundColor Cyan
        Write-Host "     - Mode: $($verifyResponse.config.mode) (Fuzzy AI)" -ForegroundColor Gray
        Write-Host "     - Batas Kering: $($verifyResponse.config.batas_kering)%" -ForegroundColor Gray
        Write-Host "     - Batas Basah: $($verifyResponse.config.batas_basah)%" -ForegroundColor Gray
        $testsPassed++
    } else {
        Write-Host "  ❌ Config not properly updated" -ForegroundColor Red
        $testsFailed++
    }
} catch {
    Write-Host "  ❌ Config update test failed: $($_.Exception.Message)" -ForegroundColor Red
    $testsFailed++
}

Write-Host ""

# ==============================================================================
# SUMMARY
# ==============================================================================
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  TEST SUMMARY" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "✅ Passed: $testsPassed" -ForegroundColor Green
Write-Host "❌ Failed: $testsFailed" -ForegroundColor Red
Write-Host ""

if ($testsFailed -eq 0) {
    Write-Host "🎉 ALL TESTS PASSED!" -ForegroundColor Green
    Write-Host "🚀 Pico W Smart Gateway is ready for deployment!" -ForegroundColor Green
    exit 0
} else {
    Write-Host "⚠️ SOME TESTS FAILED" -ForegroundColor Yellow
    Write-Host "Please fix the issues before deploying to production." -ForegroundColor Yellow
    exit 1
}
