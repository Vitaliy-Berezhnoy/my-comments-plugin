<?php

function get_name_active_db() {
    if (isset($_COOKIE['current_db'])) {
        return sanitize_text_field($_COOKIE['current_db']);
    }
    return 'mysql';     // БД по умолчанию mysql
}

function get_pdo_active_db () {
    $name_active_db = get_name_active_db();
    if ($name_active_db === 'postgres') {
        return pdo_connect_postgresql();
    }
    return pdo_connect_mysql();
}