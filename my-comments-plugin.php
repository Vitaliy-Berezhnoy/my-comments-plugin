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
*|   |   ├── comment-form-script.js      # Скрипт формы ввода нового комментария
*|   |   ├── comments-table-script.js    # Скрипт формы вывода таблицы с комментариями
*|   |   └── db-switcher-script.js       # Скрипт формы вабора активной БД
*|   |
*├── Models/
*│   ├── creating-tables-in-db.php       # Создаёт таблицы для хранения комментариев в MySQL и PostgreSQL.
*│   ├── pdo-connect.php                 # Создаёт подключения к БД MySQL и PostgreSQL с использованием PDO
*│   ├── SimpleComments.php              # Модель для работы с комментариями
*│   └── table-name.php                  # Определяет имя таблицы для хранения комментариев
*|
*├── Controllers/
*│   ├── content-security-policy.php     # Функции для отправки заголовка CSP и обработки отчётов CSP
*│   ├── enqueue-bootstrap.php           # Подключение bootstrap 5
*│   ├── enqueue-my-scripts.php          # Подключение скриптов актиации кнопок и соханения ID в sessionStorage и т.д. 
*│   ├── log-external-http-request.php   # Логирует внешние HTTP-запросы WordPress
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
require_once plugin_dir_path(__FILE__) . 'Controllers/content-security-policy.php';
require_once plugin_dir_path(__FILE__) . 'Controllers/enqueue-bootstrap.php';
require_once plugin_dir_path(__FILE__) . 'Controllers/enqueue-my-scripts.php';
//require_once plugin_dir_path(__FILE__) . 'Controllers/localize-avatar-url.php';
require_once plugin_dir_path(__FILE__) . 'Controllers/log-external-http-request.php';
require_once plugin_dir_path(__FILE__) . 'Controllers/name-active-db.php';
require_once plugin_dir_path(__FILE__) . 'Controllers/route-post-actions.php';
require_once plugin_dir_path(__FILE__) . 'Controllers/prepare-comments-for-view.php';



/**
 * При активации плагина проверяем наличие таблиц
 * для комментариев в базах MySQL и PostgreSQL.
 * При отсутствии создаём, при наличии оставляем.
 */
register_activation_hook( __FILE__, 'create_tables_on_activation' );

// Регистрируем маршрут для приёма отчётов CSP (Content Security Policy)
add_action('rest_api_init', 'add_csp_reports_api_endpoint');

// Хук для отправки заголовка Content-Security-Policy
add_action('send_headers', 'add_csp_header');

// Фильтр для замены URL аватара на локальный файл
//add_filter('get_avatar_url', 'localizeAvatarUrl', 999, 3);

//  Обрабатываем POST запросы
add_action('init', 'route_post_actions');

// Подключаем bootstrap-local после стилей темы hello-biz.
// Если ипользовать приоритет 10 (по умолчанию) тема hello-biz переопределит стили кнопок.
add_action('wp_enqueue_scripts', 'enqueue_bootstrap', 11);

// Подключаем скрипты для обработки страницы на стороне клиента. (в браузере)
add_action('wp_enqueue_scripts', 'my_comments_enqueue_scripts');

// Для контроля за трансграничной передачей, логируем внешние HTTP-запросы WordPress
add_action( 'http_api_debug', 'log_external_http_request', 10, 5 );

// Регестрируем shortcode для вставки форм и шаблонов плагина на любую страницу на WordPress 
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
