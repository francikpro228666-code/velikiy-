# Руководство разработчика

## Структура проекта

project/
├── app/
│   ├── core/                    # Ядро приложения (Controller, Security, автозагрузка)
│   ├── controllers/             # Контроллеры (обработка запросов)
│   ├── models/                  # Модели (работа с БД)
│   └── views/                   # Представления (HTML-шаблоны)
├── public/
│   ├── index.php                # Единая точка входа
│   └── .htaccess                # Перенаправление на index.php
├── init/
│   └── init.sql                 # SQL-скрипт инициализации БД
├── docs/                        # Документация
├── Dockerfile
├── docker-compose.yml
└── README.md

## Архитектура и паттерны

### MVC (Model-View-Controller)
- Model – отвечает за работу с базой данных (CRUD, бизнес-логика данных)
- View – отвечает за отображение (HTML, Bootstrap, JavaScript)
- Controller – обрабатывает запросы, вызывает модели и рендерит представления

Пример из TransactionController.php:

public function index() {
    $this->requireAuth();
    $transactions = $this->transactionModel->findAllByUser($_SESSION['user_id']);
    $this->render('transactions/index', ['transactions' => $transactions]);
}

### Front Controller
Все запросы направляются в public/index.php, где происходит маршрутизация:

if ($request == '/transactions') {
    $controller = new TransactionController($db);
    $controller->index();
}

### Active Record
Каждая модель соответствует таблице в БД и содержит методы findById(), save(), delete().

## Технологический стек и причины выбора

| Технология | Версия | Причина выбора |
|------------|--------|----------------|
| PHP | 8.3 | Современный, быстрый, поддержка ООП, встроенные функции для хеширования |
| MySQL | 8.0 | Надёжная СУБД, поддержка транзакций, внешних ключей |
| Apache | 2.4 | Стандартный веб-сервер, поддержка .htaccess |
| Docker | 20.10+ | Изоляция окружения, воспроизводимость, лёгкий деплой |
| Bootstrap | 5.3 | Быстрая адаптивная вёрстка |
| FontAwesome | 6.0 | Бесплатные иконки |
| PhpSpreadsheet | 1.29 | Генерация Excel-файлов |
| PhpWord | 0.18 | Генерация Word-файлов |

## Возможные ошибки и их устранение

| Ошибка | Решение |
|--------|---------|
| Class "PhpOffice\PhpWord\PhpWord" not found | Выполнить composer require phpoffice/phpspreadsheet phpoffice/phpword или пересобрать Docker: docker compose build --no-cache |
| Docker Desktop is unable to start | Запустить Docker Desktop вручную, проверить службу Docker |
| Unknown database 'finance_db' | Выполнить init/init.sql в phpMyAdmin или пересоздать том: docker compose down -v && docker compose up -d |
| View not found: xxx | Создать недостающий .php файл в app/views/ |
| Access denied for user | Проверить DB_HOST, DB_USER, DB_PASS в docker-compose.yml или в public/index.php |

## Работа с Docker

### Пересборка образа после изменений
docker compose build --no-cache
docker compose up -d

### Добавление нового сервиса
Отредактируйте docker-compose.yml, добавьте новый блок services.

### Изменение версии PHP
В Dockerfile замените FROM php:8.3-apache на нужную версию.

### Просмотр логов
docker compose logs -f
docker compose logs web
docker compose logs db

### Запуск команд внутри контейнера
docker compose exec web bash
docker compose exec db mysql -u root -p

## Безопасность

### Защита от SQL-инъекций
Все запросы к БД используют подготовленные выражения PDO:

$stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);

### Защита от XSS (межсайтовый скриптинг)
При выводе данных используется htmlspecialchars():

<?= htmlspecialchars($category->name) ?>

### Защита от CSRF (межсайтовая подделка запроса)
Во всех формах генерируется и проверяется токен:

$csrf_token = Security::generateCsrfToken();

// В форме
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

// Проверка
if (!Security::verifyCsrfToken($_POST['csrf_token'])) {
    die('Ошибка безопасности');
}

### Хеширование паролей
Используется password_hash() с алгоритмом PASSWORD_DEFAULT (bcrypt):

$hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

### Валидация данных
В контроллерах проверяются email, сумма, дата, ID:

if (!Security::validateEmail($email)) { ... }
$amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
if (!$amount || $amount <= 0) { ... }