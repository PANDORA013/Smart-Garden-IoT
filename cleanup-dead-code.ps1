# CLEANUP DEAD CODE - Smart Garden IoT
# Script untuk menghapus file-file yang sudah tidak dipakai
# Tanggal: 4 Januari 2026

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host " 🗑️  CLEANUP DEAD CODE" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$rootPath = Get-Location

# Daftar file yang akan dihapus
$filesToDelete = @{
    "ESP32 Code (Old Hardware)" = @(
        "arduino\auto_provisioning_esp32.ino",
        "arduino\cabai_monitoring_esp32.ino",
        "arduino\smart_mode_esp32.ino",
        "arduino\universal_iot_esp32.ino",
        "setup-esp32.ps1"
    )
    "Test Scripts (Temporary)" = @(
        "cleanup-test-data.php",
        "test-auto-provisioning.ps1",
        "test-backward-compat.ps1",
        "test-dashboard.ps1",
        "test-smart-config.ps1",
        "test-smart-modes.ps1"
    )
    "Old Documentation (History Logs)" = @(
        "CLEANUP_DEAD_CODE.md",
        "CLEANUP_SUMMARY.md",
        "DOKUMENTASI_AUTO_PROVISIONING.md",
        "DOKUMENTASI_CABAI.md",
        "DOKUMENTASI_SPA_DASHBOARD.md",
        "DOKUMENTASI_UNIVERSAL.md",
        "DOKUMENTASI_SMART_CONFIG.md",
        "DOKUMENTASI_SMART_MODES.md",
        "RINGKASAN_BACKWARD_COMPAT.md",
        "RINGKASAN_PERUBAHAN.md",
        "RINGKASAN_STATUS_FIXES.md"
    )
}

# Files to KEEP (important documentation)
$filesToKeep = @(
    "DOKUMENTASI_PICO_GATEWAY.md",
    "DOKUMENTASI_KALIBRASI_2_ARAH.md",
    "DOKUMENTASI_BACKEND_UPDATE.md",
    "DOKUMENTASI_DASHBOARD_FINAL.md",
    "RINGKASAN_PEROMBAKAN_PICO.md",
    "RINGKASAN_KALIBRASI_2_ARAH.md",
    "VERIFIKASI_SISTEM_SUDAH_BENAR.md",
    "PERBAIKAN_MOBILE_MENU.md",
    "README.md",
    "QUICK_START.md",
    "INSTALL_ARDUINO.md",
    "test-pico-gateway.ps1",
    "test-kalibrasi-2-arah.ps1",
    "arduino\pico_smart_gateway.ino"
)

# Display files to be deleted
Write-Host "📋 Files that will be DELETED:" -ForegroundColor Yellow
Write-Host ""

$totalFiles = 0
foreach ($category in $filesToDelete.Keys) {
    Write-Host "  [$category]" -ForegroundColor Magenta
    foreach ($file in $filesToDelete[$category]) {
        $fullPath = Join-Path $rootPath $file
        if (Test-Path $fullPath) {
            Write-Host "    ✓ $file" -ForegroundColor Red
            $totalFiles++
        } else {
            Write-Host "    ⊘ $file (not found)" -ForegroundColor DarkGray
        }
    }
    Write-Host ""
}

Write-Host "📌 Files that will be KEPT (Important):" -ForegroundColor Green
foreach ($file in $filesToKeep) {
    Write-Host "    ✓ $file" -ForegroundColor Green
}
Write-Host ""

Write-Host "Total files to delete: $totalFiles" -ForegroundColor Yellow
Write-Host ""

# Ask for confirmation
$confirmation = Read-Host "Do you want to proceed? (yes/no)"

if ($confirmation -ne "yes") {
    Write-Host "`n❌ Operation cancelled by user." -ForegroundColor Yellow
    exit
}

Write-Host "`n🗑️  Starting cleanup process..." -ForegroundColor Cyan

# Create backup folder
$backupFolder = Join-Path $rootPath "backup_dead_code_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
New-Item -Path $backupFolder -ItemType Directory -Force | Out-Null
Write-Host "📦 Backup folder created: $backupFolder" -ForegroundColor Green

$deletedCount = 0
$errorCount = 0

# Delete files
foreach ($category in $filesToDelete.Keys) {
    Write-Host "`n[$category]" -ForegroundColor Magenta
    
    foreach ($file in $filesToDelete[$category]) {
        $fullPath = Join-Path $rootPath $file
        
        if (Test-Path $fullPath) {
            try {
                # Backup file first
                $backupPath = Join-Path $backupFolder $file
                $backupDir = Split-Path $backupPath -Parent
                
                if (-not (Test-Path $backupDir)) {
                    New-Item -Path $backupDir -ItemType Directory -Force | Out-Null
                }
                
                Copy-Item -Path $fullPath -Destination $backupPath -Force
                
                # Delete file
                Remove-Item -Path $fullPath -Force
                
                Write-Host "  ✅ Deleted: $file" -ForegroundColor Green
                $deletedCount++
            }
            catch {
                Write-Host "  ❌ Error deleting: $file - $($_.Exception.Message)" -ForegroundColor Red
                $errorCount++
            }
        }
    }
}

# Summary
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host " 📊 CLEANUP SUMMARY" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "✅ Files deleted: $deletedCount" -ForegroundColor Green
Write-Host "❌ Errors: $errorCount" -ForegroundColor Red
Write-Host "📦 Backup location: $backupFolder" -ForegroundColor Yellow
Write-Host ""

if ($deletedCount -gt 0) {
    Write-Host "🎉 Cleanup completed successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor White
    Write-Host "  1. Check git status: git status" -ForegroundColor Gray
    Write-Host "  2. Commit changes: git add -A && git commit -m 'chore: Remove dead code'" -ForegroundColor Gray
    Write-Host "  3. Push to GitHub: git push" -ForegroundColor Gray
    Write-Host ""
    Write-Host "Note: Backup is in $backupFolder (you can delete it later)" -ForegroundColor DarkGray
} else {
    Write-Host "⚠️  No files were deleted." -ForegroundColor Yellow
}

Write-Host ""
