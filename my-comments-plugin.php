<?php
/**
 * Plugin Name: Simple comments
 * Description: Форма и список комментариев
 * Version: 1.0.0
 * Author: Vitaliy Berezhnoy
 */

// Название таблицы для хранения комментариев одинаковое в обеих БД.
const TABLE_NAME = 'proposals_and_comments';

require_once plugin_dir_path(__FILE__) . 'includes/table_name.php';
require_once plugin_dir_path(__FILE__) . 'includes/pdo_connect.php';
require_once plugin_dir_path(__FILE__) . 'includes/selected_db.php';
require_once plugin_dir_path(__FILE__) . 'includes/route-post-actions.php';
require_once plugin_dir_path(__FILE__) . 'includes/forms-and-tables.php';
require_once plugin_dir_path(__FILE__) . 'includes/creating-tables-in-db.php';

/**
 * При активации плагина проверяем наличие таблиц
 * для комментариев в базах MySQL и PostgreSQL.
 * При отсутствии создаём, при наличии оставляем.
 */
register_activation_hook( __FILE__, 'create_tables_on_activation' );

//  Обрабатываем POST запросы
add_action('init', 'route_post_actions');


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


function comments_shortcode() {
    ob_start();

    // Получаем из transient ID комментариев, помеченных на удаление
    $comment_ids = get_transient('comment_ids');

    // Если ID есть, выводим форму подтверждения удаления.
    if ($comment_ids) {
        render_comment_deletion_confirmation_form($comment_ids);

        delete_transient('comment_ids');  // Удаляем transient после использования

        return ob_get_clean();   // Больше ни чего не выводим
    }

    // Получаем из transient статус комментария. 
    $comment_status = get_transient('comment_status');

    // Если статус есть — показываем уведомление.
    if ($comment_status) {
        include plugin_dir_path(__FILE__) . 'templates/notification.php';        
        delete_transient('comment_status');    // Удаляем transient после использования
    }

    // Выводим форму ввода комментария
    include plugin_dir_path(__FILE__) . 'templates/comment-form.php';

    // Выводим форму переключателя БД
    $current_db = get_name_active_db();    // Передаём в форму имя текущей БД.
    include plugin_dir_path(__FILE__) . 'templates/db-switcher.php';

    // Выводим таблицу с комментариями, которая также служит формой для их удаления.
    display_comments_table();

    return ob_get_clean();
}
