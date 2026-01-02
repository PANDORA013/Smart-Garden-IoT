# 🧪 TEST AUTO-PROVISIONING SYSTEM

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  TEST AUTO-PROVISIONING SYSTEM" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$baseUrl = "http://localhost:8000/api"

# Simulasi 3 Arduino yang check-in pertama kali
$devices = @(
    @{ id = "CABAI_01"; firmware = "v2.0" },
    @{ id = "CABAI_02"; firmware = "v2.0" },
    @{ id = "TOMAT_01"; firmware = "v2.0" }
)

Write-Host "[SIMULASI] 3 Arduino baru check-in ke server..." -ForegroundColor Yellow
Write-Host ""

foreach ($device in $devices) {
    Write-Host "Device: $($device.id)" -ForegroundColor Cyan
    
    try {
        $response = Invoke-RestMethod -Uri "$baseUrl/device/check-in?device_id=$($device.id)&firmware=$($device.firmware)" -Method Get
        
        if ($response.success) {
            Write-Host "   [OK] Check-in successful!" -ForegroundColor Green
            
            if ($response.is_new_device) {
                Write-Host "   ✨ NEW DEVICE!" -ForegroundColor Yellow
                Write-Host "   ✅ Auto-configured with Cabai defaults" -ForegroundColor Green
            } else {
                Write-Host "   📡 Existing device, config loaded" -ForegroundColor White
            }
            
            Write-Host "   Config received:" -ForegroundColor Gray
            Write-Host "      - Sensor Min: $($response.config.sensor_min)" -ForegroundColor Gray
            Write-Host "      - Sensor Max: $($response.config.sensor_max)" -ForegroundColor Gray
            Write-Host "      - Batas Siram: $($response.config.batas_siram)%" -ForegroundColor Gray
            Write-Host "      - Plant Type: $($response.config.plant_type)" -ForegroundColor Gray
            Write-Host ""
        }
    } catch {
        Write-Host "   [ERROR] $($_.Exception.Message)" -ForegroundColor Red
        Write-Host ""
    }
    
    Start-Sleep -Seconds 1
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "[TEST] Getting device list..." -ForegroundColor Yellow
Write-Host ""

try {
    $deviceList = Invoke-RestMethod -Uri "$baseUrl/devices" -Method Get
    
    Write-Host "[OK] Found $($deviceList.count) devices:" -ForegroundColor Green
    foreach ($dev in $deviceList.data) {
        Write-Host "   - $($dev.device_id) ($($dev.plant_type)) - Status: $($dev.status)" -ForegroundColor White
    }
    Write-Host ""
} catch {
    Write-Host "[ERROR] $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "[TEST] Simulate data from CABAI_01..." -ForegroundColor Yellow
Write-Host ""

$dataBody = @{
    soil_moisture = 35.5
    status_pompa = "Hidup"
    relay_status = $true
    device_name = "CABAI_01"
    ip_address = "192.168.1.100"
} | ConvertTo-Json

try {
    $dataResponse = Invoke-RestMethod -Uri "$baseUrl/monitoring/insert" -Method Post -Body $dataBody -ContentType "application/json"
    
    if ($dataResponse.success) {
        Write-Host "[OK] Data inserted from CABAI_01!" -ForegroundColor Green
        Write-Host "   Soil Moisture: $($dataResponse.data.soil_moisture)%" -ForegroundColor Gray
        Write-Host "   Pompa: $($dataResponse.data.status_pompa)" -ForegroundColor Gray
        Write-Host ""
    }
} catch {
    Write-Host "[ERROR] $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "[TEST] Update TOMAT_01 to use Tomat preset..." -ForegroundColor Yellow
Write-Host ""

# Get TOMAT_01 ID first
try {
    $deviceList = Invoke-RestMethod -Uri "$baseUrl/devices" -Method Get
    $tomatDevice = $deviceList.data | Where-Object { $_.device_id -eq "TOMAT_01" }
    
    if ($tomatDevice) {
        $presetBody = @{ preset = "tomat" } | ConvertTo-Json
        
        $presetResponse = Invoke-RestMethod -Uri "$baseUrl/devices/$($tomatDevice.id)/preset" -Method Post -Body $presetBody -ContentType "application/json"
        
        if ($presetResponse.success) {
            Write-Host "[OK] TOMAT_01 now uses Tomat preset!" -ForegroundColor Green
            Write-Host "   Batas Siram updated: $($presetResponse.data.batas_siram)% (Tomat needs more water)" -ForegroundColor Gray
            Write-Host ""
        }
    }
} catch {
    Write-Host "[ERROR] $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  TEST COMPLETED!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "✅ 3 Devices auto-registered:" -ForegroundColor Green
Write-Host "   - CABAI_01 (Cabai preset - batas 40%)" -ForegroundColor White
Write-Host "   - CABAI_02 (Cabai preset - batas 40%)" -ForegroundColor White
Write-Host "   - TOMAT_01 (Tomat preset - batas 60%)" -ForegroundColor White
Write-Host ""
Write-Host "🚀 Lihat di dashboard:" -ForegroundColor Yellow
Write-Host "   http://localhost:8000/" -ForegroundColor White
Write-Host ""
Write-Host "📱 API Devices:" -ForegroundColor Yellow
Write-Host "   http://localhost:8000/api/devices" -ForegroundColor White
Write-Host ""
