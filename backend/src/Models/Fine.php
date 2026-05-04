<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Fine {
    const FINE_PER_DAY = 5.00; // 5 ETB per day

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

    public static function getUserFines($userId, $status = null) {
        $db = Database::getInstance()->getConnection();
        
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
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getTotalUnpaidFines($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM fines
            WHERE user_id = ? AND status = 'unpaid'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch()['total'];
    }

    public static function payFine($id, $paymentMethod, $paymentReference) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE fines
            SET status = 'paid', paid_date = NOW(), payment_method = ?, payment_reference = ?
            WHERE id = ?
        ");
        return $stmt->execute([$paymentMethod, $paymentReference, $id]);
    }

    public static function waiveFine($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE fines SET status = 'waived' WHERE id = ?");
        return $stmt->execute([$id]);
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
        
        $transactions = $stmt->fetchAll();
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

    public static function getAllFines($filters = []) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT f.*, u.first_name, u.last_name, u.email, b.title as book_title
            FROM fines f
            JOIN users u ON f.user_id = u.id
            JOIN transactions t ON f.transaction_id = t.id
            JOIN books b ON t.book_id = b.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (isset($filters['status'])) {
            $sql .= " AND f.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['user_id'])) {
            $sql .= " AND f.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        $sql .= " ORDER BY f.created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
