# ============================================
# CLEANUP DUMMY DATA & DEAD CODE
# ============================================
# Script untuk menghapus file test example
# dan dead code yang tidak terpakai
# ============================================

Write-Host "🧹 CLEANUP DUMMY DATA & DEAD CODE..." -ForegroundColor Cyan
Write-Host ""

$rootPath = "c:\xampp\htdocs\Smart Garden IoT"
Set-Location $rootPath

$deletedCount = 0
$failedCount = 0

# ============================================
# 1. HAPUS FILE TEST EXAMPLE (TIDAK TERPAKAI)
# ============================================
Write-Host "📁 Menghapus file test example..." -ForegroundColor Yellow

$testFiles = @(
    "tests\Unit\ExampleTest.php",
    "tests\Feature\ExampleTest.php"
)

foreach ($file in $testFiles) {
    $fullPath = Join-Path $rootPath $file
    if (Test-Path $fullPath) {
        try {
            Remove-Item -Path $fullPath -Force
            Write-Host "  ✅ Deleted: $file" -ForegroundColor Green
            $deletedCount++
        } catch {
            Write-Host "  ❌ Failed: $file" -ForegroundColor Red
            $failedCount++
        }
    } else {
        Write-Host "  ⚠️  Not found: $file" -ForegroundColor Gray
    }
}

Write-Host ""

# ============================================
# 2. CLEAN DATABASE SEEDER (REMOVE DUMMY USER)
# ============================================
Write-Host "🗄️  Cleaning DatabaseSeeder.php..." -ForegroundColor Yellow

$seederPath = Join-Path $rootPath "database\seeders\DatabaseSeeder.php"
$cleanSeeder = @'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder untuk production
        // Tidak ada dummy data
    }
}
'@

try {
    Set-Content -Path $seederPath -Value $cleanSeeder -Encoding UTF8
    Write-Host "  ✅ Cleaned: DatabaseSeeder.php (removed dummy user)" -ForegroundColor Green
    $deletedCount++
} catch {
    Write-Host "  ❌ Failed to clean DatabaseSeeder.php" -ForegroundColor Red
    $failedCount++
}

Write-Host ""

# ============================================
# 3. CLEAN USER FACTORY (REMOVE COMMENTS)
# ============================================
Write-Host "👤 Cleaning UserFactory.php..." -ForegroundColor Yellow

$factoryPath = Join-Path $rootPath "database\factories\UserFactory.php"
$cleanFactory = @'
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
'@

try {
    Set-Content -Path $factoryPath -Value $cleanFactory -Encoding UTF8
    Write-Host "  ✅ Cleaned: UserFactory.php" -ForegroundColor Green
    $deletedCount++
} catch {
    Write-Host "  ❌ Failed to clean UserFactory.php" -ForegroundColor Red
    $failedCount++
}

Write-Host ""

# ============================================
# SUMMARY
# ============================================
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "✅ CLEANUP SELESAI!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "📊 Files cleaned/deleted: $deletedCount" -ForegroundColor Green
Write-Host "❌ Failed: $failedCount" -ForegroundColor Red
Write-Host ""
Write-Host "✨ Proyek sekarang bersih dari dummy data dan dead code!" -ForegroundColor Cyan
Write-Host ""
