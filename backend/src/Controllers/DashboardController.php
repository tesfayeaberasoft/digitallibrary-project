<?php

namespace App\Controllers;

use App\Core\Response;
use App\Core\Database;

class DashboardController {
    public function stats() {
        $user = $GLOBALS['auth_user'];
        
        // Only admin/librarian can view dashboard stats
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            Response::forbidden('You do not have permission to view dashboard statistics');
        }
        
        try {
            $db = Database::getInstance()->getConnection();
            
            // Total books
            $stmt = $db->query("SELECT COUNT(*) as total FROM books WHERE status = 'active'");
            $totalBooks = $stmt->fetch()['total'];
            
            // Available books
            $stmt = $db->query("SELECT COUNT(*) as total FROM books WHERE status = 'active' AND available_copies > 0");
            $availableBooks = $stmt->fetch()['total'];
            
            // Total users
            $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
            $totalUsers = $stmt->fetch()['total'];
            
            // Active transactions (issued books)
            $stmt = $db->query("SELECT COUNT(*) as total FROM transactions WHERE status = 'issued'");
            $activeTransactions = $stmt->fetch()['total'];
            
            // Overdue transactions
            $stmt = $db->query("SELECT COUNT(*) as total FROM transactions WHERE status = 'overdue'");
            $overdueTransactions = $stmt->fetch()['total'];
            
            // Recent transactions
            $stmt = $db->query("
                SELECT t.*, 
                       b.title as book_title, b.author as book_author,
                       u.first_name as user_first_name, u.last_name as user_last_name
                FROM transactions t
                JOIN books b ON t.book_id = b.id
                JOIN users u ON t.user_id = u.id
                ORDER BY t.created_at DESC
                LIMIT 10
            ");
            $recentTransactions = $stmt->fetchAll();
            
            // Most borrowed books
            $stmt = $db->query("
                SELECT b.id, b.title, b.author, b.cover_image, COUNT(t.id) as borrow_count
                FROM books b
                JOIN transactions t ON b.id = t.book_id
                GROUP BY b.id
                ORDER BY borrow_count DESC
                LIMIT 10
            ");
            $popularBooks = $stmt->fetchAll();
            
            Response::success([
                'total_books' => $totalBooks,
                'available_books' => $availableBooks,
                'total_users' => $totalUsers,
                'active_transactions' => $activeTransactions,
                'overdue_transactions' => $overdueTransactions,
                'recent_transactions' => $recentTransactions,
                'popular_books' => $popularBooks
            ]);
        } catch (\Exception $e) {
            Response::serverError('Failed to fetch dashboard statistics');
        }
    }
}
