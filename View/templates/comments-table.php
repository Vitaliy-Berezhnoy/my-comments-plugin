<?php
/**
 * Шаблон для отображения таблицы с комментариями (с использованием Bootstrap)
 * @var array $comments List of comment objects
 * @var string $table_id Optional table ID
 * @var array $table_data Data for pagination
 */
?>

<?php if (empty($table_data['comments'])): ?>
    <div class="alert alert-info" role="alert">
        <?php esc_html_e('Комментариев пока нет.', 'my-comments'); ?>
    </div>
<?php else: ?>
    <!-- Основная форма выбора комментариев -->
    <form method="post" action="" id="delete-comments-form">
        <?php wp_nonce_field('delete_comments_action', 'delete_comments_nonce'); ?>

        <input type="hidden" name="delete_selected_comments" value="">
        
        <!-- Пагинация сверху -->
        <div class="d-flex justify-content-between align-items-center mb-3">            
            <?php if (!empty($table_data) && $table_data['total'] > $table_data['per_page']): ?>
                <div class="text-muted small">
                    <?php printf(
                        esc_html__('Показано %d из %d комментариев', 'my-comments'),
                        count($table_data['comments']),
                        $table_data['total']
                    ); ?>
                </div>
                <?php include plugin_dir_path(__FILE__) . 'pagination.php'; ?>
            <?php endif; ?>
        </div>

        <div class="table-responsive mt-2">
            <table id="<?php echo htmlspecialchars($table_data['table_id']); ?>" class="table table-striped table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 40px;">
                            <!-- Пустой заголовок для колонки с чекбоксами -->
                        </th>
                        <th scope="col">ID</th>
                        <th scope="col">Имя</th>
                        <th scope="col">Комментарий</th>
                        <th scope="col">Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($table_data['comments'] as $comment): ?>
                        <tr>
                            <td>
                                <input type="checkbox" 
                                       name="comment_ids[]" 
                                       value="<?php echo htmlspecialchars($comment->id); ?>" 
                                       class="form-check-input comment-checkbox"
                                       onchange="updateDeleteButton(this)">
                            </td>
                            <td class="fw-bold"><?php echo htmlspecialchars($comment->id); ?></td>
                            <td><?php echo htmlspecialchars($comment->name); ?></td>
                            <td>
                                <div class="text-wrap w-100">
                                    <?php echo htmlspecialchars($comment->comment); ?>
                                </div>
                            </td>
                            <td class="text-muted small"><?php echo htmlspecialchars($comment->created_at); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="text-muted small">
            <span id="selected-count">Выбрано для удаления: <strong>0</strong> комментариев</span>
        </div>


<div class="d-flex gap-2 mt-3">
    <!-- Кнопка удаления -->
    <button type="button"
            id="delete-selected-btn"
            class="btn btn-outline-danger"
            data-bs-toggle="modal"
            data-bs-target="#confirm-delete-modal"
            disabled>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash me-1" viewBox="0 0 16 16">
            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
            <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
        </svg>
        Удалить выбранные
    </button>
    
    <!-- Кнопка очистки -->
    <button type="button"
            id="clear-selected-btn"
            class="btn btn-outline-dark"
            disabled>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle me-1" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
        </svg>
        Очистить все выделения
    </button>
</div>

        <!-- Пагинация снизу -->
        <?php if (!empty($table_data) && $table_data['total'] > $table_data['per_page']): ?>
            <div class="d-flex justify-content-center mt-4">
                <?php include plugin_dir_path(__FILE__) . 'pagination.php'; ?>
            </div>
        <?php endif; ?>

        <!-- Подключение модального окна подтверждения -->
        <?php include plugin_dir_path(__DIR__) . 'modal-window/confirm-delete.php'; ?>
    </form>

    <!-- JavaScript для активации кнопки удаления -->

<?php endif; ?>