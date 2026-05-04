<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Fine {
    const FINE_PER_DAY = 5.00; // 5 ETB per day
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getUserFines($userId, $status = null) {
        $sql = "
            SELECT f.*, t.book_id, b.title as book_title, b.author as book_author
            FROM fines f
            JOIN transactions t ON f.transaction_id = t.id
            JOIN books b ON t.book_id = b.id
            WHERE f.user_id = ?
        ";
        
        $params = [$userId];
        
        if ($status) {
            $sql .= " AND f.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY f.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalUnpaidFines($userId) {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM fines
            WHERE user_id = ? AND status = 'unpaid'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT f.*, t.book_id, b.title as book_title
            FROM fines f
            JOIN transactions t ON f.transaction_id = t.id
            JOIN books b ON t.book_id = b.id
            WHERE f.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function payFine($id, $paymentMethod, $paymentReference) {
        $stmt = $this->db->prepare("
            UPDATE fines
            SET status = 'paid', paid_date = NOW(), payment_method = ?, payment_reference = ?
            WHERE id = ?
        ");
        return $stmt->execute([$paymentMethod, $paymentReference, $id]);
    }

    public function waiveFine($id) {
        $stmt = $this->db->prepare("UPDATE fines SET status = 'waived' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllFines($status = null, $userId = null) {
        $sql = "
            SELECT f.*, u.first_name, u.last_name, u.email, b.title as book_title
            FROM fines f
            JOIN users u ON f.user_id = u.id
            JOIN transactions t ON f.transaction_id = t.id
            JOIN books b ON t.book_id = b.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " AND f.status = ?";
            $params[] = $status;
        }
        
        if ($userId) {
            $sql .= " AND f.user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY f.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatistics() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_fines,
                SUM(CASE WHEN status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_count,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN status = 'waived' THEN 1 ELSE 0 END) as waived_count,
                SUM(CASE WHEN status = 'unpaid' THEN amount ELSE 0 END) as total_unpaid,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_collected,
                SUM(CASE WHEN status = 'waived' THEN amount ELSE 0 END) as total_waived
            FROM fines
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Static methods for backward compatibility
    public static function create($data) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO fines (transaction_id, user_id, amount, reason, days_overdue, status)
            VALUES (:transaction_id, :user_id, :amount, :reason, :days_overdue, 'unpaid')
        ");
        
        $stmt->execute([
            'transaction_id' => $data['transaction_id'],
            'user_id' => $data['user_id'],
            'amount' => $data['amount'],
            'reason' => $data['reason'] ?? 'Overdue book',
            'days_overdue' => $data['days_overdue'] ?? 0
        ]);
        
        return $db->lastInsertId();
    }

    public static function calculateOverdueFine($daysOverdue) {
        return $daysOverdue * self::FINE_PER_DAY;
    }

    public static function generateOverdueFines() {
        $db = Database::getInstance()->getConnection();
        
        // Get all overdue transactions without fines
        $stmt = $db->query("
            SELECT t.*, DATEDIFF(CURDATE(), t.due_date) as days_overdue
            FROM transactions t
            LEFT JOIN fines f ON t.id = f.transaction_id
            WHERE t.status = 'overdue' AND f.id IS NULL AND t.due_date < CURDATE()
        ");
        
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $finesCreated = 0;
        
        foreach ($transactions as $transaction) {
            $amount = self::calculateOverdueFine($transaction['days_overdue']);
            
            self::create([
                'transaction_id' => $transaction['id'],
                'user_id' => $transaction['user_id'],
                'amount' => $amount,
                'reason' => "Overdue book - {$transaction['days_overdue']} days",
                'days_overdue' => $transaction['days_overdue']
            ]);
            
            $finesCreated++;
        }
        
        return $finesCreated;
    }
}
