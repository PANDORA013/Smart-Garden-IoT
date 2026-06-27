<?php

// Create database file if it doesn't exist
$dbPath = '/tmp/database.sqlite';
if (!file_exists($dbPath)) {
    touch($dbPath);
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Run migrations if database is empty
try {
    $app->make('Illuminate\Contracts\Console\Kernel')->call('migrate', ['--force' => true]);
} catch (Exception $e) {
    // Ignore migration errors
}

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
