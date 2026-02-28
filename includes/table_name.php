<?php
/**
 * Функция возвращает имя таблицы для хранения комментариев.
 * Оно одинаково для обеих баз данных — MySQL и PostgreSQL.
 */

function get_table_name() {
    global $wpdb;
    $table_name = $wpdb->prefix . TABLE_NAME;
    return $table_name;
}