<?php
namespace app\core;

class Router {
    private $db;
    private $routes = [];
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function get($path, $handler) {
        $this->routes['GET'][$path] = $handler;
    }
    
    public function post($path, $handler) {
        $this->routes['POST'][$path] = $handler;
    }
    
    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{[a-z]+\}/', '([0-9]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $this->callHandler($handler, $matches);
                return;
            }
        }
        
        http_response_code(404);
        echo "<h1>404 - Страница не найдена</h1>";
        echo "<p>URL: " . htmlspecialchars($uri) . "</p>";
    }
    
    private function callHandler($handler, $params = []) {
        if (is_string($handler)) {
            $parts = explode('@', $handler);
            $controllerName = 'app\\controllers\\' . $parts[0];
            $methodName = $parts[1];
            
            if (class_exists($controllerName)) {
                $controller = new $controllerName($this->db);
                if (method_exists($controller, $methodName)) {
                    call_user_func_array([$controller, $methodName], $params);
                    return;
                }
            }
        }
        
        http_response_code(500);
        echo "<h1>500 - Ошибка сервера</h1>";
    }
}