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
    
    // Reservation routes (Phase 2)
    $router->post('/api/reservations', 'ReservationController@create');
    $router->get('/api/reservations', 'ReservationController@index');
    $router->delete('/api/reservations/{id}', 'ReservationController@cancel');
    $router->get('/api/books/{id}/reservations', 'ReservationController@bookReservations');
    
    // Fine routes (Phase 2)
    $router->get('/api/fines', 'FineController@index');
    $router->get('/api/fines/total', 'FineController@total');
    $router->post('/api/fines/{id}/pay', 'FineController@pay');
    $router->post('/api/fines/{id}/waive', 'FineController@waive'); // Admin/Librarian only
    $router->get('/api/fines/all', 'FineController@all'); // Admin/Librarian only
    $router->get('/api/fines/statistics', 'FineController@statistics'); // Admin/Librarian only
    
    // Notification routes (Phase 2)
    $router->get('/api/notifications', 'NotificationController@index');
    $router->get('/api/notifications/unread', 'NotificationController@unread');
    $router->get('/api/notifications/unread-count', 'NotificationController@unreadCount');
    $router->put('/api/notifications/{id}/read', 'NotificationController@markAsRead');
    $router->put('/api/notifications/read-all', 'NotificationController@markAllAsRead');
    $router->delete('/api/notifications/{id}', 'NotificationController@delete');
    $router->delete('/api/notifications/read-all', 'NotificationController@deleteAllRead');
    
    // Review routes (Phase 3)
    $router->post('/api/books/{id}/reviews', 'ReviewController@create');
    $router->get('/api/books/{id}/reviews', 'ReviewController@bookReviews');
    $router->get('/api/books/{id}/reviews/statistics', 'ReviewController@bookStatistics');
    $router->get('/api/reviews/user', 'ReviewController@userReviews');
    $router->put('/api/reviews/{id}', 'ReviewController@update');
    $router->delete('/api/reviews/{id}', 'ReviewController@delete');
    $router->post('/api/reviews/{id}/helpful', 'ReviewController@markHelpful');
    
    // Reading List routes (Phase 3)
    $router->get('/api/reading-lists', 'ReadingListController@index');
    $router->get('/api/reading-lists/public', 'ReadingListController@publicLists');
    $router->get('/api/reading-lists/{id}', 'ReadingListController@show');
    $router->post('/api/reading-lists', 'ReadingListController@create');
    $router->put('/api/reading-lists/{id}', 'ReadingListController@update');
    $router->delete('/api/reading-lists/{id}', 'ReadingListController@delete');
    $router->post('/api/reading-lists/{id}/books', 'ReadingListController@addBook');
    $router->delete('/api/reading-lists/{id}/books/{bookId}', 'ReadingListController@removeBook');
    
    // Report routes (Phase 2 & 3) - Admin/Librarian only
    $router->get('/api/reports/dashboard', 'ReportController@dashboard');
    $router->get('/api/reports/popular-books', 'ReportController@popularBooks');
    $router->get('/api/reports/active-users', 'ReportController@activeUsers');
    $router->get('/api/reports/overdue', 'ReportController@overdue');
    $router->get('/api/reports/revenue', 'ReportController@revenue');
    $router->get('/api/reports/category-stats', 'ReportController@categoryStats');
    $router->get('/api/reports/monthly-activity', 'ReportController@monthlyActivity');
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
