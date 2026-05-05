<?php

//  Функция определяет обработчик для POST-запроса и, если нужно, выполняет редирект после обработки.
function route_post_actions() {

    //  Проверяем наличие POST запроса.
    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') return;

    // Инжектим зависимости
    $pdo = get_pdo_active_db();
    $table_name = get_table_name();
    $commentModel = new SimpleComments($pdo, $table_name);

    //  Выбираем функцию обработчик POST запроса.
    $result = match (true) {
        isset($_POST['save_db_choice']) => handle_db_switch_request(),  //  запрос от формы переключения БД
        isset($_POST['save_comment']) => process_and_save_comment($commentModel),  //  запрос от формы ввода комментария
        isset($_POST['delete_selected_comments']) => delete_selected_comments($commentModel), // запрос от формы удаления комментариев
        default => false,        
    };
    
    if ($result) {
        // Формируем URL для редиректа
        $redirect_url = $_SERVER['REQUEST_URI'];

        // Удаляем существующий якорь, если он есть
        $redirect_url = strtok($redirect_url, '#');

        // Добавляем якорь из скрытого поля, если он передан
        $anchor = sanitize_html_class($_POST['anchor'] ?? '');
        if ($anchor) {
            $redirect_url .= "#{$anchor}";
        }
        wp_safe_redirect($redirect_url);
        exit;
    }
    //  Редирект не нужен
    return false;
}


// Функция для обработки POST запроса от формы переключения БД
function handle_db_switch_request() {

    // Проверяем nonce
    if (!isset($_POST['save_db_choice_nonce']) ||
        !wp_verify_nonce($_POST['save_db_choice_nonce'], 'save_db_choice_action')) {

        return false;
    }

    $name_selected_db = sanitize_text_field($_POST['db_choice']);

    // Сохраняем в cookie (на 3 дня)
    setcookie(
        'current_db',
        $name_selected_db,
        time() + (86400 * 3),
        '/',
        '',
        false,
        true
    );
    return true;  // true - разрешит Редирект
}


// Функция для обработки POST запроса от формы ввода комментария
function process_and_save_comment(SimpleComments $commentModel):bool {

    // Проверяем nonce
    if (!isset($_POST['add_comment_nonce']) ||
        !wp_verify_nonce($_POST['add_comment_nonce'], 'add_comment_action')) {

        return false;
    }

    // Санизируем данные
    $name = sanitize_text_field($_POST['comment_name']);
    $comment = sanitize_textarea_field($_POST['comment_text']);

    //  Проверяем обязательные поля
    //  Если после очистки появились пустые поля — сохраняем сообщение в transient.
    if (empty($name) || empty($comment)) {
        set_transient(
            'comment_status',
            [
                'type' => 'warning',
                'message' => 'При проверке данных найдены последовательности,<br>блокируемые системой безопасности.<br>Комменарий удалён!'
            ],
            180  // время жизни в секундах
        );
        return true;    // true - разрешит Редирект
    }

    // Сохраняем комментарий в одну из БД PostgreSQL или MySQL
    $commentModel->saveComment($name, $comment);

    return true;    // true - разрешит Редирект
}


//  Функция для обработки POST‑запроса от формы удаления комментариев
function delete_selected_comments(SimpleComments $commentModel): bool {

    //  Проверяем nonce
    if (!isset($_POST['delete_comments_nonce']) ||
        !wp_verify_nonce($_POST['delete_comments_nonce'], 'delete_comments_action')) {
        return false;
    }

    //  Получаем список комментариев помеченных на удаление
    $selected_ids = $_POST['comment_ids'] ?? [];

    if (empty($selected_ids)) {
        set_transient(
            'comment_status',
            [
                'type' => 'warning',
                'message' => 'Не выбраны комментарии для удаления'
            ],
            180  // время жизни в секундах
        );

        return true;    // true - разрешит Редирект
    }

    //  Санизируем ID
    $selected_ids = array_map('intval', $selected_ids);    // преабразует в числа
    $selected_ids = array_filter($selected_ids);    // удалит пустые элементы

    if (empty($selected_ids)) {
        set_transient(
            'comment_status',
            [
                'type' => 'error',
                'message' => 'Ошибка при удалении - Некорректные ID комментариев'
            ],
            180
        );

        return true;   // true - разрешит Редирект
    }

    // Удаляем комментарии
    $commentModel->deleteComments($selected_ids);

    // // Закрываем соеденение с БД
    // $stmt = null;
     $pdo = null;

    return true;   // true - разрешит Редирект
}
