<?php
namespace app\core;

abstract class Controller {
    protected $db;
    
    public function __construct($db = null) {
        $this->db = $db;
    }
    
    protected function render($view, $data = []) {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "View not found: " . $view;
        }
    }
    
    protected function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }
    
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Проверка прав администратора
     * Вызывает requireAuth(), затем проверяет роль пользователя
     */
    protected function requireAdmin() {
        $this->requireAuth(); // сначала авторизация
        
        // Загружаем модель пользователя
        $userModel = new \app\models\User($this->db);
        $userModel->findById($_SESSION['user_id']);
        
        if ($userModel->role !== 'admin') {
            http_response_code(403);
            die('Доступ запрещён. Недостаточно прав.');
        }
    }
}