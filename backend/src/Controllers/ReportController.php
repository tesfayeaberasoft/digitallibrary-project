<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use PDO;

class ReportController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get dashboard statistics
     */
    public function dashboard()
    {
        $user = $GLOBALS['auth_user'];
        
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Total books
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM books");
        $totalBooks = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Available books
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM books WHERE status = 'available'");
        $availableBooks = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total users
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE role = 'member'");
        $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Active transactions
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM transactions WHERE status = 'issued'");
        $activeTransactions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Overdue transactions
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM transactions 
            WHERE status = 'issued' AND due_date < NOW()
        ");
        $overdueTransactions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total fines
        $stmt = $this->db->query("SELECT SUM(amount) as total FROM fines WHERE status = 'unpaid'");
        $totalUnpaidFines = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Reservations
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM reservations WHERE status = 'pending'");
        $pendingReservations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Recent activity (last 30 days)
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM transactions 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $recentActivity = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return Response::json([
            'success' => true,
            'data' => [
                'total_books' => $totalBooks,
                'available_books' => $availableBooks,
                'total_users' => $totalUsers,
                'active_transactions' => $activeTransactions,
                'overdue_transactions' => $overdueTransactions,
                'total_unpaid_fines' => $totalUnpaidFines,
                'pending_reservations' => $pendingReservations,
                'recent_activity' => $recentActivity
            ]
        ]);
    }

    /**
     * Get most popular books
     */
    public function popularBooks()
    {
        $user = $GLOBALS['auth_user'];
        
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $limit = $_GET['limit'] ?? 10);

        $stmt = $this->db->prepare("
            SELECT b.*, 
                   COUNT(t.id) as borrow_count,
                   b.average_rating,
                   b.total_reviews
            FROM books b
            LEFT JOIN transactions t ON b.id = t.book_id
            GROUP BY b.id
            ORDER BY borrow_count DESC, b.average_rating DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return Response::json([
            'success' => true,
            'data' => $books
        ]);
    }

    /**
     * Get most active users
     */
    public function activeUsers()
    {
        $user = $GLOBALS['auth_user'];
        
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $limit = $_GET['limit'] ?? 10);

        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.email,
                   COUNT(t.id) as total_borrows,
                   SUM(CASE WHEN t.status = 'issued' THEN 1 ELSE 0 END) as active_borrows,
                   SUM(CASE WHEN t.status = 'returned' THEN 1 ELSE 0 END) as returned_borrows
            FROM users u
            LEFT JOIN transactions t ON u.id = t.user_id
            WHERE u.role = 'member'
            GROUP BY u.id
            ORDER BY total_borrows DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return Response::json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get overdue report
     */
    public function overdue()
    {
        $user = $GLOBALS['auth_user'];
        
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $stmt = $this->db->query("
            SELECT t.*, 
                   b.title, b.isbn,
                   u.name as user_name, u.email as user_email,
                   DATEDIFF(NOW(), t.due_date) as days_overdue,
                   f.amount as fine_amount, f.status as fine_status
            FROM transactions t
            JOIN books b ON t.book_id = b.id
            JOIN users u ON t.user_id = u.id
            LEFT JOIN fines f ON t.id = f.transaction_id
            WHERE t.status = 'issued' AND t.due_date < NOW()
            ORDER BY days_overdue DESC
        ");
        
        $overdueTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return Response::json([
            'success' => true,
            'data' => $overdueTransactions
        ]);
    }

    /**
     * Get fine revenue report
     */
    public function revenue()
    {
        $user = $GLOBALS['auth_user'];
        
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $period = $_GET['period'] ?? 'month'); // day, week, month, year

        $dateFormat = match($period) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m'
        };

        // Revenue by period
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(paid_date, ?) as period,
                   SUM(amount) as total_revenue,
                   COUNT(*) as fine_count
            FROM fines
            WHERE status = 'paid' AND paid_date IS NOT NULL
            GROUP BY period
            ORDER BY period DESC
            LIMIT 12
        ");
        
        $stmt->execute([$dateFormat]);
        $revenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total statistics
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_collected,
                SUM(CASE WHEN status = 'unpaid' THEN amount ELSE 0 END) as total_pending,
                SUM(CASE WHEN status = 'waived' THEN amount ELSE 0 END) as total_waived,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
                COUNT(CASE WHEN status = 'unpaid' THEN 1 END) as unpaid_count,
                COUNT(CASE WHEN status = 'waived' THEN 1 END) as waived_count
            FROM fines
        ");
        
        $totals = $stmt->fetch(PDO::FETCH_ASSOC);

        return Response::json([
            'success' => true,
            'data' => [
                'revenue_by_period' => $revenue,
                'totals' => $totals
            ]
        ]);
    }

    /**
     * Get category statistics
     */
    public function categoryStats()
    {
        $user = $GLOBALS['auth_user'];
        
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $stmt = $this->db->query("
            SELECT b.category,
                   COUNT(DISTINCT b.id) as book_count,
                   COUNT(t.id) as borrow_count,
                   AVG(b.average_rating) as avg_rating
            FROM books b
            LEFT JOIN transactions t ON b.id = t.book_id
            GROUP BY b.category
            ORDER BY borrow_count DESC
        ");
        
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return Response::json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get monthly activity report
     */
    public function monthlyActivity()
    {
        $user = $GLOBALS['auth_user'];
        
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $months = $_GET['months'] ?? 12);

        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                   COUNT(*) as total_transactions,
                   SUM(CASE WHEN status = 'issued' THEN 1 ELSE 0 END) as issued,
                   SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned
            FROM transactions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY month
            ORDER BY month DESC
        ");
        
        $stmt->execute([$months]);
        $activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return Response::json([
            'success' => true,
            'data' => $activity
        ]);
    }
}
