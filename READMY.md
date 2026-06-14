# Simple Comments Plugin

**Версия:** 3.0.0  
**Автор:** Vitaliy Berezhnoy  

---
> ⚠️ Проект выполнен в рамках тестового задания и предназначен для демонстрации владения различными профессиональными инструментами.

---

## 📌 О проекте

Simple Comments — это кастомная система комментариев для WordPress, которая не использует стандартные классы *WP_Comment* и таблицы *wp_comments*. Архитектура плагина построена на базе **MVC**, для хранения данных реализована поддержка двух СУБД — **MySQL** и **PostgreSQL** — с возможностью переключения между ними на лету, а также современными подходами безопасности.

## Функционал:
  - Создание в БД необходимых таблиц при активации плагина.
  - **CRUD** для управления комментариями.
  - Сохранение вводимого текста и отметок на удаление при перезагрузке страницы.   
  - Пагинация комментариев.
  - **Поддержку двух СУБД** — MySQL и PostgreSQL — с динамическим переключением через cookie
  - **Асинхронное взаимодействие** с модальными окнами без отправки форм (валидация на клиенте).
  - **Исключение трансграничной передачи** данных на стороне клиента и контроль на стороне сервера.
  - **Безопасность на нескольких уровнях**: CSP, nonce-проверки, санитизация, подготовленные запросы PDO.
 - **Логирование подозрительной активности** (внешние HTTP-запросы, ошибки БД, CSP-отчёты).
  

---



## 🛠️ Демонстрируемые технологии и подходы

### 🧱 Архитектура и паттерны

| Паттерн / подход | Реализация |
|------------------|-------------|
| **MVC** | Models (PDO, SimpleComments), Controllers (маршрутизация, подготовка данных), View (шаблоны + модальные окна) |
| **Dependency Injection** | `SimpleComments` принимает `PDO` и `table_name` через конструктор |
| **DRY** | Общий `TABLE_NAME` через константу, единая функция `get_pdo_active_db()` |
| **Fail-first с graceful degradation** | Ошибки БД логируются, пользователь получает transient + модалку |

### 🗄️ Работа с базами данных

| Технология | Детали |
|------------|--------|
| **PDO** | Единый интерфейс для MySQL и PostgreSQL |
| **Настоящие подготовленные запросы** | `ATTR_EMULATE_PREPARES => false` |
| **Абстракция подключения** | `get_pdo_active_db()` — фабрика по активной БД |
| **Кросс-СУБД совместимость** | Одинаковая схема таблицы (`id`, `name`, `comment`, `created_at`) |
| **WordPress константы** | MySQL — `DB_NAME`, `DB_USER`; PostgreSQL — `PG_DB_NAME`, `PG_DB_USER`, `PG_DB_PORT` |
| **Активационный хук** | `register_activation_hook` — создание таблиц при активации |

### 🔐 Безопасность

| Мера | Где реализовано |
|------|----------------|
| **Content-Security-Policy** | `add_csp_header()` — отправка заголовка, `add_csp_reports_api_endpoint()` — сбор отчётов |
| **Nonce-проверки** | Три формы: `add_comment_action`, `delete_comments_action`, `save_db_choice_action` |
| **Санитизация** | `sanitize_text_field()`, `sanitize_textarea_field()`, `sanitize_html_class()`, `intval()` для ID |
| **PDO-защита от инъекций** | Все запросы через `prepare()` + `bindParam()` / `execute($array)` |
| **Логирование** | `error_log()` для PDOException, `log_external_http_request()` для внешних запросов |
| **Транзитные сообщения** | `set_transient('comment_status', ...)` — без GET-параметров |

### 🎨 Фронтенд

| Компонент | Технологии |
|-----------|-------------|
| **CSS-фреймворк** | Bootstrap 5 (локальная копия, не CDN — CSP-совместимо) |
| **Кастомные JS** | 3 скрипта: форма комментария, таблица, переключатель БД |
| **Состояние кнопок** | Активация/деактивация кнопок |
| **Якоря в URL** | Редирект с `#form_comment` / `#comments-table` — навигация без плагинов |

### 🔌 WordPress API

| API | Использование |
|-----|----------------|
| **Shortcode** | `[show_comments]` — точка входа всего плагина |
| **REST API** | Эндпоинт для CSP-отчётов |
| **Хуки** | `init`, `send_headers`, `wp_enqueue_scripts` (с приоритетом 11 для переопределения темы) |
| **Transients** | Хранение статуса комментария (success/warning/error) |
| **Cookies** | `current_db` — выбор БД на 3 дня |
| **$wpdb->prefix** | В функции `get_table_name()` — мультисайт-совместимость |

### 🧪 Отладка и мониторинг

| Инструмент | Что отслеживает |
|------------|----------------|
| **http_api_debug** | Все внешние запросы WordPress (контроль утечек данных) |
| **error_log()** | Ошибки PDO при подключении, чтении, записи, удалении |
| **CSP report-uri** | Нарушения политики безопасности (инжекты, inline-стили) |

---

## ⚠️ Сознательные ограничения (важно для понимания)

Плагин был создан как тестовый проект в соответствии с техническим заданием, а не для промышленного применения. Поэтому здесь нет:
  - Авторизации пользователей
  - Редактирования комментариев   
  - Вложенных ответов (threaded comments)
  - Админ-интерфейса для настройки подключений к PostgreSQL
  - i18n (все сообщения на русском, без __())
  - AJAX (вся логика на POST + редиректах)

---
## 📁 Структура плагина
```text
/*
*my-comments-plugin/
*├── assets/
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
```
---

## 🔥 Технические детали, достойные упоминания

### 1. Маршрутизация POST-запросов через `match (true)`
```php
match (true) {
    isset($_POST['save_db_choice']) => handle_db_switch_request(),
    isset($_POST['save_comment']) => process_and_save_comment(...),
    isset($_POST['delete_selected_comments']) => delete_selected_comments(...),
    default => false,
};
```
— Чисто, читаемо, без каскада if-elseif.

### 2. Подключение Bootstrap с повышенным приоритетом
```php
add_action('wp_enqueue_scripts', 'enqueue_bootstrap', 11);
```
Тема hello-biz (приоритет 10) не переопределяет стили кнопок.

### 3. Пагинация с поддержкой двух форматов URL
```php
if (get_query_var('paged')) $current_page = get_query_var('paged');
elseif (get_query_var('page')) $current_page = get_query_var('page');
```
Совместимость с разными настройками ЧПУ.

### 4. Удаление комментариев с динамическими плейсхолдерами
```php
$placeholders = str_repeat('?, ', count($selected_ids) - 1) . '?';
$sql = "DELETE FROM ... WHERE id IN ($placeholders)";
$stmt->execute($selected_ids);
```
Безопасно для любого количества ID.

### 5. Редирект с сохранением якоря
```php
$redirect_url = strtok($_SERVER['REQUEST_URI'], '#');
$anchor = sanitize_html_class($_POST['anchor'] ?? '');
if ($anchor) $redirect_url .= "#{$anchor}";
wp_safe_redirect($redirect_url);
```
Пользователь после отправки формы возвращается ровно к месту действия.



## ⚙️ Установка и активация

    Скопировать папку my-comments-plugin в /wp-content/plugins/

    Активировать плагин в админке WordPress

    Разместить шорткод [show_comments] на любой странице

    (Опционально) Добавить в wp-config.php константы для PostgreSQL:
    php

    define('PG_DB_NAME', 'your_postgres_db');
    define('PG_DB_USER', 'your_postgres_user');
    define('PG_DB_PASSWORD', 'your_postgres_password');
    define('PG_DB_HOST', 'localhost');
    define('PG_DB_PORT', '5432');


---


👤 Контакт

Автор: Vitaliy Berezhnoy
Плагин создан в рамках технического задания для демонстрации навыков WordPress-разработки.

