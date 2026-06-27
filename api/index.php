<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Create database directory and file if they don't exist
$dbPath = '/tmp/database.sqlite';
if (!file_exists($dbPath)) {
    touch($dbPath);
}

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Run migrations if database is empty
try {
    $app->make('Illuminate\Contracts\Console\Kernel')->call('migrate', ['--force' => true]);
} catch (Exception $e) {
    // Ignore migration errors
}

$app->make(Illuminate\Contracts\Http\Kernel::class)
    ->handle(Illuminate\Http\Request::capture())
    ->send();
