<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Transaction {
    public static function create($data) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO transactions (user_id, book_id, issue_date, due_date, status, issued_by, notes)
            VALUES (:user_id, :book_id, :issue_date, :due_date, :status, :issued_by, :notes)
        ");
        
        $stmt->execute([
            'user_id' => $data['user_id'],
            'book_id' => $data['book_id'],
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'status' => 'issued',
            'issued_by' => $data['issued_by'],
            'notes' => $data['notes'] ?? null
        ]);
        
        return $db->lastInsertId();
    }

    public static function findById($id) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT t.*, 
                   b.title as book_title, b.author as book_author, b.isbn,
                   u.first_name as user_first_name, u.last_name as user_last_name, u.email as user_email
            FROM transactions t
            JOIN books b ON t.book_id = b.id
            JOIN users u ON t.user_id = u.id
            WHERE t.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findActiveByUserAndBook($userId, $bookId) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT * FROM transactions 
            WHERE user_id = ? AND book_id = ? AND status = 'issued'
        ");
        
        $stmt->execute([$userId, $bookId]);
        return $stmt->fetch();
    }

    public static function returnBook($id, $returnedTo) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            UPDATE transactions 
            SET status = 'returned', return_date = CURDATE(), returned_to = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([$returnedTo, $id]);
    }

    public static function getUserTransactions($userId, $filters = []) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT t.*, 
                   b.title as book_title, b.author as book_author, b.isbn, b.cover_image
            FROM transactions t
            JOIN books b ON t.book_id = b.id
            WHERE t.user_id = ?
        ";
        
        $params = [$userId];
        
        if (isset($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY t.created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getBookTransactions($bookId, $filters = []) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT t.*, 
                   u.first_name as user_first_name, u.last_name as user_last_name, u.email as user_email
            FROM transactions t
            JOIN users u ON t.user_id = u.id
            WHERE t.book_id = ?
        ";
        
        $params = [$bookId];
        
        if (isset($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY t.created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function all($filters = []) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT t.*, 
                   b.title as book_title, b.author as book_author, b.isbn,
                   u.first_name as user_first_name, u.last_name as user_last_name, u.email as user_email
            FROM transactions t
            JOIN books b ON t.book_id = b.id
            JOIN users u ON t.user_id = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (isset($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['user_id'])) {
            $sql .= " AND t.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (isset($filters['book_id'])) {
            $sql .= " AND t.book_id = ?";
            $params[] = $filters['book_id'];
        }
        
        $sql .= " ORDER BY t.created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        if (isset($filters['offset'])) {
            $sql .= " OFFSET ?";
            $params[] = (int)$filters['offset'];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function updateOverdueStatus() {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            UPDATE transactions 
            SET status = 'overdue' 
            WHERE status = 'issued' AND due_date < CURDATE()
        ");
        
        return $stmt->execute();
    }

    public static function getActiveTransactionCount($userId) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM transactions 
            WHERE user_id = ? AND status = 'issued'
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
}
