# Инструкция по развёртыванию

## Требования к серверу (минимальные)

- ОС: Windows 10/11, Linux (Ubuntu 20.04+), macOS
- Docker Desktop 4.20+ или Docker Engine 24.0+
- Docker Compose V2
- 2 ГБ ОЗУ (рекомендуется 4 ГБ)
- 10 ГБ свободного места

## Развёртывание через Docker (рекомендуемый способ)

### Шаг 1. Клонирование репозитория
git clone https://github.com/ваш-репозиторий/finance-diary.git
cd finance-diary

### Шаг 2. Настройка переменных окружения (опционально)
Если требуется изменить параметры подключения к БД, создайте файл .env:
DB_HOST=db
DB_NAME=finance_db
DB_USER=finance_user
DB_PASS=finance_pass


### Шаг 3. Запуск контейнеров
docker compose up -d

При первом запуске Docker скачает базовые образы (php:8.3-apache, mysql:8.0, phpmyadmin/phpmyadmin) и соберёт образ приложения. Процесс может занять 2–5 минут.


### Шаг 4. Проверка работоспособности
docker compose ps

Все три контейнера должны быть в статусе Up.

| Контейнер | Статус | Порт |
|-----------|--------|------|
| finance_app | Up | 8080 |
| finance_db | Up | 3307 |
| finance_phpmyadmin | Up | 8081 |


### Шаг 5. Доступ к приложению
- Веб-приложение: http://localhost:8080
- phpMyAdmin: http://localhost:8081 (Сервер: db, Пользователь: root, Пароль: root)

## Развёртывание вручную (без Docker)

### Шаг 1. Установка XAMPP
Скачайте и установите XAMPP с PHP 8.3+ с официального сайта https://www.apachefriends.org/ru/

### Шаг 2. Клонирование репозитория
Склонируйте проект в C:\xampp\htdocs\project

### Шаг 3. Создание базы данных
1. Запустите MySQL через XAMPP Control Panel
2. Откройте phpMyAdmin: http://localhost/phpmyadmin
3. Создайте базу данных finance_db
4. Импортируйте файл init/init.sql


### Шаг 4. Запуск сервера
cd C:\xampp\htdocs\project
php -S localhost:8000 -t public

### Шаг 5. Открытие приложения
http://localhost:8000

## Резервное копирование

### Бэкап тома Docker (данные БД)

Создание бэкапа:
docker run --rm -v finance_db_data:/source -v %cd%:/backup alpine tar czf /backup/db_backup_$(date +%Y%m%d).tar.gz -C /source .

Восстановление:
docker run --rm -v finance_db_data:/target -v %cd%:/backup alpine tar xzf /backup/db_backup_20250517.tar.gz -C /target

### Дамп БД через phpMyAdmin
1. Зайдите в http://localhost:8081
2. Выберите базу finance_db
3. Экспорт → Быстрый метод → SQL → Сохранить


### Сохранение логов
docker compose logs > logs_$(date +%Y%m%d).txt

## Обновление приложения

### Через Docker
git pull
docker compose down
docker compose build --no-cache
docker compose up -d

### Через локальный PHP-сервер
git pull
# Если изменилась структура БД – выполните init/init.sql заново
php -S localhost:8000 -t public

## Устранение неполадок

| Проблема | Решение |
|----------|---------|
| Порт 8080 уже занят | Измените порт в docker-compose.yml на другой (например, "8082:80") |
| Ошибка Connection refused при подключении к БД | Проверьте, что контейнер db запущен: docker compose ps |
| БД пустая (нет таблиц) | Выполните docker compose down -v и снова docker compose up -d – скрипт init.sql выполнится заново |
| Не скачиваются отчёты | В Docker пересоберите образ: docker compose build --no-cache. Локально – выполните composer install |
| Ошибка 403 при открытии phpMyAdmin | Проверьте, что сервер в phpMyAdmin указан как db, а не localhost |