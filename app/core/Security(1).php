<?php
namespace app\core;

class Security {
    /**
     * Защита от XSS атак (очистка ввода)
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Валидация email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Валидация суммы (положительное число)
     */
    public static function validateAmount($amount) {
        return is_numeric($amount) && $amount > 0;
    }
    
    /**
     * Валидация даты
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Генерация CSRF токена
     */
    public static function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Проверка CSRF токена
     */
    public static function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Защита от массового присвоения
     */
    public static function filterFields($data, $allowedFields) {
        return array_intersect_key($data, array_flip($allowedFields));
    }
    
    /**
     * Валидация ID
     */
    public static function validateId($id) {
        return filter_var($id, FILTER_VALIDATE_INT) && $id > 0;
    }
} 
