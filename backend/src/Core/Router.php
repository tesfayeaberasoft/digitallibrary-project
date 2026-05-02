<?php

namespace App\Core;

class Router {
    private $routes = [];
    private $middlewares = [];
    private $currentMiddleware = [];

    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function group($options, $callback) {
        if (isset($options['middleware'])) {
            $this->currentMiddleware[] = $options['middleware'];
        }
        
        $callback($this);
        
        if (isset($options['middleware'])) {
            array_pop($this->currentMiddleware);
        }
    }

    private function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $this->currentMiddleware
        ];
    }

    public function notFound($handler) {
        $this->notFoundHandler = $handler;
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Handle preflight requests
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        foreach ($this->routes as $route) {
            $pattern = $this->convertToRegex($route['path']);
            
            if ($route['method'] === $method && preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove full match
                
                // Execute middleware
                foreach ($route['middleware'] as $middleware) {
                    $this->executeMiddleware($middleware);
                }
                
                // Execute handler
                $this->executeHandler($route['handler'], $matches);
                return;
            }
        }

        // 404 Not Found
        if (isset($this->notFoundHandler)) {
            call_user_func($this->notFoundHandler);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Not found']);
        }
    }

    private function convertToRegex($path) {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function executeMiddleware($middleware) {
        $className = "App\\Middleware\\" . ucfirst($middleware) . "Middleware";
        
        if (class_exists($className)) {
            $middlewareInstance = new $className();
            $middlewareInstance->handle();
        }
    }

    private function executeHandler($handler, $params = []) {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        list($controller, $method) = explode('@', $handler);
        $controllerClass = "App\\Controllers\\" . $controller;

        if (class_exists($controllerClass)) {
            $controllerInstance = new $controllerClass();
            
            if (method_exists($controllerInstance, $method)) {
                call_user_func_array([$controllerInstance, $method], $params);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Method not found']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Controller not found']);
        }
    }
}
