<?php
namespace app\controllers;

use app\core\Controller;
use app\core\Security;
use app\models\User;

class UserController extends Controller {
    private $userModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new User($db);
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверка CSRF токена
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                die('Ошибка безопасности: неверный CSRF токен');
            }
            
            // Валидация email
            $email = $_POST['email'] ?? '';
            if (!Security::validateEmail($email)) {
                $error = "Некорректный email адрес";
                $csrf_token = Security::generateCsrfToken();
                $this->render('register', ['error' => $error, 'csrf_token' => $csrf_token]);
                return;
            }
            
            // Валидация пароля
            $password = $_POST['password'] ?? '';
            if (strlen($password) < 6) {
                $error = "Пароль должен быть не менее 6 символов";
                $csrf_token = Security::generateCsrfToken();
                $this->render('register', ['error' => $error, 'csrf_token' => $csrf_token]);
                return;
            }
            
            // Очистка имени (XSS защита)
            $username = Security::sanitize($_POST['username'] ?? '');
            $full_name = Security::sanitize($_POST['full_name'] ?? '');
            
            // Проверка существования пользователя
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser) {
                $error = "Пользователь с таким email уже существует";
                $csrf_token = Security::generateCsrfToken();
                $this->render('register', ['error' => $error, 'csrf_token' => $csrf_token]);
                return;
            }
            
            // Создание пользователя
            $this->userModel->username = $username;
            $this->userModel->email = $email;
            $this->userModel->password = password_hash($password, PASSWORD_DEFAULT);
            $this->userModel->full_name = $full_name;
            $this->userModel->currency = $_POST['currency'] ?? 'RUB';
            
            if ($this->userModel->save()) {
                $_SESSION['user_id'] = $this->userModel->id;
                $_SESSION['username'] = $this->userModel->username;
                $this->redirect('/dashboard');
            } else {
                $error = "Ошибка регистрации";
                $csrf_token = Security::generateCsrfToken();
                $this->render('register', ['error' => $error, 'csrf_token' => $csrf_token]);
            }
        } else {
            $csrf_token = Security::generateCsrfToken();
            $this->render('register', ['csrf_token' => $csrf_token]);
        }
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверка CSRF токена
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                die('Ошибка безопасности: неверный CSRF токен');
            }
            
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Валидация email
            if (!Security::validateEmail($email)) {
                $error = "Некорректный email";
                $csrf_token = Security::generateCsrfToken();
                $this->render('login', ['error' => $error, 'csrf_token' => $csrf_token]);
                return;
            }
            
            $user = $this->userModel->findByEmail($email);
            if ($user && password_verify($password, $user->password)) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $this->redirect('/dashboard');
            } else {
                $error = "Неверный email или пароль";
                $csrf_token = Security::generateCsrfToken();
                $this->render('login', ['error' => $error, 'csrf_token' => $csrf_token]);
            }
        } else {
            $csrf_token = Security::generateCsrfToken();
            $this->render('login', ['csrf_token' => $csrf_token]);
        }
    }
    
    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }
    
    public function profile() {
        $this->requireAuth();
        
        $user = $this->userModel->findById($_SESSION['user_id']);
        $csrf_token = Security::generateCsrfToken();
        
        $this->render('profile', ['user' => $user, 'csrf_token' => $csrf_token]);
    }
    
    public function updateProfile() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверка CSRF
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                die('Ошибка безопасности');
            }
            
            $this->userModel->findById($_SESSION['user_id']);
            $this->userModel->full_name = Security::sanitize($_POST['full_name'] ?? $this->userModel->full_name);
            $this->userModel->currency = $_POST['currency'] ?? $this->userModel->currency;
            
            if (!empty($_POST['password'])) {
                if (strlen($_POST['password']) >= 6) {
                    $this->userModel->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }
            }
            
            if ($this->userModel->save()) {
                $this->redirect('/profile?success=1');
            } else {
                $this->redirect('/profile?error=1');
            }
        }
    }
}