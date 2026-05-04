<?php

namespace App\Controllers;

use App\Core\Request;
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
    public function index(Request $request)
    {
        $user = $request->user;
        $limit = $request->query('limit', 50);
        $offset = $request->query('offset', 0);
        
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
    public function unreadCount(Request $request)
    {
        $user = $request->user;
        
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
    public function unread(Request $request)
    {
        $user = $request->user;
        
        $notifications = $this->notificationModel->getUnreadNotifications($user['id']);
        
        return Response::json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user;
        
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
    public function markAllAsRead(Request $request)
    {
        $user = $request->user;
        
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
    public function delete(Request $request, $id)
    {
        $user = $request->user;
        
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
    public function deleteAllRead(Request $request)
    {
        $user = $request->user;
        
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
