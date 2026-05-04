<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Notification {
    public static function create($data) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, type, title, message, related_entity_type, related_entity_id, sent_via)
            VALUES (:user_id, :type, :title, :message, :related_entity_type, :related_entity_id, :sent_via)
        ");
        
        $stmt->execute([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'related_entity_type' => $data['related_entity_type'] ?? null,
            'related_entity_id' => $data['related_entity_id'] ?? null,
            'sent_via' => $data['sent_via'] ?? 'system'
        ]);
        
        return $db->lastInsertId();
    }

    public static function getUserNotifications($userId, $unreadOnly = false) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$userId];
        
        if ($unreadOnly) {
            $sql .= " AND is_read = FALSE";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT 50";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function markAsRead($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = TRUE, read_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function markAllAsRead($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = TRUE, read_at = NOW() WHERE user_id = ? AND is_read = FALSE");
        return $stmt->execute([$userId]);
    }

    public static function getUnreadCount($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM notifications WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Notification generators
    public static function sendDueDateReminder($userId, $transactionId, $bookTitle, $dueDate) {
        return self::create([
            'user_id' => $userId,
            'type' => 'due_date',
            'title' => 'Book Due Soon',
            'message' => "Your book '{$bookTitle}' is due on {$dueDate}. Please return it on time to avoid fines.",
            'related_entity_type' => 'transaction',
            'related_entity_id' => $transactionId
        ]);
    }

    public static function sendOverdueAlert($userId, $transactionId, $bookTitle, $daysOverdue) {
        return self::create([
            'user_id' => $userId,
            'type' => 'overdue',
            'title' => 'Book Overdue',
            'message' => "Your book '{$bookTitle}' is {$daysOverdue} days overdue. Please return it immediately. Fines may apply.",
            'related_entity_type' => 'transaction',
            'related_entity_id' => $transactionId
        ]);
    }

    public static function sendReservationAlert($userId, $reservationId, $bookTitle) {
        return self::create([
            'user_id' => $userId,
            'type' => 'reservation',
            'title' => 'Reserved Book Available',
            'message' => "Your reserved book '{$bookTitle}' is now available. Please collect it within 48 hours.",
            'related_entity_type' => 'reservation',
            'related_entity_id' => $reservationId
        ]);
    }

    public static function sendFineNotification($userId, $fineId, $amount) {
        return self::create([
            'user_id' => $userId,
            'type' => 'fine',
            'title' => 'Fine Applied',
            'message' => "A fine of {$amount} ETB has been applied to your account. Please pay to continue borrowing books.",
            'related_entity_type' => 'fine',
            'related_entity_id' => $fineId
        ]);
    }

    public static function sendGeneralNotification($userId, $title, $message) {
        return self::create([
            'user_id' => $userId,
            'type' => 'general',
            'title' => $title,
            'message' => $message
        ]);
    }
}
