<?php
/**
 * Функции для подключения к БД MySQL и PostgreSQL с использованием PDO
 */

function pdo_connect_mysql() {
    // Используем константы WordPress для получения данных подключения
    $db_name = DB_NAME;
    $db_user = DB_USER;
    $db_password = DB_PASSWORD;
    $db_host = DB_HOST;

    // DSN (Data Source Name) для MySQL
    $dns = "mysql:host={$db_host};dbname={$db_name}";

    // Опции PDO для улучшения работы и безопасности
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Выбрасывать исключения при ошибках
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Возвращать массивы как ассоциативные
        PDO::ATTR_EMULATE_PREPARES => false,  // Использовать настоящие подготовленные запросы
    ];

    try {
        $pdo = new PDO($dns, $db_user, $db_password, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Логируем ошибку, но не показываем пользователю чувствительные данные
        error_log('PDO Connection to MySQL Error in MyPlugin: ' . $e->getMessage());
        return false;  // Надо или нет выбросить исключение дальше ???
    }
}