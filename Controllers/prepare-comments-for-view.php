<?php

// Функция подготовки данных для отображения таблицы с комментариями и формы удаления
function prepare_comments_table_data_for_view(string $table_id = 'comments-table', int $per_page = 5) {
    
    $pdo = get_pdo_active_db();
    $table_name = get_table_name();
    $commentModel = new SimpleComments($pdo, $table_name);

    // Определяем общее количество строк в таблице в БД
    $total = $commentModel->getTotalCount();  

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
    $comments = $commentModel->getComments($per_page, $offset);

    return [
        'comments' => $comments,
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $current_page,
        'table_id' => $table_id
    ];
}
