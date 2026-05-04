<?php
/**
 * Database Connection Test Script
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Testing Database Connection...\n\n";

echo "Configuration:\n";
echo "DB_HOST: " . $_ENV['DB_HOST'] . "\n";
echo "DB_PORT: " . $_ENV['DB_PORT'] . "\n";
echo "DB_NAME: " . $_ENV['DB_NAME'] . "\n";
echo "DB_USER: " . $_ENV['DB_USER'] . "\n";
echo "DB_PASS: " . (empty($_ENV['DB_PASS']) ? '(empty)' : '(set)') . "\n\n";

try {
    $dsn = "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Database connection successful!\n\n";
    
    // Test if tables exist
    $tables = ['users', 'books', 'transactions'];
    echo "Checking tables:\n";
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists\n";
            
            // Count records
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $countStmt->fetch()['count'];
            echo "   Records: $count\n";
        } else {
            echo "❌ Table '$table' does NOT exist\n";
        }
    }
    
    echo "\n✅ All checks passed! Backend is ready.\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "Troubleshooting steps:\n";
    echo "1. Make sure MySQL is running\n";
    echo "2. Check your database credentials in backend/.env\n";
    echo "3. Create the database: mysql -u root -p < database/schema.sql\n";
    echo "4. Import seed data: mysql -u root -p < database/seeds.sql\n";
}
