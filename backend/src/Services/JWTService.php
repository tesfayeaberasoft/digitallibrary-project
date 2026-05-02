<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService {
    private static function getSecret() {
        return $_ENV['JWT_SECRET'];
    }

    private static function getExpiry() {
        return (int)$_ENV['JWT_EXPIRY'];
    }

    public static function encode($payload) {
        $issuedAt = time();
        $expire = $issuedAt + self::getExpiry();

        $token = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'user_id' => $payload['user_id'],
            'email' => $payload['email'],
            'role' => $payload['role']
        ];

        return JWT::encode($token, self::getSecret(), 'HS256');
    }

    public static function decode($token) {
        try {
            return JWT::decode($token, new Key(self::getSecret(), 'HS256'));
        } catch (\Exception $e) {
            throw new \Exception('Invalid token: ' . $e->getMessage());
        }
    }

    public static function verify($token) {
        try {
            self::decode($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
