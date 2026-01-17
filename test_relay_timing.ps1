# Test relay command timing
Write-Host "`n=== TEST RELAY COMMAND TIMING ===" -ForegroundColor Cyan

# Step 1: Toggle Relay ON
Write-Host "`n[1] Mengirim toggle relay ON..." -ForegroundColor Yellow
$toggleResult = Invoke-RestMethod -Uri "http://192.168.18.35:8000/api/monitoring/relay/toggle" -Method POST -ContentType "application/json" -Body '{"device_id":"PICO_CABAI_01","status":true}'
Write-Host "   Toggle Result: $($toggleResult.message)" -ForegroundColor Green
Write-Host "   relay_command: $($toggleResult.relay_command)" -ForegroundColor Green

# Step 2: Immediate check (< 1 second)
Write-Host "`n[2] Cek database SEGERA (< 1 detik)..." -ForegroundColor Yellow
$dbCheck1 = php artisan tinker --execute="echo json_encode(\App\Models\DeviceSetting::where('device_id', 'PICO_CABAI_01')->first(['relay_command']));" | ConvertFrom-Json
Write-Host "   relay_command in DB: $($dbCheck1.relay_command)" -ForegroundColor $(if($dbCheck1.relay_command -eq $null){"Red"}else{"Green"})

# Step 3: Wait 5 seconds
Write-Host "`n[3] Tunggu 5 detik..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

# Step 4: Check again
Write-Host "`n[4] Cek database lagi (5 detik kemudian)..." -ForegroundColor Yellow
$dbCheck2 = php artisan tinker --execute="echo json_encode(\App\Models\DeviceSetting::where('device_id', 'PICO_CABAI_01')->first(['relay_command']));" | ConvertFrom-Json
Write-Host "   relay_command in DB: $($dbCheck2.relay_command)" -ForegroundColor $(if($dbCheck2.relay_command -eq $null){"Red"}else{"Green"})

# Step 5: Check logs
Write-Host "`n[5] Cek log terakhir..." -ForegroundColor Yellow
$lastLogs = Get-Content "storage\logs\laravel.log" -Tail 5
Write-Host $lastLogs

Write-Host "`n=== TEST SELESAI ===" -ForegroundColor Cyan
