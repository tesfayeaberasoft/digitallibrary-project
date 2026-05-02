<?php
/**
 * Digital Library Management System - API Entry Point
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Database;
use App\Middleware\CorsMiddleware;
use App\Middleware\AuthMiddleware;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Error handling
error_reporting($_ENV['APP_DEBUG'] === 'true' ? E_ALL : 0);
ini_set('display_errors', $_ENV['APP_DEBUG'] === 'true' ? '1' : '0');

// Set headers
header('Content-Type: application/json');

// Handle CORS
CorsMiddleware::handle();

// Initialize router
$router = new Router();

// Initialize database connection
try {
    Database::getInstance();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Internal server error'
    ]);
    exit;
}

// Public routes (no authentication required)
$router->post('/api/auth/register', 'AuthController@register');
$router->post('/api/auth/login', 'AuthController@login');
$router->post('/api/auth/forgot-password', 'AuthController@forgotPassword');
$router->post('/api/auth/reset-password', 'AuthController@resetPassword');

// Protected routes (authentication required)
$router->group(['middleware' => 'auth'], function($router) {
    // Auth routes
    $router->post('/api/auth/logout', 'AuthController@logout');
    $router->get('/api/auth/profile', 'AuthController@profile');
    $router->put('/api/auth/profile', 'AuthController@updateProfile');
    $router->post('/api/auth/change-password', 'AuthController@changePassword');
    
    // Book routes
    $router->get('/api/books', 'BookController@index');
    $router->get('/api/books/search', 'BookController@search');
    $router->get('/api/books/{id}', 'BookController@show');
    $router->post('/api/books', 'BookController@store'); // Admin/Librarian only
    $router->put('/api/books/{id}', 'BookController@update'); // Admin/Librarian only
    $router->delete('/api/books/{id}', 'BookController@destroy'); // Admin only
    
    // Transaction routes
    $router->post('/api/transactions/issue', 'TransactionController@issue');
    $router->post('/api/transactions/return', 'TransactionController@return');
    $router->get('/api/transactions/user/{userId}', 'TransactionController@userTransactions');
    $router->get('/api/transactions/book/{bookId}', 'TransactionController@bookTransactions');
    $router->get('/api/transactions', 'TransactionController@index');
    
    // User routes (Admin/Librarian only)
    $router->get('/api/users', 'UserController@index');
    $router->get('/api/users/{id}', 'UserController@show');
    $router->put('/api/users/{id}', 'UserController@update');
    $router->delete('/api/users/{id}', 'UserController@destroy');
    
    // Dashboard/Stats routes
    $router->get('/api/dashboard/stats', 'DashboardController@stats');
});

// Handle 404
$router->notFound(function() {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Endpoint not found'
    ]);
});

// Dispatch the request
$router->dispatch();
