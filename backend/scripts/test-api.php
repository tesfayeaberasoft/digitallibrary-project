<?php
/**
 * API Test Script - Run this to test if the API endpoints work
 */

echo "Testing API Endpoints...\n\n";

$baseUrl = "http://localhost:8000/api";

// Test 1: Books endpoint
echo "Test 1: GET /api/books\n";
$ch = curl_init("$baseUrl/books");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ Books endpoint working (HTTP $httpCode)\n";
} else {
    echo "❌ Books endpoint failed (HTTP $httpCode)\n";
}

// Test 2: Login endpoint
echo "\nTest 2: POST /api/auth/login\n";
$ch = curl_init("$baseUrl/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'admin@library.com',
    'password' => 'admin123'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ Login endpoint working (HTTP $httpCode)\n";
    $data = json_decode($response, true);
    if (isset($data['data']['token'])) {
        echo "✅ JWT token generated successfully\n";
    }
} else {
    echo "❌ Login endpoint failed (HTTP $httpCode)\n";
    echo "Response: $response\n";
}

echo "\n✅ API tests complete!\n";
