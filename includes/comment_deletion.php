<?php

//  Функция для обработки POST‑запроса от формы удаления комментариев
function process_comment_deletion_request() {

    //  Проверяем, что это POST запрос из формы удаления
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_selected_comments'])) {
        return false;
    }
    //  Проверяем nonce
    if (!isset($_POST['delete_comments_nonce']) || !wp_verify_nonce($_POST['delete_comments_nonce'], 'delete_comments_action')) {
        return false;
    }

    //  Получаем список комментариев помеченных на удаление
    $comment_ids = $_POST['comment_ids'] ?? [];
    $comment_ids = array_map('intval', $comment_ids);

    //  Сохраняем в transient на три минуты
    set_transient('comment_ids', $comment_ids, 180);

    // Редирект ДО начала вывода контента
    wp_safe_redirect($_SERVER['REQUEST_URI']);
    exit;

}

//  Функция для вывода формы подтверждения удаления комментариев
function render_comment_deletion_confirmation_form(array $comment_ids) {

    //  $comment_ids - id комментариев помеченных на удаление
    
    $pdo = get_pdo_active_db();

    //  Получаем комментарии помеченные на удпление в виде объектов
    $table_name = get_table_name();
    $placeholders = str_repeat('?, ', count($comment_ids) - 1) . '?';
    $sql = "SELECT * FROM $table_name WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($comment_ids);
    //  Передаем комментарии в виде объектов
    //  в форму подтверждения удаления.
    $comments_to_delete = $stmt->fetchAll(PDO::FETCH_OBJ);

    //  Выводим форму подтверждения удаления
    include dirname(plugin_dir_path(__FILE__)) . '/templates/confirm-delete.php';

    return true;
}

//  Функция для обработки POST‑запроса от формы подтверждения удаления
function handle_comment_deletion_confirmation() {

    //  Проверяем, что это POST запрос и нажата кнопка "Подтвердить удаление"
    if (!isset($_SERVER['REQUEST_METHOD']) || !isset($_POST['confirm_deletion_submit'])) {
        return false;
    }

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
        wp_safe_redirect($_SERVER['REQUEST_URI']);
        exit;
    }

    //  Санизируем ID
    $selected_ids = array_map('intval', $selected_ids);    // преабразует в числа
    $selected_ids = array_filter($selected_ids);    // удалит пустые элементы

    if (empty($selected_ids)) {
        set_transient(
            'comment_status',
            [
                'type' => 'error',
                'message' => 'Некорректные ID комментариев'
            ],
            180
        );
        wp_safe_redirect($_SERVER['REQUEST_URI']);
        exit;
    }

    // Удаляем комментарии

    $pdo = get_pdo_active_db();

    $table_name = get_table_name();
    $placeholders = str_repeat('?, ', count($selected_ids) - 1) . '?';
    $sql = "DELETE FROM $table_name WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute($selected_ids);
        $deleted_count = $stmt->rowCount();
        $comment_status = [
            'type' => 'success',
            'message' => "Удалено комментариев: {$deleted_count}"
        ];
        $stmt = null;
        $pdo = null;
    } catch(PDOException $e) {
        error_log("Error when deleting a comment from the database: {$e}");
        $comment_status = [
            'type' => 'error',
            'message' => "Ошибка при удалении комментариев"
        ];
        $stmt = null;
        $pdo = null;
    }

    // Временно сохраняем сообщение об успехе/ошибке
    set_transient('comment_status', $comment_status, 180);  // время жизни 180 секунд

    // Редирект ДО начала вывода контента
    wp_safe_redirect($_SERVER['REQUEST_URI']);
    exit;
}