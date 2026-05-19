<?php
/**
 * Функция регистрации маршрута через REST API для приёма отчётов CSP.
 */
function add_csp_reports_api_endpoint() {
    register_rest_route(
        'my-csp/v1', // Пространство имен (namespace)
        '/report',   // Название маршрута (route)
        array(
            'methods'  => 'POST', // Принимаем только POST
            'callback' => 'handle_csp_report', // Функция-обработчик
            'permission_callback' => '__return_true', // Разрешаем всем (можно ограничить)
        )
    );
}


/**
 * Функция-обработчик входящего отчёта CSP.
 *
 * @param WP_REST_Request $request Объект запроса.
 * @return WP_REST_Response Ответ сервера.
 */
function handle_csp_report(WP_REST_Request $request) {
    // Получаем тело запроса (JSON)
    $body = $request->get_body();
    $report = json_decode($body, true);

    // Формируем строку для лога с таймстампом
    $log_entry = date('Y-m-d H:i:s') . " - " . print_r($report, true) . "\n";

    // Логируем отчёт в файл wp-content/logs/csp-reports.log
    $log_path = WP_CONTENT_DIR . '/logs';
    if ( ! is_dir($log_path) )  {
        wp_mkdir_p($log_path);  // Безопасное создание директории
    }
    $log_path .= '/csp-reports.log';
    file_put_contents($log_path, $log_entry, FILE_APPEND);

    // Отправляем ответ 204 No Content (как ожидает браузер)
    return new WP_REST_Response(null, 204);
}


/**
 * Функция добавляет заголовок Content-Security-Policy-Report-Only.
 */

function add_csp_header() {
    // Генерируем URL для отчётов о нарушениях CSP
    $report_uri = rest_url('my-csp/v1/report');

    $directives = [
        "default-src 'self'",
        "style-src 'self' 'unsafe-inline'",
        "script-src 'self' 'unsafe-inline'",
        "img-src 'self' data:",
        "font-src 'self' data:"
    ];

    $csp_policy = join('; ', $directives) . ';';

    // Собираем полный заголовок
    $header_value = "Content-Security-Policy-Report-Only: {$csp_policy} report-uri {$report_uri};";

    // Отправляем заголовок в браузер
    header($header_value);
}
