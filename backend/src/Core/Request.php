<?php

namespace App\Core;

class Request {
    public static function all() {
        $data = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = $_GET;
        } else {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true) ?? [];
        }
        
        return $data;
    }

    public static function get($key, $default = null) {
        $data = self::all();
        return $data[$key] ?? $default;
    }

    public static function has($key) {
        $data = self::all();
        return isset($data[$key]);
    }

    public static function only($keys) {
        $data = self::all();
        $result = [];
        
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $result[$key] = $data[$key];
            }
        }
        
        return $result;
    }

    public static function header($key, $default = null) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$key] ?? $default;
    }

    public static function bearerToken() {
        $header = self::header('Authorization');
        
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    public static function ip() {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    public static function userAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    public static function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function path() {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
}
