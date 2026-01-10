# =============================================================================
# NETWORK CONFIGURATION CHECKER
# =============================================================================
# Script untuk cek konfigurasi jaringan dan update otomatis
# =============================================================================

Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "     🌐 SMART GARDEN IoT - NETWORK CONFIGURATION CHECKER" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan

# 1. CEK IP ADDRESS SERVER
Write-Host "`n📍 Step 1: Checking Server IP Addresses..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$ipAddresses = Get-NetIPAddress -AddressFamily IPv4 | Where-Object { 
    $_.IPAddress -notlike "127.*" -and 
    $_.IPAddress -notlike "169.*" 
}

if ($ipAddresses) {
    foreach ($ip in $ipAddresses) {
        $interface = $ip.InterfaceAlias
        $address = $ip.IPAddress
        
        Write-Host "   ✅ $interface" -ForegroundColor Green
        Write-Host "      IP: $address" -ForegroundColor White
    }
} else {
    Write-Host "   ❌ No active network connection found!" -ForegroundColor Red
}

# 2. CEK WiFi CONNECTION
Write-Host "`n📡 Step 2: Checking WiFi Status..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$wifiStatus = netsh wlan show interfaces | Select-String "State|SSID" | Out-String

if ($wifiStatus -match "connected") {
    $ssid = ($wifiStatus | Select-String "SSID" | Select-Object -First 1) -replace ".*: ", ""
    Write-Host "   ✅ WiFi Connected: $ssid" -ForegroundColor Green
} else {
    Write-Host "   ⚠️  WiFi Disconnected" -ForegroundColor Yellow
}

# 3. CEK LARAVEL SERVER
Write-Host "`n🚀 Step 3: Checking Laravel Server..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$phpProcess = Get-Process -Name php -ErrorAction SilentlyContinue | Where-Object { $_.CommandLine -like "*artisan serve*" }

if ($phpProcess) {
    Write-Host "   ✅ Laravel Server Running (PID: $($phpProcess.Id))" -ForegroundColor Green
} else {
    Write-Host "   ❌ Laravel Server NOT Running!" -ForegroundColor Red
    Write-Host "      Start with: php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor Gray
}

# 4. CEK MySQL
Write-Host "`n💾 Step 4: Checking MySQL..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$mysqlProcess = Get-Process -Name mysqld -ErrorAction SilentlyContinue

if ($mysqlProcess) {
    Write-Host "   ✅ MySQL Running (PID: $($mysqlProcess.Id))" -ForegroundColor Green
} else {
    Write-Host "   ❌ MySQL NOT Running!" -ForegroundColor Red
    Write-Host "      Start XAMPP Control Panel → MySQL → Start" -ForegroundColor Gray
}

# 5. TEST KONEKSI
Write-Host "`n🧪 Step 5: Testing API Endpoint..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$activeIP = ($ipAddresses | Select-Object -First 1).IPAddress

if ($activeIP) {
    try {
        $response = Invoke-RestMethod -Uri "http://${activeIP}:8000/api/monitoring/stats" -Method GET -TimeoutSec 3
        
        if ($response.success) {
            Write-Host "   ✅ API Endpoint Reachable!" -ForegroundColor Green
            Write-Host "      URL: http://${activeIP}:8000" -ForegroundColor White
        }
    } catch {
        Write-Host "   ❌ API Endpoint NOT Reachable" -ForegroundColor Red
        Write-Host "      Error: $_" -ForegroundColor Gray
    }
}

# 6. FIREWALL CHECK
Write-Host "`n🛡️  Step 6: Checking Firewall..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$firewallRule = Get-NetFirewallRule -DisplayName "Laravel Port 8000" -ErrorAction SilentlyContinue

if ($firewallRule) {
    Write-Host "   ✅ Firewall Rule Exists" -ForegroundColor Green
} else {
    Write-Host "   ⚠️  Firewall Rule NOT Found" -ForegroundColor Yellow
    Write-Host "      Creating rule..." -ForegroundColor Gray
    
    try {
        New-NetFirewallRule -DisplayName "Laravel Port 8000" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow | Out-Null
        Write-Host "   ✅ Firewall Rule Created!" -ForegroundColor Green
    } catch {
        Write-Host "   ❌ Failed to create rule (requires Admin)" -ForegroundColor Red
    }
}

# 7. GENERATE CONFIG
Write-Host "`n📝 Step 7: Recommended Configuration..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

if ($activeIP) {
    Write-Host "`n   Pico W Configuration (network_config.py):" -ForegroundColor Cyan
    Write-Host "   ═══════════════════════════════════════════════" -ForegroundColor Gray
    Write-Host "   SSID = `"Bocil`"" -ForegroundColor White
    Write-Host "   PASSWORD = `"kesayanganku`"" -ForegroundColor White
    Write-Host "   SERVER_URL = `"http://${activeIP}:8000/api/monitoring/insert`"" -ForegroundColor Green
    Write-Host "   DEVICE_ID = `"PICO_CABAI_01`"" -ForegroundColor White
    
    Write-Host "`n   Laravel Server:" -ForegroundColor Cyan
    Write-Host "   ═══════════════════════════════════════════════" -ForegroundColor Gray
    Write-Host "   php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor White
    
    Write-Host "`n   Browser Dashboard:" -ForegroundColor Cyan
    Write-Host "   ═══════════════════════════════════════════════" -ForegroundColor Gray
    Write-Host "   http://localhost:8000" -ForegroundColor White
    Write-Host "   http://${activeIP}:8000" -ForegroundColor Green
}

# 8. UPDATE CONFIG FILE
Write-Host "`n🔧 Step 8: Auto-Update Configuration Files..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

if ($activeIP) {
    # Update network_config.py
    $configFile = "arduino\network_config.py"
    
    if (Test-Path $configFile) {
        $content = Get-Content $configFile -Raw
        $newContent = $content -replace 'SERVER_URL_ETHERNET = "http://[^"]+:8000', "SERVER_URL_ETHERNET = `"http://${activeIP}:8000/api/monitoring/insert`""
        Set-Content -Path $configFile -Value $newContent
        
        Write-Host "   ✅ Updated $configFile" -ForegroundColor Green
        Write-Host "      New IP: $activeIP" -ForegroundColor White
    }
}

# SUMMARY
Write-Host "`n═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "                    📋 SUMMARY" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan

Write-Host "`n🎯 Next Steps:" -ForegroundColor Green
Write-Host "   1. Upload network_config.py ke Pico W (via Thonny)" -ForegroundColor White
Write-Host "   2. Upload pico_micropython.py ke Pico W" -ForegroundColor White
Write-Host "   3. Reset Pico W (CTRL+D di Thonny atau tombol reset)" -ForegroundColor White
Write-Host "   4. Monitor Serial Output untuk cek koneksi" -ForegroundColor White
Write-Host "   5. Buka dashboard: http://${activeIP}:8000`n" -ForegroundColor White

Write-Host "═══════════════════════════════════════════════════════════`n" -ForegroundColor Cyan
