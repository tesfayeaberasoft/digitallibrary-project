<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\JWTService;
use App\Models\User;

class AuthMiddleware {
    public function handle() {
        $token = Request::bearerToken();
        
        if (!$token) {
            Response::unauthorized('Token not provided');
        }
        
        try {
            $payload = JWTService::decode($token);
            
            // Verify user exists and is active
            $user = User::findById($payload->user_id);
            
            if (!$user || $user['status'] !== 'active') {
                Response::unauthorized('Invalid or inactive user');
            }
            
            // Store user in global state
            $GLOBALS['auth_user'] = $user;
            
        } catch (\Exception $e) {
            Response::unauthorized('Invalid or expired token');
        }
    }
}
