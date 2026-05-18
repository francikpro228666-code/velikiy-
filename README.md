# Финансовый дневник — учёт личных финансов

## Краткое описание
Веб-приложение для ведения бюджета: учёт доходов/расходов, планирование бюджета, финансовые цели, отчёты в Excel/Word.

## Команда
- Штрак Р.О.
- Гладких В.Е.
- Дудина Я.А.

## Технологический стек
- **Backend:** PHP 8.3, MySQL 8.0, Apache
- **Frontend:** HTML5, Bootstrap 5, FontAwesome, JavaScript
- **Библиотеки:** PhpSpreadsheet, PhpWord
- **Контейнеризация:** Docker, Docker Compose

## Инструкция по установке и запуску

### Локальный запуск
1. Установите XAMPP с PHP 8.3+
2. Склонируйте репозиторий в `C:\xampp\htdocs\project`
3. Импортируйте `init/init.sql` в phpMyAdmin
4. Запустите сервер: `php -S localhost:8000 -t public`
5. Откройте `http://localhost:8000`

### Запуск через Docker
```bash
docker compose up -d