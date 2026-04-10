<?php
/**
 * Шаблон для отображения таблицы с комментариями (с использованием Bootstrap)
 * @var array $comments List of comment objects
 * @var string $table_id Optional table ID
 * @var array $pagination_data Data for pagination
 */
?>

<?php if (empty($comments)): ?>
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
            <?php if (!empty($pagination_data) && $pagination_data['total'] > $pagination_data['per_page']): ?>
                <div class="text-muted small">
                    <?php printf(
                        esc_html__('Показано %d из %d комментариев', 'my-comments'),
                        count($comments),
                        $pagination_data['total']
                    ); ?>
                </div>
                <?php include plugin_dir_path(__FILE__) . 'pagination.php'; ?>
            <?php endif; ?>
        </div>

        <div class="table-responsive mt-2">
            <table id="<?php echo htmlspecialchars($table_id); ?>" class="table table-striped table-hover table-bordered">
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
                    <?php foreach ($comments as $comment): ?>
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

        <!-- Кнопка удаления -->
        <div>
            <button type="submit" name="delete_selected_comments" id="delete-selected-btn" class="mt-3 btn btn-danger btn-sm" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash me-1" viewBox="0 0 16 16">
                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                </svg>
                Удалить выбранные
            </button>
        </div>

        <!-- Пагинация снизу -->
        <?php if (!empty($pagination_data) && $pagination_data['total'] > $pagination_data['per_page']): ?>
            <div class="d-flex justify-content-center mt-4">
                <?php include plugin_dir_path(__FILE__) . 'pagination.php'; ?>
            </div>
        <?php endif; ?>
    </form>

    <!-- JavaScript для активации кнопки удаления -->
    <script>
// Ключ для хранения данных в sessionStorage
const STORAGE_KEY = 'my-comments-selected-ids';

// Функция сохранения ID в sessionStorage
function saveSelectedIds() {
    // Получаем текущие сохранённые ID
    const savedIds = sessionStorage.getItem(STORAGE_KEY);
    const existingIds = savedIds ? JSON.parse(savedIds) : [];

    // Получаем ID отмеченных на текущей странице
    const currentCheckedIds = Array.from(
        document.querySelectorAll('.comment-checkbox:checked')
    ).map(cb => cb.value);

    // Объединяем наборы ID (Set автоматически убирает дубликаты)
    const allCheckedIdsSet = new Set([...existingIds, ...currentCheckedIds]);

    // Получаем множество ID НЕотмеченных на текущей странице
    const currentUncheckedIdsSet = new Set(
        [...document.querySelectorAll('.comment-checkbox:not(:checked)')]
           .map(cb => cb.value)
    );

    // Удаляем ID комментариев, отметки на которых были сняты
    const updatedIdsSet = allCheckedIdsSet.difference(currentUncheckedIdsSet);

    // Сохраняем в sessionStorage обновлённый список
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(updatedIdsSet)));
    updateSelectionCount();
    updateDeleteButtonState();
}

// Восстановление отметок при загрузке страницы
function restoreSelectedIds() {
    const savedIds = sessionStorage.getItem(STORAGE_KEY);
    if (savedIds) {
        const ids = JSON.parse(savedIds);
        ids.forEach(id => {
            const checkbox = document.querySelector(`.comment-checkbox[value="${id}"]`);
            if (checkbox) checkbox.checked = true;
        });
    }
    updateSelectionCount();
    updateDeleteButtonState();

}

// Обновление счётчика выбранных комментариев
function updateSelectionCount() {
    const savedIds = sessionStorage.getItem(STORAGE_KEY);
    const count = savedIds ? JSON.parse(savedIds).length : 0;
    const countElement = document.getElementById('selected-count');
    if (countElement) {
        countElement.innerHTML = `Выбрано для удаления: <strong>${count}</strong> комментариев`;
    }
}

// Очистка данных после успешной отправки формы
function clearSelectedIds() {
    sessionStorage.removeItem(STORAGE_KEY);
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    restoreSelectedIds();

    // Отслеживаем изменения чекбоксов
    document.querySelectorAll('.comment-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', saveSelectedIds);
    });
});

// Обновление состояния кнопки удаления
function updateDeleteButtonState() {
    const deleteBtn = document.getElementById('delete-selected-btn');
    const savedIds = sessionStorage.getItem(STORAGE_KEY);
    const hasSelected = savedIds && JSON.parse(savedIds).length > 0;
    deleteBtn.disabled = !hasSelected;
}

// Отправка формы удаления
document.getElementById('delete-comments-form').addEventListener('submit', function(e) {

    // Деактивируем кнопку "Удалить выбранные"
    const deleteBtn = document.getElementById('delete-selected-btn');
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Удаление...';

    try {
        // Получаем ID для удаления из sessionStorage
        const savedIds = sessionStorage.getItem(STORAGE_KEY);
        const idsToDelete = savedIds ? JSON.parse(savedIds) : [];

        if (idsToDelete.length === 0) {
            alert('Выберите комментарии для удаления');
            e.preventDefault(); // Отменяем отправку, если нет выбранных комментариев
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Удалить выбранные';
            return;
        }

        const form = document.getElementById('delete-comments-form');

        // Удаляем старые поля comment_ids[], если они есть
        form.querySelectorAll('input[name="comment_ids\[\]"]').forEach(input => input.remove());

        // Добавляем новые поля comment_ids[] для каждого ID
        idsToDelete.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'comment_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        clearSelectedIds();

        // Активируем поле delete_selected_comments
        let deleteField = form.querySelector('input[name="delete_selected_comments"]');
        deleteField.value = '1';

    } catch (error) {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при подготовке данных для удаления. Попробуйте ещё раз.');
        e.preventDefault(); // Отменяем отправку при ошибке
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Удалить выбранные';
    }
});

    </script>
<?php endif; ?>