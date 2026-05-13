<?php
/**
 * Plugin Name: Simple comments
 * Description: Форма и список комментариев
 * Version: 1.0.0
 * Author: Vitaliy Berezhnoy
 */

/*
*my-comments-plugin/
*├── assets
*│   ├── css/
*|   |   └── bootstrap.min.css           # Mинифицированный CSS‑файл фреймворка Bootstrap
*|   |
*│   ├── js/
*|   |   ├── bootstrap.bundle.min.js     # Минифицированная JavaScript‑библиотека фреймворка Bootstrap
*|   |   ├── comment-form-script.js      #
*|   |   ├── comments-table-script.js    #
*|   |   └── db-switcher-script.js       #
*|   |
*├── Models/
*│   ├── creating-tables-in-db.php       # Создаёт таблицы для хранения комментариев в MySQL и PostgreSQL.
*│   ├── pdo-connect.php                 # Создаёт подключения к БД MySQL и PostgreSQL с использованием PDO
*│   ├── SimpleComments.php              # Модель для работы с комментариями
*│   └── table-name.php                  # Определяет имя таблицы для хранения комментариев
*|
*├── Controllers/
*│   ├── enqueue-bootstrap.php           # Подключение bootstrap 5
*│   ├── enqueue-my-scripts.php          # Подключение скриптов актиации кнопок и соханения ID в sessionStorage  
*│   ├── name-active-db.php              # Определяет имя активной БД
*│   ├── prepare-comments-for-view.php   # Подготовка данных для отображения таблицы с комментариями
*│   └── route-post-actions.php          # Маршрутизация и обработка POST запросов
*│
*├── View/
*│   ├── modal-window/                   # Модальные окна
*|   |   ├── confirm-delete.php          # Окно подтверждения удаления комментариев
*|   |   ├── fields-not-filled.php       # Окно с просьбой заполнить все поля
*|   |   └── notification.php            # Окно для сообщений успех/внимание/ошибка 
*|   |
*│   └── templates/                      # Шаблоны
*|       ├── comment-form.php            # Форма для ввода комментария
*|       ├── comments-table.php          # Форма для вывода таблицы с комментариями
*|       ├── db-switcher.php             # Форма для выбора БД
*|       └── pagination.php              # Пагинация для таблицы с комментариями
*|
*└── my-comments-plugin.php              # Главный файл
*/

// Название таблицы для хранения комментариев одинаковое в обеих БД.
const TABLE_NAME = 'proposals_and_comments';

require_once plugin_dir_path(__FILE__) . 'Models/SimpleComments.php';
require_once plugin_dir_path(__FILE__) . 'Models/table-name.php';
require_once plugin_dir_path(__FILE__) . 'Models/pdo-connect.php';
require_once plugin_dir_path(__FILE__) . 'Models/creating-tables-in-db.php';
require_once plugin_dir_path(__FILE__) . 'Controllers/enqueue-bootstrap.php';
require_once plugin_dir_path(__FILE__) . 'Controllers/enqueue-my-scripts.php';
require_once plugin_dir_path(__FILE__) . 'Controllers/name-active-db.php';
require_once plugin_dir_path(__FILE__) . 'Controllers/route-post-actions.php';
require_once plugin_dir_path(__FILE__) . 'Controllers/prepare-comments-for-view.php';



/**
 * При активации плагина проверяем наличие таблиц
 * для комментариев в базах MySQL и PostgreSQL.
 * При отсутствии создаём, при наличии оставляем.
 */
register_activation_hook( __FILE__, 'create_tables_on_activation' );

//  Обрабатываем POST запросы
add_action('init', 'route_post_actions');

// Подключаем bootstrap-local после стилей темы hello-biz.
// Если ипользовать приоритет 10 (по умолчанию) тема hello-biz переопределит стили кнопок.
add_action('wp_enqueue_scripts', 'enqueue_bootstrap', 11);

// Подключаем скрипты для обработки страницы на стороне клиента. (в браузере)
add_action('wp_enqueue_scripts', 'my_comments_enqueue_scripts');

// Для контроля за трансграничной передачей, логируем запросы на внешние сервера
add_action( 'http_api_debug', function( $response, $context, $transport, $args, $url ) {
    // Проверяем запрос, внутренний (на наш домен) или внешний
    $site_host = parse_url( get_site_url(), PHP_URL_HOST );
    $request_host = parse_url( $url, PHP_URL_HOST );
    
    // Если запрос на внешний сервер — логируем
    if ( $site_host !== $request_host && ! empty( $request_host ) ) {
        $log_entry = sprintf(
            "[%s] ВНЕШНИЙ ЗАПРОС: %s\nИнициатор: %s\n---\n",
            date( 'Y-m-d H:i:s' ),
            $url,
            $context
        );
        
        // Пишем в файл лога в корне wp-content
        file_put_contents( WP_CONTENT_DIR . '/external-requests.log', $log_entry, FILE_APPEND );
    }
    
    return $response;
}, 10, 5 );

add_shortcode('show_comments', 'comments_shortcode');


function comments_shortcode() {
    ob_start();

    // Выводим форму для ввода комментария
    include plugin_dir_path(__FILE__) . 'View/templates/comment-form.php';

    // Выводим форму переключателя БД
    $current_db = get_name_active_db();    // Передаём в форму имя текущей БД.
    include plugin_dir_path(__FILE__) . 'View/templates/db-switcher.php';

    // Выводим таблицу с комментариями, которая также служит формой для их удаления.
    $table_data = prepare_comments_table_data_for_view();    // Получаем данные для передачи в шаблон
    include plugin_dir_path(__FILE__) . 'View/templates/comments-table.php';

    // Если в transient есть статус комментария
    // показываем модальное окно с уведомлением.
    $comment_status = get_transient('comment_status');
    if ($comment_status) {
        include plugin_dir_path(__FILE__) . 'View/modal-window/notification.php';        
        delete_transient('comment_status');    // Удаляем из transient после использования
    }

    return ob_get_clean();
}
