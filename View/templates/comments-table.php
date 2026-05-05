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
    updateButtonState();
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
    updateButtonState();
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

// Функция очистки всех выделений
function clearAllSelections() {
    if (confirm('Вы уверены, что хотите снять выделение со всех комментариев?')) {
        // Снимаем отметки со всех чекбоксов на текущей странице
        document.querySelectorAll('.comment-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Очищаем sessionStorage
        sessionStorage.removeItem(STORAGE_KEY);
        
        // Обновляем счётчик и состояние кнопки удаления
        updateSelectionCount();
        updateButtonState();
    } else {
        // Если пользователь нажал «Отмена» — сбрасываем фокус с кнопки
        document.getElementById('clear-selected-btn').blur();
    }
}

// Функция обновления состояния кнопок удаления и очистки
function updateButtonState() {
    const deleteBtn = document.getElementById('delete-selected-btn');
    const clearBtn = document.getElementById('clear-selected-btn');
    const savedIds = sessionStorage.getItem(STORAGE_KEY);
    const hasSelected = savedIds && JSON.parse(savedIds).length > 0;
    deleteBtn.disabled = !hasSelected;
    clearBtn.disabled = !hasSelected;
}

// Функция для формирования и отправки POST запроса для удаления комментариев
function submitDeleteForm() {
    // Деактивируем кнопку "Удалить выбранные"
    // const deleteBtn = document.getElementById('delete-selected-btn');
    // deleteBtn.disabled = true;
    // deleteBtn.textContent = 'Удаление...';


    // Получаем ID для удаления из sessionStorage
    const savedIds = sessionStorage.getItem(STORAGE_KEY);
    const idsToDelete = savedIds ? JSON.parse(savedIds) : [];

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

    // Активируем поле delete_selected_comments
    let deleteField = form.querySelector('input[name="delete_selected_comments"]');
    deleteField.value = '1';

    // Очищаем хранилище
    clearSelectedIds();

    // Отправляем форму
    form.submit();
}
    
    

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    restoreSelectedIds();

    // Отслеживаем изменения чекбоксов
    document.querySelectorAll('.comment-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', saveSelectedIds);
    });

    // Обработчик для кнопки очистки
    const clearBtn = document.getElementById('clear-selected-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearAllSelections);
    }

    // Перехват открытия модального окна
    const modal = document.getElementById('confirm-delete-modal'); 
    
    modal.addEventListener('show.bs.modal', function () {
        // Получаем количество ID выбранных комментариев из sessionStorage
        const savedIds = sessionStorage.getItem(STORAGE_KEY);
        const count = savedIds ? JSON.parse(savedIds).length : 0;
        // Обновляем счётчик в модальном окне
        document.getElementById('selected-count-modal').textContent = count;
    })

    // document.getElementById('cancel-delete-btn').addEventListener('click', function() {
    //     document.getElementById('delete-selected-btn').blur();
    //     document.body.focus();
    //     updateButtonState();
    // });


     // Перехват закрытия модального окна
    modal.addEventListener('hide.bs.modal', function() {
        // const triggerButton = event.relatedTarget;
        // if (triggerButton) {
        //     triggerButton.blur();
        // }
        location.reload();
    //    document.querySelector('clear-selected-btn').focus();
    //     // Если пользователь нажал «Отмена» — сбрасываем фокус с кнопки
    //     document.getElementById('delete-selected-btn').blur();
    //     document.body.focus();
    })

    // Обработчик для кнопки "Подтвердить удаление"
    const confirmBtn = document.getElementById('confirm-delete-btn');
    confirmBtn.addEventListener('click', function() {
        //location.reload();
        submitDeleteForm();
    })
});

</script>
<?php endif; ?>