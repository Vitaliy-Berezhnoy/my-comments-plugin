<?php
/**
 * Plugin Name: Simple comments
 * Description: Форма и список комментариев
 * Version: 1.0.0
 * Author: Vitaliy Berezhnoy
 */

// Подключаем файл с функциями БД
if ( ! function_exists('create_proposals_comments_table')) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/database.php';
}


// Создаём таблицу при активации
register_activation_hook( __FILE__, 'create_proposals_comments_table' );

// Обработчик отправки формы
function handle_comment_submission() {
    // Проверяем nonce
    if (!isset($_POST['add_comment_nonce']) || !wp_verify_nonce($_POST['add_comment_nonce'], 'add_comment_action')) {
        return;
    }

    // Проверяем метод запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_comment'])) {
        return;
    }

    // Санизируем данные
    $name = sanitize_text_field($_POST['comment_name']);
    $comment = sanitize_textarea_field($_POST['comment_text']);

    //  Проверяем обязательные поля
    if (empty($name) || empty($comment)) {
        return ['success' => false, 'message' => 'Заполните все поля.'];
    }

    // Сохраняем в БД
    global $wpdb;
    $table_name = $wpdb->prefix . 'proposals_and_comments';

    $result = $wpdb->insert(
        $table_name,
        array(
            'name' => $name,
            'comment' => $comment
        ),
        array('%s', '%s')
    );

    // Сообщение об успехе/ошибке
    if ($result) {
        return ['success' => true, 'message' => 'Комментарий добавлен!'];
    }
    return ['success' => false, 'message' => 'Ошибка при добавлении комментария.'];
}

// Функция для отображения таблицы
function display_comments_table($table_id = 'comments-table', $per_page = 5) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'proposals_and_comments';

    // Текущая страница
    $current_page = max(1, get_query_var('paged', 1));
    $offset = ($current_page - 1) * $per_page;

    // Общее количество
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Получаем комментарии с лимитом
    $comments = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name
             ORDER BY created_at DESC
             LIMIT %d OFFSET %d",
            $per_page, $offset
        )
    );

    // Передаем данные для пагинации в шаблон
    $pagination_data = [
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $current_page
    ];

    include plugin_dir_path(__FILE__) . 'templates/comments-table.php';
}

function comments_shortcode() {
    ob_start();
    
    // Обрабатываем отправку и получаем результат
    $submission_result = handle_comment_submission();

    // Если была отправка, показываем уведомление
    if ($submission_result) {
        include plugin_dir_path(__FILE__) . 'templates/notification.php';
    }

    // Выводим форму (передаём предыдущие значения, если они есть)
    include plugin_dir_path(__FILE__) . 'templates/comment-form.php';

    // Выводим таблицу комментариев ТОЛЬКО если:
    // - не было отправки формы ИЛИ
    // - отправка прошла успешно
    if (!$submission_result || $submission_result['success']) {
        display_comments_table();
    }

    return ob_get_clean();
}


add_shortcode('show_comments', 'comments_shortcode');

add_action('wp_enqueue_scripts', function() {
    // CSS
    wp_enqueue_style(
        'bootstrap-local',
        plugin_dir_url(__FILE__) . 'assets/css/bootstrap.min.css',
        [],
        '5.3.8'  // версия Bootstrap
    );
});