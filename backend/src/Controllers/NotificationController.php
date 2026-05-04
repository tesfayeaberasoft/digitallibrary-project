<?php

namespace App\Controllers;

use App\Core\Response;
use App\Models\Notification;

class NotificationController
{
    private $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    /**
     * Get all notifications for the authenticated user
     */
    public function index()
    {
        $user = $GLOBALS['auth_user'];
        $limit = $_GET['limit'] ?? 50;
        $offset = $_GET['offset'] ?? 0;
        
        $notifications = $this->notificationModel->getUserNotifications(
            $user['id'],
            $limit,
            $offset
        );
        
        return Response::json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount()
    {
        $user = $GLOBALS['auth_user'];
        
        $count = $this->notificationModel->getUnreadCount($user['id']);
        
        return Response::json([
            'success' => true,
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Get unread notifications
     */
    public function unread()
    {
        $user = $GLOBALS['auth_user'];
        
        $notifications = $this->notificationModel->getUnreadNotifications($user['id']);
        
        return Response::json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $user = $GLOBALS['auth_user'];
        
        // Verify notification belongs to user
        $notification = $this->notificationModel->getById($id);
        
        if (!$notification) {
            return Response::json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        if ($notification['user_id'] != $user['id']) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $result = $this->notificationModel->markAsRead($id);

        if ($result) {
            return Response::json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to update notification'
        ], 500);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = $GLOBALS['auth_user'];
        
        $result = $this->notificationModel->markAllAsRead($user['id']);

        return Response::json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'data' => [
                'updated_count' => $result
            ]
        ]);
    }

    /**
     * Delete a notification
     */
    public function delete($id)
    {
        $user = $GLOBALS['auth_user'];
        
        // Verify notification belongs to user
        $notification = $this->notificationModel->getById($id);
        
        if (!$notification) {
            return Response::json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        if ($notification['user_id'] != $user['id']) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $result = $this->notificationModel->delete($id);

        if ($result) {
            return Response::json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to delete notification'
        ], 500);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $user = $GLOBALS['auth_user'];
        
        $count = $this->notificationModel->deleteAllRead($user['id']);

        return Response::json([
            'success' => true,
            'message' => 'Read notifications deleted',
            'data' => [
                'deleted_count' => $count
            ]
        ]);
    }
}
