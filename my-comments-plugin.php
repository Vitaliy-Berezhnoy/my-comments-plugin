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
//require_once plugin_dir_path(__FILE__) . 'Database/PG_DB_Handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/pdo_connect.php';
require_once plugin_dir_path(__FILE__) . 'includes/selected_db.php';
require_once plugin_dir_path(__FILE__) . 'includes/comment_deletion.php';

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

// Обработка комментария
add_action('init', 'process_and_save_comment');

// Обработка переключателя БД
add_action('init', 'handle_db_switch_request');

add_action('init', 'process_comment_deletion_request');
add_action('init', 'handle_comment_deletion_confirmation');

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

// Функция для обработки POST запроса от формы ввода комментария
function process_and_save_comment() {
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
        set_transient(
            'comment_status',
            [
                'type' => 'warning',
                'message' => 'Заполните все поля!'
            ],
            180  // время жизни в секундах
        );
        return false;
    }

    // Сохраняем комментарий в одну из БД PostgreSQL или MySQL
    $table_name = get_table_name();
    $pdo = get_pdo_active_db();

    $sql = "INSERT INTO $table_name (name, comment) VALUES (:name, :comment);";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $comment_status = [
            'type' => 'success',
            'message' => 'Комментарий добавлен!'
        ];
        $pdo = null;
        $stmt = null;
    } catch(PDOException $e) {
        error_log(message: "Error writing a comment to the database: " . $e);
        $comment_status = [
            'type' => 'error',
            'message' => 'Ошибка при записи комментария в БД!'
        ];
        $pdo = null;
        $stmt = null;
    }

    // Временно сохраняем сообщение об успехе/ошибке
    set_transient('comment_status', $comment_status, 180);  // время жизни 180 секунд

    // Редирект ДО начала вывода контента
    wp_safe_redirect($_SERVER['REQUEST_URI']);
    exit;
}

// Функция для обработки POST запроса от формы переключения БД
function handle_db_switch_request() {
    if (isset($_POST['save_db_choice']) && wp_verify_nonce($_POST['save_db_choice_nonce'], 'save_db_choice_action')) {
        $selected_db = sanitize_text_field($_POST['db_choice']);

        // Сохраняем в cookie (на 3 дня)
        setcookie(
            'current_db',
            $selected_db,
            time() + (86400 * 3),
            '/',
            '',
            false,
            true
        );

        // Редирект ДО начала вывода контента
        wp_safe_redirect($_SERVER['REQUEST_URI']);
        exit;
    }
    return false;
}

// Функция для отображения таблицы
function display_comments_table($table_id = 'comments-table', $per_page = 5) {
    $table_name = get_table_name();

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

    // Определяем общее количество строк в таблице
    $pdo = get_pdo_active_db();
    $sql1 = "SELECT COUNT(*) AS total FROM $table_name";
    $stmt = $pdo->query($sql1);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $row ? (int)$row['total'] : 0;

    // Получаем комментарии с лимитом
    $sql2 = "SELECT * FROM $table_name
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset";
    $stmy2 = $pdo->prepare($sql2);
    $stmy2->bindParam(':limit', $per_page, PDO::PARAM_INT);
    $stmy2->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmy2->execute();
    $comments = $stmy2->fetchAll(PDO::FETCH_OBJ);


    // Передаем данные для пагинации в шаблон
    $pagination_data = [
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $current_page
    ];

    include plugin_dir_path(__FILE__) . 'templates/comments-table.php';

    return true;
}

function comments_shortcode() {
    ob_start();

    // Получаем ID удляемых комментариев из transient
    $comment_ids = get_transient('comment_ids');

    // Если есть ID выводим форму подтверждения удаления
    if ($comment_ids) {
        render_comment_deletion_confirmation_form($comment_ids);

        delete_transient('comment_ids');  // Удаляем transient после использования

        return ob_get_clean();   // Больше ни чего не выводим
    }

    // Получаем статус комментария из transient 
    $comment_status = get_transient('comment_status');

    // Если есть статус - показываем уведомление
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
