<?php
/**
 * @var string $assets_url url директории assets/
 */

function enqueue_bootstrap() {
    $assets_url = plugin_dir_url(__DIR__) . 'assets/';
    // CSS
    wp_enqueue_style(
        'bootstrap-local',
        "{$assets_url}css/bootstrap.min.css",
        [],
        '5.3.8'  // версия Bootstrap
    );

    // JS Bootstrap Bundle (включает Popper.js)
    wp_enqueue_script(
        'bootstrap-bundle-local',
        "{$assets_url}js/bootstrap.bundle.min.js",
        [], 
        '5.3.8',
        true // Загружаем в футере для улучшения производительности
    );
}