#!/usr/bin/env pwsh
# Script untuk start Laravel server dengan network access

Write-Host "🚀 Starting Laravel Development Server for Network Access" -ForegroundColor Green
Write-Host "=" -repeat 60 -ForegroundColor Gray
Write-Host ""

# Get IP Address
$ip = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.InterfaceAlias -notlike "*Loopback*" -and $_.IPAddress -like "192.168.*"}).IPAddress

if ($ip) {
    Write-Host "📡 Your Local IP: $ip" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "📱 Access from other devices:" -ForegroundColor Yellow
    Write-Host "   http://${ip}:8000" -ForegroundColor White
    Write-Host ""
    Write-Host "🔧 Pico W URL:" -ForegroundColor Yellow
    Write-Host "   http://${ip}:8000/api/monitoring/insert" -ForegroundColor White
    Write-Host ""
    Write-Host "=" -repeat 60 -ForegroundColor Gray
    Write-Host ""
    
    # Start Laravel server
    Write-Host "⏳ Starting server on 0.0.0.0:8000..." -ForegroundColor Magenta
    php artisan serve --host=0.0.0.0 --port=8000
} else {
    Write-Host "❌ Could not detect local IP address" -ForegroundColor Red
    Write-Host "Starting on localhost only..." -ForegroundColor Yellow
    php artisan serve
}
