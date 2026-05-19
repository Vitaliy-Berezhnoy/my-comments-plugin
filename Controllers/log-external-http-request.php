<?php

// function save_logs_old( $response, $context, $transport, $args, $url) {
//     // Проверяем запрос, внутренний (на наш домен) или внешний
//     $site_host = parse_url( get_site_url(), PHP_URL_HOST );
//     $request_host = parse_url( $url, PHP_URL_HOST );
    
//     // Если запрос на внешний сервер — логируем
//     if ( $site_host !== $request_host && ! empty( $request_host ) ) {
//         $log_entry = sprintf(
//             "[%s] ВНЕШНИЙ ЗАПРОС: %s\nИнициатор: %s\n---\n",
//             date( 'Y-m-d H:i:s' ),
//             $url,
//             $context
//         );
        
//         // Пишем в файл лога в wp-content/logs/external-requests.log
//         $log_path = WP_CONTENT_DIR . '/logs';
//         if ( ! is_dir($log_path) )  {
//             wp_mkdir_p($log_path);  // Безопасное создание директории
//         }
//         $log_path .= '/external-requests.log';
//         file_put_contents( $log_path , $log_entry, FILE_APPEND );
//     }
    
//     return $response;
// }


/**
 * Логирует внешние HTTP-запросы WordPress
 *
 * Функция подключается к хуку 'http_api_debug' и записывает информацию
 * о запросах на внешние ресурсы в файл лога.
 * 
 * @param mixed $response Ответ от удалённого сервера (WP_Error или массив с ответом)
 * @param string $context Контекст запроса (например, 'response', 'request')
 * @param string $transport транспортный обработчик ('curl', 'streams', 'fsockopen')
 * @param array $args аргументы запроса (массив: url, method, headers, body и др.)
 * @param string $url URL, на который был отправлен запрос
 * @return void
 */

function log_external_http_request( $response, $context, $transport, $args, $url ) {
    // Определяем хост текущего сайта и хоста запроса
    $site_host = parse_url( get_site_url(), PHP_URL_HOST );
    $request_host = parse_url( $url, PHP_URL_HOST );

    // Логируем только внешние запросы
    if ( $site_host !== $request_host && ! empty( $request_host ) ) {
        // Формируем безопасную запись для лога
        $log_entry = sprintf(
            "[%s] ВНЕШНИЙ ЗАПРОС: %s\nИнициатор: %s\nТранспорт: %s\nАргументы: %s\n---\n",
            date( 'Y-m-d H:i:s' ),
            esc_url_raw( $url ),
            esc_html( $context ),
            esc_html( $transport ),
            wp_json_encode( $args, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
        );

        // Путь к директории логов (можно переопределить через фильтр)
        // $log_dir = apply_filters( 'external_requests_log_dir', WP_CONTENT_DIR . '/logs' );
        $log_dir = WP_CONTENT_DIR . '/logs';

        // Создаём директорию, если её нет
        if ( ! is_dir( $log_dir ) ) {
            wp_mkdir_p( $log_dir );
        }

        // Полный путь к файлу лога
        $log_path_file = trailingslashit( $log_dir ) . 'external-requests.log';

        // Пытаемся записать лог, обрабатываем возможные ошибки
        if ( false === file_put_contents( $log_path_file, $log_entry, FILE_APPEND ) ) {
            error_log( 'Ошибка записи в файл лога внешних запросов: ' . $log_path_file );
        }
    }

    return $response;
}

// Подключаем функцию к хуку WordPress
add_filter( 'http_api_debug', 'log_external_http_request', 10, 5 );