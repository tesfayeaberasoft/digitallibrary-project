<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Reservation {
    public static function create($data) {
        $db = Database::getInstance()->getConnection();
        
        // Calculate expiry date (48 hours from now)
        $expiryDate = date('Y-m-d H:i:s', strtotime('+48 hours'));
        
        // Get queue position
        $queuePosition = self::getNextQueuePosition($data['book_id']);
        
        $stmt = $db->prepare("
            INSERT INTO reservations (user_id, book_id, expiry_date, queue_position, status)
            VALUES (:user_id, :book_id, :expiry_date, :queue_position, 'pending')
        ");
        
        $stmt->execute([
            'user_id' => $data['user_id'],
            'book_id' => $data['book_id'],
            'expiry_date' => $expiryDate,
            'queue_position' => $queuePosition
        ]);
        
        return $db->lastInsertId();
    }

    public static function getNextQueuePosition($bookId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT COALESCE(MAX(queue_position), 0) + 1 as next_position
            FROM reservations
            WHERE book_id = ? AND status = 'pending'
        ");
        $stmt->execute([$bookId]);
        return $stmt->fetch()['next_position'];
    }

    public static function getUserReservations($userId, $status = null) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT r.*, b.title, b.author, b.isbn, b.cover_image, b.available_copies
            FROM reservations r
            JOIN books b ON r.book_id = b.id
            WHERE r.user_id = ?
        ";
        
        $params = [$userId];
        
        if ($status) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getBookReservations($bookId, $status = 'pending') {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT r.*, u.first_name, u.last_name, u.email
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            WHERE r.book_id = ? AND r.status = ?
            ORDER BY r.queue_position ASC
        ");
        
        $stmt->execute([$bookId, $status]);
        return $stmt->fetchAll();
    }

    public static function fulfillNext($bookId) {
        $db = Database::getInstance()->getConnection();
        
        // Get the first pending reservation
        $stmt = $db->prepare("
            SELECT * FROM reservations
            WHERE book_id = ? AND status = 'pending'
            ORDER BY queue_position ASC
            LIMIT 1
        ");
        $stmt->execute([$bookId]);
        $reservation = $stmt->fetch();
        
        if ($reservation) {
            // Mark as fulfilled
            $updateStmt = $db->prepare("
                UPDATE reservations
                SET status = 'fulfilled', fulfilled_date = NOW(), notified = TRUE
                WHERE id = ?
            ");
            $updateStmt->execute([$reservation['id']]);
            
            return $reservation;
        }
        
        return null;
    }

    public static function cancel($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function expireOld() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE reservations
            SET status = 'expired'
            WHERE status = 'pending' AND expiry_date < NOW()
        ");
        return $stmt->execute();
    }

    public static function hasActiveReservation($userId, $bookId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE user_id = ? AND book_id = ? AND status = 'pending'
        ");
        $stmt->execute([$userId, $bookId]);
        return $stmt->fetchColumn() > 0;
    }
}
