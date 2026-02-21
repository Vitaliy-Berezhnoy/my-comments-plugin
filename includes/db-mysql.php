<?php

/**
 * Функции для работы с базой данных плагина
 */

/**
 * Создаёт таблицу для комментариев при активации плагина
 */
function create_table_in_mysql() {
    global $wpdb;
    $table_name = $wpdb->prefix . TABLE_NAME;

    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        comment text NOT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
