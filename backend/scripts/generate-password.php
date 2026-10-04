<?php
/**
 * Generate password hash for admin123
 */

$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Password: $password\n";
echo "Hash: $hash\n";
echo "\nVerification test: " . (password_verify($password, $hash) ? "✅ PASS" : "❌ FAIL") . "\n";
