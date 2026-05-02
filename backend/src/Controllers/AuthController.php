<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\JWTService;
use App\Services\ValidationService;

class AuthController {
    public function register() {
        $data = Request::all();
        
        $validator = new ValidationService();
        $isValid = $validator->validate($data, [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'role' => 'in:student,staff,librarian,admin',
            'phone' => 'min:10'
        ]);
        
        if (!$isValid) {
            Response::validationError($validator->getErrors());
        }
        
        try {
            $userId = User::create($data);
            $user = User::findById($userId);
            
            // Remove password from response
            unset($user['password_hash']);
            
            // Log activity
            ActivityLog::log($userId, 'REGISTER', 'user', $userId, 'User registered successfully');
            
            Response::success($user, 'Registration successful', 201);
        } catch (\Exception $e) {
            Response::serverError('Registration failed: ' . $e->getMessage());
        }
    }

    public function login() {
        $data = Request::all();
        
        $validator = new ValidationService();
        $isValid = $validator->validate($data, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (!$isValid) {
            Response::validationError($validator->getErrors());
        }
        
        $user = User::findByEmail($data['email']);
        
        if (!$user || !User::verifyPassword($data['password'], $user['password_hash'])) {
            Response::error('Invalid credentials', 401);
        }
        
        if ($user['status'] !== 'active') {
            Response::error('Account is inactive or suspended', 403);
        }
        
        // Generate JWT token
        $token = JWTService::encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);
        
        // Remove password from response
        unset($user['password_hash']);
        
        // Log activity
        ActivityLog::log($user['id'], 'LOGIN', 'user', $user['id'], 'User logged in');
        
        Response::success([
            'user' => $user,
            'token' => $token
        ], 'Login successful');
    }

    public function logout() {
        $user = $GLOBALS['auth_user'];
        
        // Log activity
        ActivityLog::log($user['id'], 'LOGOUT', 'user', $user['id'], 'User logged out');
        
        Response::success([], 'Logout successful');
    }

    public function profile() {
        $user = $GLOBALS['auth_user'];
        unset($user['password_hash']);
        
        Response::success($user);
    }

    public function updateProfile() {
        $user = $GLOBALS['auth_user'];
        $data = Request::only(['first_name', 'last_name', 'phone', 'address']);
        
        $validator = new ValidationService();
        $isValid = $validator->validate($data, [
            'first_name' => 'min:2',
            'last_name' => 'min:2',
            'phone' => 'min:10'
        ]);
        
        if (!$isValid) {
            Response::validationError($validator->getErrors());
        }
        
        try {
            User::update($user['id'], $data);
            $updatedUser = User::findById($user['id']);
            unset($updatedUser['password_hash']);
            
            // Log activity
            ActivityLog::log($user['id'], 'UPDATE_PROFILE', 'user', $user['id'], 'Profile updated');
            
            Response::success($updatedUser, 'Profile updated successfully');
        } catch (\Exception $e) {
            Response::serverError('Profile update failed');
        }
    }

    public function changePassword() {
        $user = $GLOBALS['auth_user'];
        $data = Request::all();
        
        $validator = new ValidationService();
        $isValid = $validator->validate($data, [
            'current_password' => 'required',
            'new_password' => 'required|min:6',
            'new_password_confirmation' => 'required'
        ]);
        
        if (!$isValid) {
            Response::validationError($validator->getErrors());
        }
        
        // Verify current password
        if (!User::verifyPassword($data['current_password'], $user['password_hash'])) {
            Response::error('Current password is incorrect', 400);
        }
        
        // Verify password confirmation
        if ($data['new_password'] !== $data['new_password_confirmation']) {
            Response::error('Password confirmation does not match', 400);
        }
        
        try {
            User::updatePassword($user['id'], $data['new_password']);
            
            // Log activity
            ActivityLog::log($user['id'], 'CHANGE_PASSWORD', 'user', $user['id'], 'Password changed');
            
            Response::success([], 'Password changed successfully');
        } catch (\Exception $e) {
            Response::serverError('Password change failed');
        }
    }

    public function forgotPassword() {
        // TODO: Implement password reset token generation and email sending
        Response::success([], 'Password reset instructions sent to your email');
    }

    public function resetPassword() {
        // TODO: Implement password reset with token verification
        Response::success([], 'Password reset successful');
    }
}
