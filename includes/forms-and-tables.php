<?php

// Функция для отображения таблицы с комментариями и формы удаления
function display_comments_table($table_id = 'comments-table', $per_page = 5) {
    
    // Определяем общее количество строк в таблице в БД
    $pdo = get_pdo_active_db();
    $table_name = get_table_name();

    $sql = "SELECT COUNT(*) AS total FROM $table_name";

    try {
        $stmt = $pdo->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $row ? (int)$row['total'] : 0;
        $row = null;
        $stmt = null;
    } catch(PDOException $e) {
        error_log("Error reading from the table {$table_name}" . $e->getMessage());
        set_transient('error_message', "Ошибка чтения из таблицы с комментариями в выбранной БД.", 180);
        $row = null;
        $stmt = null;
        $pdo = null;
        return false;
    }    

    //  Вычисляем количество страниц
    $pages_count = ceil($total / $per_page);

    // Определяем текущую страницу, учитывая оба формата URL
    if (get_query_var('paged')) {
        $current_page = get_query_var('paged');
    } elseif (get_query_var('page')) {
        $current_page = get_query_var('page');
    } else {
        $current_page = 1;
    }
    $current_page = max(1, intval($current_page));

    //  При переключении базы данных
    //  номер текущей страницы может оказаться больше общего числа страниц в подключенной базе.
    if ($current_page > $pages_count) {
        $current_page = 1;
    }

    // Определяем смещение для SQL-запроса
    $offset = ($current_page - 1) * $per_page;

    // Получаем комментарии с лимитом
    $sql = "SELECT * FROM $table_name
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset";
    
    try {
        $stmy = $pdo->prepare($sql);
        $stmy->bindParam(':limit', $per_page, PDO::PARAM_INT);
        $stmy->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmy->execute();
        //  Готовим комментарии для передачи в шаблон в виде объектов
        $comments = $stmy->fetchAll(PDO::FETCH_OBJ);
    } catch(PDOException $e) {
        error_log("Error reading from the table {$table_name}" . $e->getMessage());
        set_transient('error_message', "Ошибка чтения из таблицы с комментариями в выбранной БД.", 180);
        return false;      
    }

    // Передаем данные для пагинации в шаблон
    $pagination_data = [
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $current_page,
        'table_id' => $table_id
    ];

    include dirname(plugin_dir_path(__FILE__)) . '/templates/comments-table.php';

    //  Закрываем соеденение с БД
    $stmt = null;
    $pdo = null;

    return true;
}

//  Функция для вывода формы подтверждения удаления комментариев
function render_comment_deletion_confirmation_form(array $comment_ids) {

    //  $comment_ids - id комментариев помеченных на удаление
    
    $pdo = get_pdo_active_db();

    //  Получаем комментарии, помеченные на удаление, в виде объектов
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

    //  Закрываем соеденение с БД
    $stmt = null;
    $pdo = null;

    return true;
}