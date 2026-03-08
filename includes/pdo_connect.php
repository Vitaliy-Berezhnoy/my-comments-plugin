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
        error_log('PDO Connection to MySQL in MyPlugin Error: ' . $e->getMessage());
        return false;  // Как жить дальше если соеденение не установленно ???
    }
}

function pdo_connect_postgresql() {
    // Используем константы WordPress для получения данных подключения
    $db_name = PG_DB_NAME;
    $db_user = PG_DB_USER;
    $db_password = PG_DB_PASSWORD;
    $db_host = PG_DB_HOST;
    $db_port = PG_DB_PORT;

    // DSN (Data Source Name) для PostgreSQL
    $dns = "pgsql:host={$db_host};port={$db_port};dbname={$db_name}";

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
        error_log('PDO Connection to PostgreSQL in MyPlugin Error: ' . $e->getMessage());
        return false;
    }
}