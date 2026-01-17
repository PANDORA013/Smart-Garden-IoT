# ========================================
# TEST RELAY CONTROL - Smart Garden IoT
# ========================================
# Script untuk test manual kontrol relay
# Author: Testing Script
# Date: 2026-01-17

$SERVER = "http://192.168.18.35:8000"
$DEVICE_ID = "PICO_CABAI_01"

function Show-Banner {
    Write-Host "`n============================================" -ForegroundColor Cyan
    Write-Host "   SMART GARDEN - RELAY CONTROL TESTER" -ForegroundColor Cyan
    Write-Host "============================================`n" -ForegroundColor Cyan
}

function Get-CurrentStatus {
    Write-Host "[INFO] Mengambil status terkini..." -ForegroundColor Yellow
    try {
        $response = Invoke-RestMethod -Uri "$SERVER/api/monitoring/latest" -Method GET
        
        if ($response.success) {
            $data = $response.data
            Write-Host "`n[STATUS] Device: $($data.device_id)" -ForegroundColor Green
            Write-Host "         Online: $($data.is_online)" -ForegroundColor $(if($data.is_online){"Green"}else{"Red"})
            Write-Host "         Relay Status: $($data.relay_status)" -ForegroundColor $(if($data.relay_status){"Green"}else{"Gray"})
            Write-Host "         HW Relay: $($data.hardware_status.relay)" -ForegroundColor $(if($data.hardware_status.relay){"Green"}else{"Gray"})
            Write-Host "         Soil: $($data.soil_moisture)%" -ForegroundColor Cyan
            Write-Host "         Temp: $($data.temperature)°C" -ForegroundColor Cyan
            Write-Host "         Last Update: $($data.updated_at)`n" -ForegroundColor Gray
            
            return $data
        }
    } catch {
        Write-Host "[ERROR] Gagal mengambil status: $_" -ForegroundColor Red
        return $null
    }
}

function Send-RelayCommand {
    param(
        [Parameter(Mandatory=$true)]
        [bool]$Status,
        
        [string]$DeviceId = $DEVICE_ID
    )
    
    $action = if($Status){"NYALAKAN"}else{"MATIKAN"}
    Write-Host "`n[COMMAND] Mengirim perintah: $action pompa..." -ForegroundColor Yellow
    
    try {
        $body = @{
            device_id = $DeviceId
            status = $Status
        } | ConvertTo-Json
        
        $response = Invoke-RestMethod -Uri "$SERVER/api/monitoring/relay/toggle" `
                                      -Method POST `
                                      -Body $body `
                                      -ContentType 'application/json'
        
        if ($response.success) {
            Write-Host "[SUCCESS] Command berhasil dikirim!" -ForegroundColor Green
            Write-Host "          Relay Command: $($response.relay_command)" -ForegroundColor Cyan
            return $true
        } else {
            Write-Host "[FAILED] $($response.message)" -ForegroundColor Red
            return $false
        }
    } catch {
        Write-Host "[ERROR] Gagal mengirim command: $_" -ForegroundColor Red
        return $false
    }
}

function Wait-ForUpdate {
    param(
        [int]$Seconds = 20,
        [bool]$ExpectedStatus
    )
    
    Write-Host "`n[WAIT] Menunggu Pico W update ($Seconds detik)..." -ForegroundColor Yellow
    
    for ($i = $Seconds; $i -gt 0; $i--) {
        Write-Host "  ⏳ $i detik..." -NoNewline -ForegroundColor Gray
        Start-Sleep -Seconds 1
        Write-Host "`r" -NoNewline
    }
    
    Write-Host "`n[CHECK] Verifikasi hasil...`n" -ForegroundColor Yellow
}

function Test-RelayControl {
    param(
        [Parameter(Mandatory=$true)]
        [ValidateSet("ON", "OFF", "TOGGLE")]
        [string]$Action
    )
    
    Show-Banner
    
    # 1. Cek status awal
    Write-Host "=== STEP 1: CEK STATUS AWAL ===" -ForegroundColor Magenta
    $before = Get-CurrentStatus
    
    if (-not $before) {
        Write-Host "[ERROR] Tidak dapat melanjutkan test. Device offline?" -ForegroundColor Red
        return
    }
    
    if (-not $before.is_online) {
        Write-Host "[WARNING] Device OFFLINE! Test mungkin gagal." -ForegroundColor Red
        $continue = Read-Host "Lanjutkan? (y/n)"
        if ($continue -ne 'y') { return }
    }
    
    # 2. Tentukan target status
    $targetStatus = switch ($Action) {
        "ON"     { $true }
        "OFF"    { $false }
        "TOGGLE" { -not $before.relay_status }
    }
    
    # 3. Kirim command
    Write-Host "`n=== STEP 2: KIRIM COMMAND ===" -ForegroundColor Magenta
    $sent = Send-RelayCommand -Status $targetStatus
    
    if (-not $sent) {
        Write-Host "[ERROR] Command gagal dikirim!" -ForegroundColor Red
        return
    }
    
    # 4. Tunggu Pico W update (15-20 detik)
    Write-Host "`n=== STEP 3: TUNGGU PICO W UPDATE ===" -ForegroundColor Magenta
    Wait-ForUpdate -Seconds 20 -ExpectedStatus $targetStatus
    
    # 5. Cek hasil
    Write-Host "=== STEP 4: VERIFIKASI HASIL ===" -ForegroundColor Magenta
    $after = Get-CurrentStatus
    
    if ($after) {
        Write-Host "`n[COMPARISON]" -ForegroundColor Cyan
        Write-Host "  Before: relay_status=$($before.relay_status), hw_relay=$($before.hardware_status.relay)" -ForegroundColor Gray
        Write-Host "  After:  relay_status=$($after.relay_status), hw_relay=$($after.hardware_status.relay)" -ForegroundColor Gray
        Write-Host "  Target: $targetStatus`n" -ForegroundColor Yellow
        
        if ($after.relay_status -eq $targetStatus -and $after.hardware_status.relay -eq $targetStatus) {
            Write-Host "✅ TEST BERHASIL! Relay berhasil di-$Action!" -ForegroundColor Green
            Write-Host "   Pico W sudah execute command dengan benar.`n" -ForegroundColor Green
        } elseif ($after.relay_status -eq $before.relay_status) {
            Write-Host "❌ TEST GAGAL! Relay tidak berubah." -ForegroundColor Red
            Write-Host "   Kemungkinan masalah:" -ForegroundColor Yellow
            Write-Host "   1. Pico W tidak menerima relay_command dari response" -ForegroundColor Yellow
            Write-Host "   2. Relay fisik bermasalah (tidak bunyi klik)" -ForegroundColor Yellow
            Write-Host "   3. Wiring salah (cek Pin 16 ke relay IN)" -ForegroundColor Yellow
            Write-Host "`n   CEK SERIAL MONITOR di Thonny untuk message:" -ForegroundColor Cyan
            Write-Host "   '🔌 RELAY COMMAND FROM SERVER: $($Action)'`n" -ForegroundColor Cyan
        } else {
            Write-Host "⚠️  STATUS PARTIAL! relay_status berubah tapi hw_relay belum." -ForegroundColor Yellow
            Write-Host "   Tunggu update berikutnya...`n" -ForegroundColor Yellow
        }
    }
}

function Show-Menu {
    Show-Banner
    Write-Host "Pilih test case:" -ForegroundColor Cyan
    Write-Host "1. Nyalakan Pompa (ON)" -ForegroundColor Green
    Write-Host "2. Matikan Pompa (OFF)" -ForegroundColor Yellow
    Write-Host "3. Toggle (Balik Status)" -ForegroundColor Magenta
    Write-Host "4. Cek Status Only" -ForegroundColor Gray
    Write-Host "5. Test Sequence (ON -> Wait -> OFF)" -ForegroundColor Cyan
    Write-Host "0. Exit`n" -ForegroundColor Red
    
    $choice = Read-Host "Pilihan"
    
    switch ($choice) {
        "1" { Test-RelayControl -Action "ON" }
        "2" { Test-RelayControl -Action "OFF" }
        "3" { Test-RelayControl -Action "TOGGLE" }
        "4" { 
            Show-Banner
            Get-CurrentStatus 
        }
        "5" { 
            Show-Banner
            Write-Host "=== TEST SEQUENCE: ON -> WAIT -> OFF ===`n" -ForegroundColor Cyan
            
            Test-RelayControl -Action "ON"
            
            Write-Host "`n[SEQUENCE] Pompa sudah ON, tunggu 30 detik..." -ForegroundColor Yellow
            Start-Sleep -Seconds 30
            
            Test-RelayControl -Action "OFF"
        }
        "0" { 
            Write-Host "`nExiting... Bye! 👋`n" -ForegroundColor Cyan
            exit 
        }
        default { 
            Write-Host "`n[ERROR] Pilihan tidak valid!`n" -ForegroundColor Red
            Start-Sleep -Seconds 2
            Show-Menu 
        }
    }
    
    Write-Host "`n" -NoNewline
    $again = Read-Host "Test lagi? (y/n)"
    if ($again -eq 'y') {
        Show-Menu
    }
}

# ========================================
# MAIN EXECUTION
# ========================================

# Cek apakah server running
Write-Host "Checking server..." -ForegroundColor Gray
try {
    $null = Invoke-RestMethod -Uri "$SERVER/api/monitoring/latest" -TimeoutSec 3
    Write-Host "✅ Server OK!`n" -ForegroundColor Green
} catch {
    Write-Host "❌ Server tidak dapat diakses di $SERVER" -ForegroundColor Red
    Write-Host "   Pastikan: php artisan serve --host=0.0.0.0 --port=8000 running`n" -ForegroundColor Yellow
    exit
}

# Tampilkan menu
Show-Menu
