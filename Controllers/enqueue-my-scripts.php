<?php

function my_comments_enqueue_scripts() {
    // Получаем URL директории assets/js/
    $js_url = plugin_dir_url(__DIR__) . 'assets/js/';
    $js_path = plugin_dir_path(__DIR__) . 'assets/js/';

    wp_enqueue_script(
        'comment-form-script',
        "{$js_url}comment-form-script.js",
        [],
        // Используем время модификации файла как версию
        filemtime($js_path . 'comment-form-script.js'),  // Версия скрипта
        true
    );

    wp_enqueue_script(
        'comments-table-script',
        "{$js_url}comments-table-script.js",
        [],
        filemtime($js_path . 'comments-table-script.js'),
        true
    );

    // Подключаем скрипт для переключателя БД
    // Передаем в JavaScript имя активной БД
    wp_enqueue_script(
        'db-switcher-script',
        "{$js_url}db-switcher-script.js",
        [],
        filemtime($js_path . 'db-switcher-script.js'),
        true
    );
    wp_localize_script(
        'db-switcher-script',
        'nameActiveDb',
        ['currentDb' => get_name_active_db()]  // Текущая активная БД
    );
}