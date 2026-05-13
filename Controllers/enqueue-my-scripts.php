<?php

function my_comments_enqueue_scripts() {
    // Получаем URL директории assets/js/
    $js_url = plugin_dir_url(__DIR__) . 'assets/js/';

    wp_enqueue_script(
        'comment-form-script',
        "{$js_url}comment-form-script.js",
        [],
        '1.0',
        true
    );

    wp_enqueue_script(
        'comments-table-script',
        "{$js_url}comments-table-script.js",
        [],
        '1.0',
        true
    );

    // Подключаем скрипт для переключателя БД
    // Передаем в JavaScript имя активной БД
    wp_enqueue_script(
        'db-switcher-script',
        "{$js_url}db-switcher-script.js",
        [],
        '1.0',
        true
    );
    wp_localize_script(
        'db-switcher-script',
        'nameActiveDb',
        ['currentDb' => get_name_active_db()]  // Текущая активная БД
    );


    //     wp_enqueue_script(
    //     'my-globals',
    //     "{$js_url}globals.js",
    //     [],
    //     '1.0',
    //     true
    // );

    // // Логика выбора/отметки комментариев и работы с sessionStorage
    // wp_enqueue_script(
    //     'my-comments-selection',
    //     "{$js_url}comment-selection.js",
    //     [],     // зависимости
    //     '1.0',  // версия
    //     true    // загружать в футере       
    // );

    // // Обновление интерфейса (счётчики, состояния кнопок)
    // wp_enqueue_script(
    //     'my-ui-updates',
    //     "{$js_url}ui-updates.js",
    //     [],
    //     '1.0',
    //     true
    // );

    // // Обработка отправки формы удаления комментариев
    // // и взаимодействия с модальным окном подтверждения удаления
    // wp_enqueue_script(
    //     'my-handlers-form-delete',
    //     "{$js_url}handlers-form-delete.js",
    //     [],
    //     '1.0',
    //     true
    // );

    // // Инициализация при загрузке страницы
    // wp_enqueue_script(
    //     'my-init',
    //     "{$js_url}init.js",
    //     [],
    //     '1.0',
    //     true
    // );
}