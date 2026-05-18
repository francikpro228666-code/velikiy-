<?php
/**
 * Простая ручная автозагрузка классов
 */

spl_autoload_register(function ($class) {
    // Префикс пространства имен
    $prefix = 'app\\';
    
    // Базовая директория
    $base_dir = __DIR__ . '/../';
    
    // Проверяем, начинается ли класс с нашего префикса
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Получаем относительное имя класса
    $relative_class = substr($class, $len);
    
    // Заменяем разделители пространств на разделители директорий
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Если файл существует, подключаем его
    if (file_exists($file)) {
        require $file;
    }
});