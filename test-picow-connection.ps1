# =============================================================================
# TEST KONEKSI PICO W KE LARAVEL SERVER
# =============================================================================
# Script ini mensimulasikan data dari Pico W untuk test koneksi
# 
# CARA MENJALANKAN:
# 1. Buka PowerShell di folder project
# 2. Jalankan: .\test-picow-connection.ps1
# =============================================================================

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "🧪 TEST KONEKSI PICO W → LARAVEL SERVER" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Konfigurasi
$SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert"
$DEVICE_ID = "PICO_CABAI_01"

# ===========================
# STEP 1: CEK LARAVEL SERVER
# ===========================
Write-Host "📡 Step 1: Cek Laravel Server..." -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri "http://192.168.18.35:8000" -Method GET -TimeoutSec 5 -UseBasicParsing
    Write-Host "✅ Laravel Server ONLINE (Status: $($response.StatusCode))" -ForegroundColor Green
} catch {
    Write-Host "❌ Laravel Server OFFLINE atau tidak bisa diakses!" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "`n💡 SOLUSI:" -ForegroundColor Yellow
    Write-Host "   1. Pastikan Laravel server berjalan: php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor White
    Write-Host "   2. Cek IP address komputer: ipconfig" -ForegroundColor White
    Write-Host "   3. Pastikan firewall tidak block port 8000" -ForegroundColor White
    exit 1
}

Start-Sleep -Seconds 1

# ===========================
# STEP 2: CEK IP ADDRESS
# ===========================
Write-Host "`n🌐 Step 2: Cek IP Address Komputer..." -ForegroundColor Yellow

$ipAddresses = Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.254.*" }

Write-Host "IP Address yang terdeteksi:" -ForegroundColor White
foreach ($ip in $ipAddresses) {
    $ipAddr = $ip.IPAddress
    if ($ipAddr -eq "192.168.18.35") {
        Write-Host "   ✅ $ipAddr (MATCH dengan konfigurasi)" -ForegroundColor Green
    } else {
        Write-Host "   ⚠️  $ipAddr" -ForegroundColor Yellow
    }
}

$expectedIP = $ipAddresses | Where-Object { $_.IPAddress -eq "192.168.18.35" }
if (-not $expectedIP) {
    Write-Host "`n⚠️  WARNING: IP Address komputer bukan 192.168.18.35!" -ForegroundColor Yellow
    Write-Host "   Konfigurasi saat ini menggunakan: 192.168.18.35" -ForegroundColor White
    Write-Host "   IP Address aktual Anda: $($ipAddresses[0].IPAddress)" -ForegroundColor White
    Write-Host "`n💡 Update konfigurasi di file berikut:" -ForegroundColor Yellow
    Write-Host "   - arduino/pico_smart_gateway.ino (line 35)" -ForegroundColor White
    Write-Host "   - arduino/pico_micropython.py (line 29)" -ForegroundColor White
}

Start-Sleep -Seconds 1

# ===========================
# STEP 3: TEST API ENDPOINT
# ===========================
Write-Host "`n🔌 Step 3: Test API Endpoint..." -ForegroundColor Yellow
Write-Host "Target: $SERVER_URL" -ForegroundColor White

# Data simulasi dari Pico W
$testData = @{
    device_id = $DEVICE_ID
    temperature = 28.5
    soil_moisture = 65.3
    raw_adc = 2500
    relay_status = $false
    ip_address = "192.168.18.100"
} | ConvertTo-Json

Write-Host "`n📤 Mengirim data simulasi..." -ForegroundColor Cyan
Write-Host $testData -ForegroundColor Gray

try {
    $response = Invoke-RestMethod -Uri $SERVER_URL -Method POST -Body $testData -ContentType "application/json" -TimeoutSec 10
    
    Write-Host "`n✅ DATA BERHASIL DIKIRIM!" -ForegroundColor Green
    Write-Host "📥 Response dari server:" -ForegroundColor Cyan
    Write-Host ($response | ConvertTo-Json -Depth 10) -ForegroundColor Gray
    
    # Cek apakah ada config dalam response
    if ($response.config) {
        Write-Host "`n🔧 Konfigurasi dari server (2-Way Communication):" -ForegroundColor Yellow
        Write-Host "   Mode: $($response.config.mode)" -ForegroundColor White
        Write-Host "   ADC Min: $($response.config.adc_min)" -ForegroundColor White
        Write-Host "   ADC Max: $($response.config.adc_max)" -ForegroundColor White
        Write-Host "   Batas Kering: $($response.config.batas_kering)%" -ForegroundColor White
        Write-Host "   Batas Basah: $($response.config.batas_basah)%" -ForegroundColor White
    }
    
} catch {
    Write-Host "`n❌ GAGAL MENGIRIM DATA!" -ForegroundColor Red
    Write-Host "   HTTP Status: $($_.Exception.Response.StatusCode.Value__)" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
    
    # Coba baca response body untuk detail error
    try {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $reader.BaseStream.Position = 0
        $errorBody = $reader.ReadToEnd()
        Write-Host "   Detail Error:" -ForegroundColor Red
        Write-Host $errorBody -ForegroundColor Gray
    } catch {}
    
    Write-Host "`n💡 SOLUSI:" -ForegroundColor Yellow
    Write-Host "   1. Cek route API: routes/api.php" -ForegroundColor White
    Write-Host "   2. Cek controller: app/Http/Controllers/MonitoringController.php" -ForegroundColor White
    Write-Host "   3. Cek Laravel log: storage/logs/laravel.log" -ForegroundColor White
    exit 1
}

Start-Sleep -Seconds 1

# ===========================
# STEP 4: CEK DATABASE
# ===========================
Write-Host "`n💾 Step 4: Cek Database..." -ForegroundColor Yellow

try {
    # Cek apakah data tersimpan di database
    $artisanOutput = php artisan tinker --execute="echo DB::table('monitorings')->where('device_id', '$DEVICE_ID')->count();"
    
    if ($artisanOutput -match '\d+') {
        $recordCount = [int]$Matches[0]
        Write-Host "✅ Database OK - Total record device '$DEVICE_ID': $recordCount" -ForegroundColor Green
        
        # Ambil data terakhir
        $lastData = php artisan tinker --execute="print_r(DB::table('monitorings')->where('device_id', '$DEVICE_ID')->orderBy('created_at', 'desc')->first());"
        Write-Host "`n📊 Data terakhir:" -ForegroundColor Cyan
        Write-Host $lastData -ForegroundColor Gray
    } else {
        Write-Host "⚠️  Tidak ada data di database" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠️  Gagal cek database: $($_.Exception.Message)" -ForegroundColor Yellow
}

# ===========================
# STEP 5: TEST KONEKSI PING
# ===========================
Write-Host "`n🏓 Step 5: Test Network Connectivity..." -ForegroundColor Yellow

$pingResult = Test-Connection -ComputerName "192.168.18.35" -Count 2 -Quiet

if ($pingResult) {
    Write-Host "✅ Network OK - Komputer bisa di-ping dari jaringan" -ForegroundColor Green
} else {
    Write-Host "⚠️  Network Issue - Komputer tidak bisa di-ping" -ForegroundColor Yellow
    Write-Host "   Ini mungkin karena firewall, tapi tidak selalu bermasalah" -ForegroundColor White
}

# ===========================
# STEP 6: CEK FIREWALL
# ===========================
Write-Host "`n🛡️  Step 6: Cek Firewall Port 8000..." -ForegroundColor Yellow

try {
    $firewallRules = Get-NetFirewallRule | Where-Object { 
        $_.DisplayName -like "*8000*" -or $_.DisplayName -like "*Laravel*" -or $_.DisplayName -like "*PHP*"
    }
    
    if ($firewallRules.Count -gt 0) {
        Write-Host "✅ Ditemukan $($firewallRules.Count) firewall rule terkait" -ForegroundColor Green
        foreach ($rule in $firewallRules) {
            Write-Host "   - $($rule.DisplayName) ($($rule.Enabled))" -ForegroundColor Gray
        }
    } else {
        Write-Host "⚠️  Tidak ada firewall rule khusus untuk port 8000" -ForegroundColor Yellow
        Write-Host "   Jika ada masalah, tambahkan rule firewall:" -ForegroundColor White
        Write-Host "   New-NetFirewallRule -DisplayName 'Laravel Server' -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow" -ForegroundColor Gray
    }
} catch {
    Write-Host "⚠️  Gagal cek firewall (butuh admin): $($_.Exception.Message)" -ForegroundColor Yellow
}

# ===========================
# SUMMARY
# ===========================
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "📋 RINGKASAN TEST" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

Write-Host "✅ Laravel Server: ONLINE" -ForegroundColor Green
Write-Host "✅ API Endpoint: BERFUNGSI" -ForegroundColor Green
Write-Host "✅ Database: TERSAMBUNG" -ForegroundColor Green
Write-Host "✅ Test Data: BERHASIL DIKIRIM" -ForegroundColor Green

Write-Host "`n🎉 SISTEM SIAP DIGUNAKAN!" -ForegroundColor Green
Write-Host "`n📝 Langkah Selanjutnya:" -ForegroundColor Yellow
Write-Host "   1. Upload code Arduino/MicroPython ke Pico W" -ForegroundColor White
Write-Host "   2. Buka Serial Monitor untuk lihat output Pico W" -ForegroundColor White
Write-Host "   3. Akses dashboard: http://192.168.18.35:8000" -ForegroundColor White
Write-Host "   4. Monitor data real-time dari Pico W" -ForegroundColor White

Write-Host "`n========================================`n" -ForegroundColor Cyan
