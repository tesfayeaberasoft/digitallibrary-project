<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\ValidationService;

class UserController {
    public function index() {
        $user = $GLOBALS['auth_user'];
        
        // Only admin/librarian can view all users
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            Response::forbidden('You do not have permission to view users');
        }
        
        $filters = [
            'role' => Request::get('role'),
            'status' => Request::get('status'),
            'search' => Request::get('search'),
            'limit' => Request::get('limit', 50),
            'offset' => Request::get('offset', 0)
        ];
        
        $users = User::all($filters);
        
        Response::success([
            'users' => $users,
            'total' => count($users)
        ]);
    }

    public function show($id) {
        $user = $GLOBALS['auth_user'];
        
        // Users can only view their own profile unless they're admin/librarian
        if ($user['id'] != $id && !in_array($user['role'], ['admin', 'librarian'])) {
            Response::forbidden('You can only view your own profile');
        }
        
        $targetUser = User::findById($id);
        
        if (!$targetUser) {
            Response::notFound('User not found');
        }
        
        unset($targetUser['password_hash']);
        
        Response::success($targetUser);
    }

    public function update($id) {
        $user = $GLOBALS['auth_user'];
        
        // Only admin/librarian can update other users
        if ($user['id'] != $id && !in_array($user['role'], ['admin', 'librarian'])) {
            Response::forbidden('You can only update your own profile');
        }
        
        $targetUser = User::findById($id);
        
        if (!$targetUser) {
            Response::notFound('User not found');
        }
        
        $data = Request::only(['first_name', 'last_name', 'phone', 'address', 'status', 'role']);
        
        // Only admin can change role and status
        if ($user['role'] !== 'admin') {
            unset($data['role']);
            unset($data['status']);
        }
        
        $validator = new ValidationService();
        $isValid = $validator->validate($data, [
            'first_name' => 'min:2',
            'last_name' => 'min:2',
            'phone' => 'min:10',
            'role' => 'in:student,staff,librarian,admin',
            'status' => 'in:active,inactive,suspended'
        ]);
        
        if (!$isValid) {
            Response::validationError($validator->getErrors());
        }
        
        try {
            User::update($id, $data);
            $updatedUser = User::findById($id);
            unset($updatedUser['password_hash']);
            
            // Log activity
            ActivityLog::log($user['id'], 'UPDATE_USER', 'user', $id, "Updated user: {$updatedUser['email']}");
            
            Response::success($updatedUser, 'User updated successfully');
        } catch (\Exception $e) {
            Response::serverError('Failed to update user');
        }
    }

    public function destroy($id) {
        $user = $GLOBALS['auth_user'];
        
        // Only admin can delete users
        if ($user['role'] !== 'admin') {
            Response::forbidden('Only administrators can delete users');
        }
        
        // Prevent self-deletion
        if ($user['id'] == $id) {
            Response::error('You cannot delete your own account', 400);
        }
        
        $targetUser = User::findById($id);
        
        if (!$targetUser) {
            Response::notFound('User not found');
        }
        
        try {
            User::delete($id);
            
            // Log activity
            ActivityLog::log($user['id'], 'DELETE_USER', 'user', $id, "Deleted user: {$targetUser['email']}");
            
            Response::success([], 'User deleted successfully');
        } catch (\Exception $e) {
            Response::serverError('Failed to delete user');
        }
    }
}
