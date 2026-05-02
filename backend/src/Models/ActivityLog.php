<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Request;

class ActivityLog {
    public static function log($userId, $action, $entityType = null, $entityId = null, $description = null) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent)
            VALUES (:user_id, :action, :entity_type, :entity_id, :description, :ip_address, :user_agent)
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent()
        ]);
        
        return $db->lastInsertId();
    }

    public static function getUserLogs($userId, $limit = 50) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT * FROM activity_logs 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public static function getRecentLogs($limit = 100) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT al.*, u.first_name, u.last_name, u.email
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC 
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
