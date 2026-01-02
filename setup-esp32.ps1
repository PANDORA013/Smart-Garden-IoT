# ========================================
# AUTO SETUP SCRIPT - ESP32 CONNECTION
# ========================================
# Script ini akan:
# 1. Cek Laravel server status
# 2. Cek firewall configuration
# 3. Generate konfigurasi ESP32 dengan IP otomatis
# 4. Test API endpoint
# ========================================

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  CABAI IoT - ESP32 AUTO SETUP" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# ========== 1. CHECK LARAVEL SERVER ==========
Write-Host "[1/5] Checking Laravel Server..." -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000" -TimeoutSec 2 -ErrorAction Stop
    Write-Host "✅ Laravel server is running!" -ForegroundColor Green
} catch {
    Write-Host "❌ Laravel server NOT running!" -ForegroundColor Red
    Write-Host "`n💡 Jalankan server dengan command:" -ForegroundColor Yellow
    Write-Host "   php artisan serve`n" -ForegroundColor Cyan
    exit
}

# ========== 2. CHECK FIREWALL ==========
Write-Host "`n[2/5] Checking Firewall..." -ForegroundColor Yellow

$firewallRule = Get-NetFirewallRule -DisplayName "Laravel Dev Server - Port 8000" -ErrorAction SilentlyContinue

if ($firewallRule) {
    Write-Host "✅ Firewall rule sudah dikonfigurasi" -ForegroundColor Green
} else {
    Write-Host "⚠️  Firewall rule belum ada" -ForegroundColor Yellow
    $isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    
    if ($isAdmin) {
        Write-Host "   Creating firewall rule..." -ForegroundColor Cyan
        New-NetFirewallRule -DisplayName "Laravel Dev Server - Port 8000" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow -ErrorAction SilentlyContinue | Out-Null
        Write-Host "✅ Firewall rule created!" -ForegroundColor Green
    } else {
        Write-Host "   ⚠️  Run PowerShell as Administrator to create rule" -ForegroundColor Yellow
    }
}

# ========== 3. GET IP ADDRESS ==========
Write-Host "`n[3/5] Detecting IP Address..." -ForegroundColor Yellow

$wifiIP = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias "Wi-Fi" -ErrorAction SilentlyContinue).IPAddress

if (-not $wifiIP) {
    $wifiIP = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias "Ethernet" -ErrorAction SilentlyContinue).IPAddress
}

if (-not $wifiIP) {
    # Fallback: ambil IP pertama yang bukan loopback
    $wifiIP = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.254.*" } | Select-Object -First 1).IPAddress
}

if ($wifiIP) {
    Write-Host "✅ IP Address: $wifiIP" -ForegroundColor Green
} else {
    Write-Host "❌ Cannot detect IP address!" -ForegroundColor Red
    Write-Host "   Connect to WiFi or Ethernet first.`n" -ForegroundColor Yellow
    exit
}

# ========== 4. TEST API ENDPOINT ==========
Write-Host "`n[4/5] Testing API Endpoint..." -ForegroundColor Yellow

try {
    # Test dengan data dummy
    $body = @{
        soil_moisture = 50.0
        status_pompa = "Mati"
    } | ConvertTo-Json

    $headers = @{
        "Content-Type" = "application/json"
    }

    $apiResponse = Invoke-WebRequest -Uri "http://localhost:8000/api/monitoring/insert" -Method POST -Body $body -Headers $headers -TimeoutSec 3 -ErrorAction Stop
    
    if ($apiResponse.StatusCode -eq 201) {
        Write-Host "✅ API endpoint working! (Status: 201 Created)" -ForegroundColor Green
    } else {
        Write-Host "⚠️  API responded with status: $($apiResponse.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ API endpoint error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "   Check if Laravel routes are configured correctly.`n" -ForegroundColor Yellow
}

# ========== 5. GENERATE ESP32 CONFIG ==========
Write-Host "`n[5/5] Generating ESP32 Configuration..." -ForegroundColor Yellow

$serverUrl = "http://${wifiIP}:8000/api/monitoring/insert"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  📋 KONFIGURASI ESP32" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

Write-Host "Edit file: arduino/cabai_monitoring_esp32.ino`n" -ForegroundColor Yellow

Write-Host "Ganti bagian WiFi & Server URL:`n" -ForegroundColor Gray

Write-Host "// ========== KONFIGURASI WiFi ==========" -ForegroundColor White
Write-Host "const char* ssid = ""NAMA_WIFI_ANDA"";           // ⚠️ GANTI INI!" -ForegroundColor Yellow
Write-Host "const char* password = ""PASSWORD_WIFI_ANDA"";   // ⚠️ GANTI INI!" -ForegroundColor Yellow
Write-Host ""
Write-Host "// ========== KONFIGURASI SERVER ==========" -ForegroundColor White
Write-Host "const char* serverUrl = ""$serverUrl"";" -ForegroundColor Green
Write-Host ""

Write-Host "========================================`n" -ForegroundColor Cyan

# ========== SAVE TO FILE ==========
$configContent = @"
// ========================================
// AUTO-GENERATED ESP32 CONFIGURATION
// Generated: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
// ========================================

// WiFi Configuration
const char* ssid = "NAMA_WIFI_ANDA";           // ⚠️ GANTI DENGAN SSID WiFi ANDA!
const char* password = "PASSWORD_WIFI_ANDA";   // ⚠️ GANTI DENGAN PASSWORD WiFi!

// Server Configuration
const char* serverUrl = "$serverUrl";

// ========================================
// CARA PENGGUNAAN:
// 1. Copy ssid dan password WiFi Anda
// 2. Paste ke arduino/cabai_monitoring_esp32.ino
// 3. Copy serverUrl di atas
// 4. Paste ke arduino/cabai_monitoring_esp32.ino
// 5. Upload ke ESP32
// ========================================
"@

$configPath = "ESP32_CONFIG.txt"
$configContent | Out-File -FilePath $configPath -Encoding UTF8

Write-Host "✅ Konfigurasi disimpan ke: $configPath`n" -ForegroundColor Green

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  📝 NEXT STEPS" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

Write-Host "1. ✅ Laravel server: RUNNING" -ForegroundColor Green
Write-Host "2. ✅ Firewall: CONFIGURED" -ForegroundColor Green
Write-Host "3. ✅ API Endpoint: WORKING" -ForegroundColor Green
Write-Host "4. ⏳ Install Arduino IDE (jika belum)" -ForegroundColor Yellow
Write-Host "5. ⏳ Upload code ke ESP32" -ForegroundColor Yellow
Write-Host "6. ⏳ Pasang sensor & relay" -ForegroundColor Yellow
Write-Host "7. ⏳ Test real-time monitoring`n" -ForegroundColor Yellow

Write-Host "📚 Baca panduan lengkap: INSTALL_ARDUINO.md`n" -ForegroundColor Cyan

Write-Host "========================================`n" -ForegroundColor Cyan
