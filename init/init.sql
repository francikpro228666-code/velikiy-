-- ============================================
-- ИНИЦИАЛИЗАЦИЯ БАЗЫ ДАННЫХ ДЛЯ DOCKER
-- ============================================

-- 1. Создаём базу данных, если её нет
CREATE DATABASE IF NOT EXISTS finance_db;
USE finance_db;

-- 2. Создаём пользователя для приложения (если не существует)
CREATE USER IF NOT EXISTS 'finance_user'@'%' IDENTIFIED BY 'finance_pass';
GRANT ALL PRIVILEGES ON finance_db.* TO 'finance_user'@'%';
FLUSH PRIVILEGES;

-- ============================================
-- 3. ТАБЛИЦЫ (структура)
-- ============================================

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    currency VARCHAR(3) DEFAULT 'RUB',
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица категорий (доходы и расходы)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    parent_id INT DEFAULT NULL,
    icon VARCHAR(50) DEFAULT 'fa-tag',
    color VARCHAR(7) DEFAULT '#000000',
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Таблица транзакций
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    is_recurring BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Таблица бюджетных лимитов
CREATE TABLE IF NOT EXISTS budget_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    month DATE NOT NULL,
    limit_amount DECIMAL(10,2) NOT NULL,
    current_spent DECIMAL(10,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Таблица финансовых целей
CREATE TABLE IF NOT EXISTS goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    target_amount DECIMAL(10,2) NOT NULL,
    current_amount DECIMAL(10,2) DEFAULT 0,
    deadline DATE,
    is_completed BOOLEAN DEFAULT FALSE,
    color VARCHAR(7) DEFAULT '#17a2b8',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица пополнений целей
CREATE TABLE IF NOT EXISTS goal_deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    goal_id INT NOT NULL,
    transaction_id INT DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    deposited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (goal_id) REFERENCES goals(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL
);

-- ============================================
-- 4. НАЧАЛЬНЫЕ ДАННЫЕ
-- ============================================

-- Категории доходов
INSERT IGNORE INTO categories (name, type, icon, color) VALUES
('Зарплата', 'income', 'fa-money-bill-wave', '#28a745'),
('Фриланс', 'income', 'fa-laptop-code', '#20c997'),
('Подарки', 'income', 'fa-gift', '#17a2b8'),
('Инвестиции', 'income', 'fa-chart-line', '#6f42c1'),
('Кэшбэк', 'income', 'fa-percent', '#fd7e14');

-- Категории расходов
INSERT IGNORE INTO categories (name, type, icon, color) VALUES
('Продукты', 'expense', 'fa-apple-alt', '#dc3545'),
('Транспорт', 'expense', 'fa-bus', '#fd7e14'),
('Кафе и рестораны', 'expense', 'fa-utensils', '#6f42c1'),
('Развлечения', 'expense', 'fa-film', '#e83e8c'),
('Здоровье', 'expense', 'fa-heartbeat', '#20c997'),
('Квартплата', 'expense', 'fa-home', '#ffc107'),
('Связь и интернет', 'expense', 'fa-wifi', '#17a2b8'),
('Одежда', 'expense', 'fa-tshirt', '#fd7e14'),
('Образование', 'expense', 'fa-graduation-cap', '#007bff'),
('Прочее', 'expense', 'fa-ellipsis-h', '#6c757d');

-- Тестовый пользователь (пароль: 123456)
-- Хеш пароля: password_hash('123456', PASSWORD_DEFAULT) -> $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT IGNORE INTO users (username, email, password, full_name, currency, role) VALUES
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Тестовый Пользователь', 'RUB', 'user');

-- Тестовые транзакции (для user_id = 1)
INSERT IGNORE INTO transactions (user_id, category_id, amount, date, description) VALUES
(1, 1, 50000.00, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'Зарплата'),
(1, 6, 3500.00, DATE_SUB(CURDATE(), INTERVAL 14 DAY), 'Продукты'),
(1, 7, 800.00, DATE_SUB(CURDATE(), INTERVAL 13 DAY), 'Транспорт'),
(1, 8, 1200.00, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'Кафе'),
(1, 9, 500.00, DATE_SUB(CURDATE(), INTERVAL 8 DAY), 'Кино');

-- Тестовые бюджетные лимиты (на текущий месяц)
INSERT IGNORE INTO budget_limits (user_id, category_id, month, limit_amount, current_spent) VALUES
(1, 6, DATE_FORMAT(CURDATE(), '%Y-%m-01'), 15000, 3500),
(1, 7, DATE_FORMAT(CURDATE(), '%Y-%m-01'), 2000, 800),
(1, 8, DATE_FORMAT(CURDATE(), '%Y-%m-01'), 5000, 1200);

-- Тестовая финансовая цель
INSERT IGNORE INTO goals (user_id, name, target_amount, current_amount, deadline, color) VALUES
(1, 'Новый ноутбук', 80000, 25000, DATE_ADD(CURDATE(), INTERVAL 6 MONTH), '#17a2b8');