#!/usr/bin/env php
<?php
/**
 * Manual Test Runner - Mode AI Fuzzy Logic
 * 
 * Run: php run-tests.php
 */

require __DIR__ . '/vendor/autoload.php';

use Tests\TestModeAIFuzzyLogic;

echo "\n🚀 Starting Mode AI Fuzzy Logic Tests...\n";

try {
    $tester = new TestModeAIFuzzyLogic();
    $tester->setUp();
    $tester->runAllTests();
} catch (\Throwable $e) {
    echo "\n❌ Test Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);
