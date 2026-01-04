<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    $pdo->exec('CREATE DATABASE IF NOT EXISTS smart_garden CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✅ Database 'smart_garden' berhasil dibuat!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
