<?php
session_start();

// Ручная автозагрузка классов
require_once __DIR__ . '/../app/core/autoload.php';

// Подключение к базе данных (настройте под свой проект)
try {
    // Замените данные на свои
    $host = 'localhost';
    $dbname = 'finance_db';  // название вашей БД
    $username = 'root';       // пользователь БД
    $password = '';           // пароль
    
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Если БД не настроена, показываем ошибку
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Получаем URI и метод
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Убираем GET параметры из URI
$request = strtok($request, '?');

// Простая маршрутизация
try {
    // Пользователи
    if ($request == '/register') {
        $controller = new app\controllers\UserController($db);
        $controller->register();
    } 
    elseif ($request == '/login') {
        $controller = new app\controllers\UserController($db);
        $controller->login();
    } 
    elseif ($request == '/logout') {
        $controller = new app\controllers\UserController($db);
        $controller->logout();
    } 
    elseif ($request == '/profile') {
        $controller = new app\controllers\UserController($db);
        if ($method == 'POST') {
            $controller->updateProfile();
        } else {
            $controller->profile();
        }
    }
    // Транзакции
    elseif ($request == '/transactions') {
        $controller = new app\controllers\TransactionController($db);
        $controller->index();
    }
    elseif ($request == '/transactions/create') {
        $controller = new app\controllers\TransactionController($db);
        if ($method == 'POST') {
            $controller->store();
        } else {
            $controller->create();
        }
    }
    elseif (preg_match('/^\/transactions\/edit\/(\d+)$/', $request, $matches)) {
        $controller = new app\controllers\TransactionController($db);
        if ($method == 'POST') {
            $controller->update($matches[1]);
        } else {
            $controller->edit($matches[1]);
        }
    }
    elseif (preg_match('/^\/transactions\/delete\/(\d+)$/', $request, $matches)) {
        $controller = new app\controllers\TransactionController($db);
        $controller->delete($matches[1]);
    }
// Бюджет
elseif ($request == '/budgets') {
    $controller = new app\controllers\BudgetController($db);
    if ($method == 'POST') {
        $controller->update();
    } else {
        $controller->index();
    }
}

// Цели
elseif ($request == '/goals') {
    $controller = new app\controllers\GoalController($db);
    $controller->index();
}
elseif ($request == '/goals/create') {
    $controller = new app\controllers\GoalController($db);
    if ($method == 'POST') {
        $controller->store();
    } else {
        $controller->create();
    }
}
elseif (preg_match('/^\/goals\/edit\/(\d+)$/', $request, $matches)) {
    $controller = new app\controllers\GoalController($db);
    if ($method == 'POST') {
        $controller->update($matches[1]);
    } else {
        $controller->edit($matches[1]);
    }
}
elseif (preg_match('/^\/goals\/delete\/(\d+)$/', $request, $matches)) {
    $controller = new app\controllers\GoalController($db);
    $controller->delete($matches[1]);
}
elseif (preg_match('/^\/goals\/deposit\/(\d+)$/', $request, $matches)) {
    $controller = new app\controllers\GoalController($db);
    $controller->deposit($matches[1]);
}
    // Категории
    elseif ($request == '/categories') {
        $controller = new app\controllers\CategoryController($db);
        $controller->index();
    }
    elseif ($request == '/categories/create') {
        $controller = new app\controllers\CategoryController($db);
        if ($method == 'POST') {
            $controller->store();
        } else {
            $controller->create();
        }
    }
    elseif (preg_match('/^\/categories\/edit\/(\d+)$/', $request, $matches)) {
        $controller = new app\controllers\CategoryController($db);
        if ($method == 'POST') {
            $controller->update($matches[1]);
        } else {
            $controller->edit($matches[1]);
        }
    }
    elseif (preg_match('/^\/categories\/delete\/(\d+)$/', $request, $matches)) {
        $controller = new app\controllers\CategoryController($db);
        $controller->delete($matches[1]);
    }
    // Дашборд
    elseif ($request == '/' || $request == '/dashboard') {
        $controller = new app\controllers\DashboardController($db);
        $controller->index();
    }
    else {
        http_response_code(404);
        echo "<h1>404 - Страница не найдена</h1>";
        echo "<p>Запрошенная страница: " . htmlspecialchars($request) . "</p>";
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "<h1>Ошибка</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}