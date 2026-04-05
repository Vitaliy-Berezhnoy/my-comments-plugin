<?php

//  Функция определяет обработчик для POST-запроса и, если нужно, выполняет редирект после обработки.
function route_post_actions() {

    //  Проверяем наличие POST запроса.
    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }

    //  Выбираем функцию обработчик POST запроса.
    $result = match (true) {
        isset($_POST['save_db_choice']) => handle_db_switch_request(),  //  запрос от формы переключения БД
        isset($_POST['save_comment']) => process_and_save_comment(),  //  запрос от формы ввода комментария
        isset($_POST['delete_selected_comments']) => process_comment_deletion_request(), // формы удаления комментариев
        isset($_POST['confirm_deletion']) => handle_comment_deletion_confirmation(),  //от формы подтверждения удаления
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
            $redirect_url .= '#' . $anchor;
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
function process_and_save_comment() {

    // Проверяем nonce
    if (!isset($_POST['add_comment_nonce']) ||
        !wp_verify_nonce($_POST['add_comment_nonce'], 'add_comment_action')) {

        return false;
    }

    // Санизируем данные
    $name = sanitize_text_field($_POST['comment_name']);
    $comment = sanitize_textarea_field($_POST['comment_text']);

    //  Проверяем обязательные поля
    if (empty($name) || empty($comment)) {
        //  Если есть не заполненное поле — сохраняем сообщение в transient.
        set_transient(
            'comment_status',
            [
                'type' => 'warning',
                'message' => 'Заполните все поля!'
            ],
            180  // время жизни в секундах
        );
        return false;  // false - запретит Редирект
    }

    // Сохраняем комментарий в одну из БД PostgreSQL или MySQL
    $table_name = get_table_name();
    $pdo = get_pdo_active_db();

    $sql = "INSERT INTO $table_name (name, comment) VALUES (:name, :comment);";


    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->execute();
        $comment_status = [
            'type' => 'success',
            'message' => 'Комментарий добавлен!'
        ];
    } catch(PDOException $e) {
        error_log(message: "Error writing a comment to the database: " . $e->getMessage());
        $comment_status = [
            'type' => 'error',
            'message' => 'Ошибка при записи комментария в БД!'
        ];
    }
    // Закрываем соеденение
    $pdo = null;
    $stmt = null;

    // Сохраняем сообщение об успехе/ошибке в transient
    set_transient('comment_status', $comment_status, 180);  // время жизни 180 секунд

    return true;    // true - разрешит Редирект
}


//  Функция для обработки POST‑запроса от формы удаления комментариев
function process_comment_deletion_request() {

    //  Проверяем nonce
    if (!isset($_POST['delete_comments_nonce']) ||
        !wp_verify_nonce($_POST['delete_comments_nonce'], 'delete_comments_action')) {
        return false;
    }

    //  Получаем список комментариев помеченных на удаление
    $comment_ids = $_POST['comment_ids'] ?? [];
    $comment_ids = array_map('intval', $comment_ids);

    //  Сохраняем в transient на три минуты
    set_transient('comment_ids', $comment_ids, 180);

    return true;    // true - разрешит Редирект
}


//  Функция для обработки POST‑запроса от формы подтверждения удаления
function handle_comment_deletion_confirmation() {

    //  Проверяем что это nonce от формы подтверждения удаления
    if (!isset($_POST['confirmation_of_deletion_nonce']) ||
        !wp_verify_nonce($_POST['confirmation_of_deletion_nonce'], 'confirmation_of_deletion_action')) {

        return false;
    }

    // Извлекаем ID
    $selected_ids = $_POST['selected_ids'] ?? [];

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

    $pdo = get_pdo_active_db();

    $table_name = get_table_name();
    $placeholders = str_repeat('?, ', count($selected_ids) - 1) . '?';
    $sql = "DELETE FROM $table_name WHERE id IN ($placeholders)";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($selected_ids);
        $deleted_count = $stmt->rowCount();
        $comment_status = [
            'type' => 'success',
            'message' => "Удалено комментариев: {$deleted_count}"
        ];
    } catch(PDOException $e) {
        error_log("Error when deleting a comment from the database: " . $e->getMessage());
        $comment_status = [
            'type' => 'error',
            'message' => "Ошибка при удалении комментариев"
        ];
    }
    // Закрываем соеденение с БД
    $stmt = null;
    $pdo = null;

    // Сохраняем сообщение об успехе/ошибке в transient
    set_transient('comment_status', $comment_status, 180);  // время жизни 180 секунд

    return true;   // true - разрешит Редирект
}