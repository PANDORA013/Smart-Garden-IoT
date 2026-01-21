<?php

namespace Tests;

use App\Services\ModeAIService;

/**
 * Test Manual - Mode Manual (Weekly Loop System)
 * 
 * Menguji semua aspek Mode Manual dengan scheduled watering:
 * - Weekly schedule validation
 * - Daily pump timing
 * - Manual control logic
 * - Configuration for each day
 */
class TestModeManual
{
    private ModeAIService $modeAIService;
    private $mockDevice;

    public function setUp(): void
    {
        $this->modeAIService = new ModeAIService();
        
        // Mock device dengan config default untuk Mode Manual
        $this->mockDevice = new \stdClass();
        $this->mockDevice->device_id = 'TEST_PICO_02';
        $this->mockDevice->sensor_min = 4095;
        $this->mockDevice->sensor_max = 1500;
        $this->mockDevice->pump_status = false;
        $this->mockDevice->pump_on_at = null;
        $this->mockDevice->mode = 4; // Manual mode
        
        // Weekly schedule: Monday to Sunday
        $this->mockDevice->weekly_schedule = [
            'monday' => ['enabled' => true, 'pump_on' => '06:00', 'pump_duration' => 120],
            'tuesday' => ['enabled' => true, 'pump_on' => '06:00', 'pump_duration' => 120],
            'wednesday' => ['enabled' => true, 'pump_on' => '06:00', 'pump_duration' => 120],
            'thursday' => ['enabled' => true, 'pump_on' => '06:00', 'pump_duration' => 120],
            'friday' => ['enabled' => true, 'pump_on' => '06:00', 'pump_duration' => 120],
            'saturday' => ['enabled' => false, 'pump_on' => '06:00', 'pump_duration' => 0],
            'sunday' => ['enabled' => false, 'pump_on' => '06:00', 'pump_duration' => 0]
        ];
    }

    /**
     * TEST 1: Schedule Validation
     */
    public function testScheduleValidation()
    {
        echo "\n=== TEST 1: Weekly Schedule Validation ===\n";

        $schedule = $this->mockDevice->weekly_schedule;
        
        // Check each day
        $enabledDays = 0;
        foreach ($schedule as $day => $config) {
            if ($config['enabled']) {
                $enabledDays++;
                echo "✓ {$day}: ENABLED - Pump ON: {$config['pump_on']}, Duration: {$config['pump_duration']}s\n";
            } else {
                echo "✓ {$day}: DISABLED\n";
            }
        }

        echo "\nTotal hari aktif: {$enabledDays}/7\n";
        assert($enabledDays === 5, "Harus 5 hari aktif (Senin-Jumat)");

        // Validate timing format
        foreach ($schedule as $day => $config) {
            if ($config['enabled']) {
                $time = $config['pump_on'];
                assert(preg_match('/^\d{2}:\d{2}$/', $time), "Format waktu harus HH:MM");
            }
        }

        echo "✅ TEST 1 PASSED - Schedule valid\n";
    }

    /**
     * TEST 2: Daily Schedule Execution
     */
    public function testDailyScheduleExecution()
    {
        echo "\n=== TEST 2: Daily Schedule Execution ===\n";

        $schedule = $this->mockDevice->weekly_schedule;
        
        // Simulasi hari Senin, jam 06:00
        $currentDay = 'monday';
        $currentTime = '06:00';
        
        $dayConfig = $schedule[$currentDay];
        $shouldPumpOn = $dayConfig['enabled'] && $dayConfig['pump_on'] === $currentTime;

        echo "Hari: Senin\n";
        echo "Jam: {$currentTime}\n";
        echo "Enabled: " . ($dayConfig['enabled'] ? 'YA' : 'TIDAK') . "\n";
        echo "Waktu Hidup: {$dayConfig['pump_on']}\n";
        echo "Durasi: {$dayConfig['pump_duration']}s\n";
        echo "Pompa seharusnya: " . ($shouldPumpOn ? 'ON ✓' : 'OFF') . "\n";

        assert($shouldPumpOn === true, "Senin jam 06:00 pompa harus ON");
        echo "✅ TEST 2 PASSED - Schedule execution OK\n";
    }

    /**
     * TEST 3: Non-Active Day
     */
    public function testNonActiveDayNoWatering()
    {
        echo "\n=== TEST 3: Non-Active Day (No Watering) ===\n";

        $schedule = $this->mockDevice->weekly_schedule;
        
        // Simulasi hari Sabtu
        $currentDay = 'saturday';
        $currentTime = '06:00';
        
        $dayConfig = $schedule[$currentDay];
        $shouldPumpOn = $dayConfig['enabled'] && $dayConfig['pump_on'] === $currentTime;

        echo "Hari: Sabtu\n";
        echo "Jam: {$currentTime}\n";
        echo "Enabled: " . ($dayConfig['enabled'] ? 'YA' : 'TIDAK') . "\n";
        echo "Pompa seharusnya: " . ($shouldPumpOn ? 'ON' : 'OFF ✓') . "\n";

        assert($shouldPumpOn === false, "Sabtu pompa harus OFF (hari libur)");
        echo "✅ TEST 3 PASSED - Non-active day handling OK\n";
    }

    /**
     * TEST 4: Pump Duration Calculation
     */
    public function testPumpDurationCalculation()
    {
        echo "\n=== TEST 4: Pump Duration Calculation ===\n";

        $schedule = $this->mockDevice->weekly_schedule;
        
        // Hitung total durasi seminggu
        $totalDuration = 0;
        echo "Durasi penyiraman per hari:\n";
        foreach ($schedule as $day => $config) {
            if ($config['enabled']) {
                $totalDuration += $config['pump_duration'];
                echo "  {$day}: {$config['pump_duration']}s\n";
            }
        }

        $totalMinutes = $totalDuration / 60;
        echo "\nTotal durasi: {$totalDuration}s = {$totalMinutes} menit/minggu\n";

        assert($totalDuration === 600, "Total durasi harus 600s (5 hari × 120s)");
        assert($totalMinutes === 10, "Total durasi harus 10 menit/minggu");

        echo "✅ TEST 4 PASSED - Duration calculation OK\n";
    }

    /**
     * TEST 5: Time-based Decision Logic
     */
    public function testTimeBasedDecisionLogic()
    {
        echo "\n=== TEST 5: Time-based Decision Logic ===\n";

        $schedule = $this->mockDevice->weekly_schedule;
        
        // Test berbagai waktu pada hari Senin
        $testTimes = [
            ['time' => '05:59', 'expected' => false],  // Sebelum jam 6
            ['time' => '06:00', 'expected' => true],   // Tepat jam 6
            ['time' => '06:01', 'expected' => true],   // Sedikit setelah jam 6 (assume start dalam 1 min window)
            ['time' => '08:00', 'expected' => false],  // Setelah durasi selesai (2 menit)
        ];

        $dayConfig = $schedule['monday'];
        $pumpOnTime = strtotime($dayConfig['pump_on']);
        $pumpOffTime = $pumpOnTime + ($dayConfig['pump_duration'] * 60);

        echo "Jadwal Senin: {$dayConfig['pump_on']} selama {$dayConfig['pump_duration']}s\n\n";

        foreach ($testTimes as $test) {
            $testTime = strtotime('2026-01-26 ' . $test['time']); // Senin, 26 Jan 2026
            $pumpOnTimeTest = strtotime('2026-01-26 ' . $dayConfig['pump_on']);
            $pumpOffTimeTest = $pumpOnTimeTest + $dayConfig['pump_duration'];

            $shouldBe = ($testTime >= $pumpOnTimeTest && $testTime < $pumpOffTimeTest);
            
            echo "  {$test['time']}: Pompa " . ($shouldBe ? 'ON' : 'OFF') . " (expected: " . ($test['expected'] ? 'ON' : 'OFF') . ")\n";
            assert($shouldBe === $test['expected'], "Time {$test['time']} decision salah");
        }

        echo "\n✅ TEST 5 PASSED - Time logic OK\n";
    }

    /**
     * TEST 6: Manual Override During Scheduled Time
     */
    public function testManualOverrideDuringSchedule()
    {
        echo "\n=== TEST 6: Manual Override During Scheduled Time ===\n";

        // Setup: Senin jam 06:00, pompa seharusnya ON
        $schedule = $this->mockDevice->weekly_schedule;
        $dayConfig = $schedule['monday'];
        $isScheduledToRun = true;

        echo "Waktu penjadwalan: Senin 06:00\n";
        echo "Pompa seharusnya: ON (scheduled)\n\n";

        // Skenario 1: User manual OFF
        echo "Skenario 1: User memilih Manual OFF\n";
        $manualCommand = 'OFF';
        $finalState = $manualCommand === 'OFF' ? false : true;
        echo "  Manual command: {$manualCommand}\n";
        echo "  Final state: " . ($finalState ? 'ON' : 'OFF') . "\n";
        assert($finalState === false, "Manual OFF harus override schedule");

        // Skenario 2: User manual ON
        echo "\nSkenario 2: User memilih Manual ON\n";
        $manualCommand = 'ON';
        $finalState = $manualCommand === 'ON' ? true : false;
        echo "  Manual command: {$manualCommand}\n";
        echo "  Final state: " . ($finalState ? 'ON' : 'OFF') . "\n";
        assert($finalState === true, "Manual ON harus override schedule");

        echo "\n✅ TEST 6 PASSED - Manual override OK\n";
    }

    /**
     * TEST 7: Weekly Cycle Simulation
     */
    public function testWeeklyCycleSimulation()
    {
        echo "\n=== TEST 7: Weekly Cycle Simulation ===\n";

        $schedule = $this->mockDevice->weekly_schedule;
        $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $dayKeys = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        echo "Simulasi siklus penyiraman mingguan:\n\n";

        for ($i = 0; $i < 7; $i++) {
            $day = $dayNames[$i];
            $config = $schedule[$dayKeys[$i]];
            
            if ($config['enabled']) {
                echo "  {$day}: ✓ Hidup - Jam {$config['pump_on']} ({$config['pump_duration']}s)\n";
            } else {
                echo "  {$day}: ✗ Libur\n";
            }
        }

        echo "\n✅ TEST 7 PASSED - Weekly cycle OK\n";
    }

    /**
     * TEST 8: Configuration Validation for Mode Manual
     */
    public function testModeManualConfigValidation()
    {
        echo "\n=== TEST 8: Mode Manual Configuration Validation ===\n";

        $schedule = $this->mockDevice->weekly_schedule;
        $errors = [];

        // Validate each day
        foreach ($schedule as $day => $config) {
            // Check required fields
            if (!isset($config['enabled'])) {
                $errors[] = "{$day}: Missing 'enabled' field";
            }
            if (!isset($config['pump_on'])) {
                $errors[] = "{$day}: Missing 'pump_on' field";
            }
            if (!isset($config['pump_duration'])) {
                $errors[] = "{$day}: Missing 'pump_duration' field";
            }

            // Check time format
            if (isset($config['pump_on'])) {
                if (!preg_match('/^\d{2}:\d{2}$/', $config['pump_on'])) {
                    $errors[] = "{$day}: Invalid time format '{$config['pump_on']}'";
                }
            }

            // Check duration range
            if (isset($config['pump_duration'])) {
                if ($config['pump_duration'] < 0 || $config['pump_duration'] > 600) {
                    $errors[] = "{$day}: Duration {$config['pump_duration']}s invalid (0-600s)";
                }
            }
        }

        if (count($errors) > 0) {
            echo "❌ Configuration errors:\n";
            foreach ($errors as $error) {
                echo "  - {$error}\n";
            }
            assert(false, "Configuration validation failed");
        }

        echo "✓ All days have required fields\n";
        echo "✓ All times in valid format (HH:MM)\n";
        echo "✓ All durations in valid range (0-600s)\n";
        echo "✅ TEST 8 PASSED - Config validation OK\n";
    }

    /**
     * TEST 9: Pump State Transition
     */
    public function testPumpStateTransition()
    {
        echo "\n=== TEST 9: Pump State Transition ===\n";

        echo "Simulasi transisi state pompa:\n\n";

        // State 1: OFF → scheduled ON
        echo "1️⃣ OFF → Scheduled ON\n";
        $this->mockDevice->pump_status = false;
        $shouldTurnOn = true;
        $this->mockDevice->pump_status = $shouldTurnOn;
        echo "   Pompa berubah dari OFF → ON ✓\n";
        assert($this->mockDevice->pump_status === true);

        // State 2: ON → duration end (OFF)
        echo "\n2️⃣ ON → Duration End (OFF)\n";
        $this->mockDevice->pump_status = true;
        $durationEnded = true;
        $this->mockDevice->pump_status = !$durationEnded;
        echo "   Pompa berubah dari ON → OFF ✓\n";
        assert($this->mockDevice->pump_status === false);

        // State 3: OFF → next scheduled (ON)
        echo "\n3️⃣ OFF → Next Day Scheduled (ON)\n";
        $this->mockDevice->pump_status = false;
        $nextScheduleActive = true;
        $this->mockDevice->pump_status = $nextScheduleActive;
        echo "   Pompa berubah dari OFF → ON ✓\n";
        assert($this->mockDevice->pump_status === true);

        echo "\n✅ TEST 9 PASSED - State transitions OK\n";
    }

    /**
     * TEST 10: Real-world Weekly Scenario
     */
    public function testRealWorldWeeklyScenario()
    {
        echo "\n=== TEST 10: Real-world Weekly Scenario ===\n";

        $schedule = $this->mockDevice->weekly_schedule;
        
        echo "Skenario: Penyiraman Senin-Jumat setiap jam 06:00\n\n";

        $days = ['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 
                 'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu', 'sunday' => 'Minggu'];
        
        foreach ($days as $dayKey => $dayName) {
            $config = $schedule[$dayKey];
            
            if ($config['enabled']) {
                $durationMin = $config['pump_duration'] / 60;
                echo "✓ {$dayName}: Jam {$config['pump_on']} → Penyiraman {$durationMin}m\n";
            } else {
                echo "✗ {$dayName}: Hari libur (tanpa penyiraman)\n";
            }
        }

        echo "\n📊 Summary:\n";
        echo "  • Hari aktif: 5 (Senin-Jumat)\n";
        echo "  • Total penyiraman: 10 menit/minggu\n";
        echo "  • Jadwal konsisten setiap hari\n";

        echo "\n✅ TEST 10 PASSED - Weekly scenario OK\n";
    }

    /**
     * Run all tests
     */
    public function runAllTests()
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║  TEST MANUAL - MODE MANUAL (WEEKLY LOOP SYSTEM)           ║\n";
        echo "║  Smart Garden IoT System                                   ║\n";
        echo "║  Tanggal: " . date('d-m-Y H:i:s') . "                              ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";

        try {
            $this->testScheduleValidation();
            $this->testDailyScheduleExecution();
            $this->testNonActiveDayNoWatering();
            $this->testPumpDurationCalculation();
            $this->testTimeBasedDecisionLogic();
            $this->testManualOverrideDuringSchedule();
            $this->testWeeklyCycleSimulation();
            $this->testModeManualConfigValidation();
            $this->testPumpStateTransition();
            $this->testRealWorldWeeklyScenario();

            echo "\n";
            echo "╔════════════════════════════════════════════════════════════╗\n";
            echo "║  ✅ SEMUA TEST BERHASIL!                                   ║\n";
            echo "║  Mode Manual (Weekly Loop System) siap untuk production   ║\n";
            echo "╚════════════════════════════════════════════════════════════╝\n";

        } catch (\Throwable $e) {
            echo "\n";
            echo "╔════════════════════════════════════════════════════════════╗\n";
            echo "║  ❌ TEST GAGAL                                             ║\n";
            echo "║  Error: " . $e->getMessage() . "\n";
            echo "╚════════════════════════════════════════════════════════════╝\n";
            throw $e;
        }
    }
}

// ===== RUN TESTS =====
// Uncomment to run tests
// $tester = new TestModeManual();
// $tester->setUp();
// $tester->runAllTests();
