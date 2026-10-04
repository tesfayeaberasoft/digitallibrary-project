<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "✓ ENV loaded successfully\n";
echo "  DB_HOST:    " . $_ENV['DB_HOST'] . "\n";
echo "  DB_NAME:    " . $_ENV['DB_NAME'] . "\n";
echo "  DB_USER:    " . $_ENV['DB_USER'] . "\n";
echo "  APP_URL:    " . $_ENV['APP_URL'] . "\n";
echo "  JWT_SECRET: " . (empty($_ENV['JWT_SECRET']) ? '✗ NOT SET' : '✓ SET') . "\n";

// Test DB connection
try {
    $dsn = "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "\n✓ Database connection successful!\n";

    // Count tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "  Tables found: " . count($tables) . "\n";
    foreach ($tables as $t) {
        echo "    - $t\n";
    }
} catch (Exception $e) {
    echo "\n✗ Database connection FAILED: " . $e->getMessage() . "\n";
    echo "  → Make sure XAMPP MySQL is running and database 'digital_library' exists.\n";
}
