<?php
// Настройки подключения к БД (XAMPP: пароль по умолчанию пустой)
$host = 'localhost';
$dbname = 'finance_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Создаём таблицу users, если её нет
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(255),
        currency VARCHAR(3) DEFAULT 'RUB',
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Таблица 'users' создана или уже существует.<br>";
    
    // Добавляем поле role, если его нет
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('user','admin') DEFAULT 'user'");
        echo "✅ Поле 'role' добавлено.<br>";
    } catch (PDOException $e) {
        echo "ℹ️ Поле 'role' уже есть или не требуется.<br>";
    }
    
    echo "<hr><a href='/login'>Перейти к входу</a>";
} catch (PDOException $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}
?>