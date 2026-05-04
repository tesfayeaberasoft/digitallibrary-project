<?php
/**
 * Phase 2 & 3 API Testing Script
 * Test all new endpoints for reservations, fines, notifications, reviews, reading lists, and reports
 */

// Configuration
$baseUrl = 'http://localhost:8000/api';
$token = ''; // Will be set after login

// Test credentials
$adminEmail = 'admin@library.com';
$adminPassword = 'admin123';

// Color output
function colorOutput($text, $color = 'green') {
    $colors = [
        'green' => "\033[32m",
        'red' => "\033[31m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'reset' => "\033[0m"
    ];
    return $colors[$color] . $text . $colors['reset'];
}

// Make API request
function makeRequest($method, $endpoint, $data = null, $token = null) {
    global $baseUrl;
    
    $url = $baseUrl . $endpoint;
    $ch = curl_init();
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data && in_array($method, ['POST', 'PUT'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

// Test function
function test($name, $method, $endpoint, $data = null, $expectedCode = 200) {
    global $token;
    
    echo "\n" . colorOutput("Testing: $name", 'blue') . "\n";
    echo "  Endpoint: $method $endpoint\n";
    
    $response = makeRequest($method, $endpoint, $data, $token);
    
    if ($response['code'] == $expectedCode) {
        echo colorOutput("  ✓ PASSED", 'green') . " (HTTP {$response['code']})\n";
        if (isset($response['body']['message'])) {
            echo "  Message: {$response['body']['message']}\n";
        }
        return $response['body'];
    } else {
        echo colorOutput("  ✗ FAILED", 'red') . " (Expected {$expectedCode}, got {$response['code']})\n";
        if (isset($response['body']['message'])) {
            echo "  Error: {$response['body']['message']}\n";
        }
        return null;
    }
}

echo colorOutput("\n=== Phase 2 & 3 API Testing ===\n", 'yellow');

// 1. Login
echo colorOutput("\n--- Authentication ---", 'yellow');
$loginResponse = test(
    'Admin Login',
    'POST',
    '/auth/login',
    ['email' => $adminEmail, 'password' => $adminPassword]
);

if ($loginResponse && isset($loginResponse['data']['token'])) {
    $token = $loginResponse['data']['token'];
    echo colorOutput("  Token obtained successfully\n", 'green');
} else {
    die(colorOutput("\n✗ Failed to login. Cannot continue tests.\n", 'red'));
}

// 2. Reservation Tests
echo colorOutput("\n--- Reservation Tests ---", 'yellow');

test('Create Reservation', 'POST', '/reservations', [
    'book_id' => 1
], 201);

test('Get User Reservations', 'GET', '/reservations');

test('Get Book Reservations (Admin)', 'GET', '/books/1/reservations');

// 3. Fine Tests
echo colorOutput("\n--- Fine Tests ---", 'yellow');

test('Get User Fines', 'GET', '/fines');

test('Get Total Unpaid Fines', 'GET', '/fines/total');

test('Get All Fines (Admin)', 'GET', '/fines/all');

test('Get Fine Statistics (Admin)', 'GET', '/fines/statistics');

// 4. Notification Tests
echo colorOutput("\n--- Notification Tests ---", 'yellow');

test('Get User Notifications', 'GET', '/notifications');

test('Get Unread Count', 'GET', '/notifications/unread-count');

test('Get Unread Notifications', 'GET', '/notifications/unread');

test('Mark All As Read', 'PUT', '/notifications/read-all');

// 5. Review Tests
echo colorOutput("\n--- Review Tests ---", 'yellow');

$reviewResponse = test('Create Review', 'POST', '/books/1/reviews', [
    'rating' => 5,
    'review_text' => 'Excellent book! Highly recommended.'
], 201);

test('Get Book Reviews', 'GET', '/books/1/reviews');

test('Get Book Review Statistics', 'GET', '/books/1/reviews/statistics');

test('Get User Reviews', 'GET', '/reviews/user');

if ($reviewResponse && isset($reviewResponse['data']['review_id'])) {
    $reviewId = $reviewResponse['data']['review_id'];
    
    test('Update Review', 'PUT', "/reviews/$reviewId", [
        'rating' => 4,
        'review_text' => 'Great book! Updated review.'
    ]);
    
    test('Mark Review as Helpful', 'POST', "/reviews/$reviewId/helpful");
}

// 6. Reading List Tests
echo colorOutput("\n--- Reading List Tests ---", 'yellow');

$listResponse = test('Create Reading List', 'POST', '/reading-lists', [
    'name' => 'My Favorite Books',
    'description' => 'A collection of my all-time favorites',
    'is_public' => true
], 201);

test('Get User Reading Lists', 'GET', '/reading-lists');

test('Get Public Reading Lists', 'GET', '/reading-lists/public');

if ($listResponse && isset($listResponse['data']['list_id'])) {
    $listId = $listResponse['data']['list_id'];
    
    test('Add Book to Reading List', 'POST', "/reading-lists/$listId/books", [
        'book_id' => 1,
        'notes' => 'Must read again!'
    ]);
    
    test('Get Reading List with Books', 'GET', "/reading-lists/$listId");
    
    test('Update Reading List', 'PUT', "/reading-lists/$listId", [
        'name' => 'My Top Books',
        'description' => 'Updated description'
    ]);
    
    test('Remove Book from Reading List', 'DELETE', "/reading-lists/$listId/books/1");
}

// 7. Report Tests
echo colorOutput("\n--- Report Tests (Admin) ---", 'yellow');

test('Get Dashboard Statistics', 'GET', '/reports/dashboard');

test('Get Popular Books', 'GET', '/reports/popular-books?limit=5');

test('Get Active Users', 'GET', '/reports/active-users?limit=5');

test('Get Overdue Report', 'GET', '/reports/overdue');

test('Get Revenue Report', 'GET', '/reports/revenue?period=month');

test('Get Category Statistics', 'GET', '/reports/category-stats');

test('Get Monthly Activity', 'GET', '/reports/monthly-activity?months=6');

// Summary
echo colorOutput("\n\n=== Testing Complete ===\n", 'yellow');
echo colorOutput("All Phase 2 & 3 endpoints have been tested.\n", 'green');
echo colorOutput("Check the output above for any failures.\n\n", 'yellow');
