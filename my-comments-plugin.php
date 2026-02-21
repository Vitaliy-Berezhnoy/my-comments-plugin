<?php
/**
 * Plugin Name: Simple comments
 * Description: Форма и список комментариев
 * Version: 1.0.0
 * Author: Vitaliy Berezhnoy
 */

// Название таблицы для хранения комментариев одинаковое в обеих БД.
const TABLE_NAME = 'proposals_and_comments';

require_once plugin_dir_path(__FILE__) . 'Database/PG_DB_Handler.php';


// Создаём таблицу в БД Mysql при активации плагина
if ( !function_exists('create_table_in_mysql')) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/db-mysql.php';
}
register_activation_hook( __FILE__, 'create_table_in_mysql' );

// Создаём таблицу в БД PostgreSQL при активации плагина
if (!function_exists('create_table_in_postgresql')) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/db-postgresql.php';
}
register_activation_hook( __FILE__, 'create_table_in_postgresql' );

// Начинаем сессию
add_action('init', function() {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
});
// Обработка комментария
add_action('init', 'save_сomment');

// Подключаем bootstrap-local
add_action('wp_enqueue_scripts', function() {
    // CSS
    wp_enqueue_style(
        'bootstrap-local',
        plugin_dir_url(__FILE__) . 'assets/css/bootstrap.min.css',
        [],
        '5.3.8'  // версия Bootstrap
    );
});

add_shortcode('show_comments', 'comments_shortcode');

function save_сomment() {
    // Проверяем, что это POST запрос с нашей формы
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_comment'])) {
        return false;
    }

    // Проверяем nonce
    if (!isset($_POST['add_comment_nonce']) || !wp_verify_nonce($_POST['add_comment_nonce'], 'add_comment_action')) {
        return false;
    }

    // Санизируем данные
    $name = sanitize_text_field($_POST['comment_name']);
    $comment = sanitize_textarea_field($_POST['comment_text']);

    //  Проверяем обязательные поля
    if (empty($name) || empty($comment)) {
        $_SESSION['comment_status'] = [
            'success' => false,
            'type' => 'warning',
            'message' => 'Заполните все поля!'
        ];
        return false;
    }

    // Сохраняем в БД
    global $wpdb;
    $table_name = $wpdb->prefix . TABLE_NAME;

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
        $_SESSION['comment_status'] = [
            'success' => true,
            'type' => 'success',
            'message' => 'Комментарий добавлен!'
        ];
    } else {
        $_SESSION['comment_status'] = [
            'success' => false,
            'type' => 'error',
            'message' => 'Ошибка при записи комментария в БД!'
        ];
        return false;
    }
    // Редирект ДО начала вывода контента
    wp_safe_redirect($_SERVER['REQUEST_URI']);
    exit;
}

// Функция для отображения таблицы
function display_comments_table($table_id = 'comments-table', $per_page = 5) {
    global $wpdb;
    $table_name = $wpdb->prefix . TABLE_NAME;

    // Определяем текущую страницу, учитывая оба формата URL
    if (get_query_var('paged')) {
        $current_page = get_query_var('paged');
    } elseif (get_query_var('page')) {
        $current_page = get_query_var('page');
    } else {
        $current_page = 1;
    }
    $current_page = max(1, intval($current_page));

    // Определяем смещение для SQL-запроса
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

    // Проверяем сообщения в сессии
    if (isset($_SESSION['comment_status'])) {
        include plugin_dir_path(__FILE__) . 'templates/notification.php';
        $commentSaveSuccess = $_SESSION['comment_status']['success'];
        // Очищаем сообщения
        unset($_SESSION['comment_status']);
    }

    // Выводим форму
    include plugin_dir_path(__FILE__) . 'templates/comment-form.php';

    // Выводим таблицу комментариев ТОЛЬКО если:
    // - не было отправки формы ИЛИ
    // - отправка прошла успешно
    if (!isset($commentSaveSuccess) || $commentSaveSuccess) {
        display_comments_table();
    }

    return ob_get_clean();
}
