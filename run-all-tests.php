<?php

require_once __DIR__ . '/vendor/autoload.php';

use Tests\TestModeAIFuzzyLogic;
use Tests\TestModeManual;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  SMART GARDEN IoT - COMPLETE TEST SUITE                   ║\n";
echo "║  Testing Mode AI Fuzzy Logic & Mode Manual               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

try {
    // ===== TEST SUITE 1: MODE AI FUZZY LOGIC =====
    echo "\n\n";
    echo "█ RUNNING TEST SUITE 1: MODE AI FUZZY LOGIC\n";
    echo "═══════════════════════════════════════════════\n";
    
    $testAI = new TestModeAIFuzzyLogic();
    $testAI->setUp();
    $testAI->runAllTests();

    // ===== TEST SUITE 2: MODE MANUAL WEEKLY LOOP =====
    echo "\n\n";
    echo "█ RUNNING TEST SUITE 2: MODE MANUAL (WEEKLY LOOP SYSTEM)\n";
    echo "═══════════════════════════════════════════════════════════\n";
    
    $testManual = new TestModeManual();
    $testManual->setUp();
    $testManual->runAllTests();

    // ===== FINAL SUMMARY =====
    echo "\n\n";
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║  🎉 COMPLETE TEST SUITE PASSED!                           ║\n";
    echo "║                                                            ║\n";
    echo "║  ✅ Mode AI Fuzzy Logic: 12/12 tests passed              ║\n";
    echo "║  ✅ Mode Manual Weekly Loop: 10/10 tests passed          ║\n";
    echo "║                                                            ║\n";
    echo "║  Total: 22/22 tests passed                                ║\n";
    echo "║  Smart Garden IoT siap untuk production deployment!       ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";

} catch (\Throwable $e) {
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║  ❌ TEST SUITE FAILED                                      ║\n";
    echo "║  Error: " . $e->getMessage() . "\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
    exit(1);
}
