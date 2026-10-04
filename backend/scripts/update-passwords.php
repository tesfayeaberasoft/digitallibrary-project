<?php
/**
 * Update user passwords in database
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Updating user passwords...\n\n";

try {
    $dsn = "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    // Generate new password hash for "admin123"
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    echo "New password: $password\n";
    echo "New hash: $hash\n\n";
    
    // Update all test users
    $emails = ['admin@library.com', 'librarian@library.com', 'student@library.com', 'staff@library.com'];
    
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    
    foreach ($emails as $email) {
        $stmt->execute([$hash, $email]);
        echo "✅ Updated password for: $email\n";
    }
    
    echo "\n✅ All passwords updated successfully!\n";
    echo "\nYou can now login with:\n";
    echo "Email: admin@library.com (or any test user)\n";
    echo "Password: admin123\n";
    
    // Verify one user
    echo "\nVerifying admin user...\n";
    $stmt = $pdo->prepare("SELECT email, password_hash FROM users WHERE email = 'admin@library.com'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if (password_verify($password, $user['password_hash'])) {
        echo "✅ Password verification successful!\n";
    } else {
        echo "❌ Password verification failed!\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
