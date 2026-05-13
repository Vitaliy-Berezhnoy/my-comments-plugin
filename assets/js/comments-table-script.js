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

// Функция для правильного склонения слова «комментарий»
function inflectWordComment(num) {
    if (num === 1) return 'комментарий';
    if (num >= 2 && num <= 4) return 'комментария';
    return 'комментариев';
}

// Обновление счётчика выбранных комментариев
function updateSelectionCount() {
    const savedIds = sessionStorage.getItem(STORAGE_KEY);
    const count = savedIds ? JSON.parse(savedIds).length : 0;
    const countElement = document.getElementById('selected-count');
    const wordComment = inflectWordComment(count);

    if (countElement) {
        countElement.innerHTML = `Выбрано для удаления: <strong>${count}</strong> ${wordComment}`;
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
        const countElement = document.getElementById('selected-count-modal');
        const wordComment = inflectWordComment(count);

        if (countElement) {
            countElement.innerHTML = `Выбрано для удаления: <strong>${count}</strong> ${wordComment}`;
        }
    })

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
        submitDeleteForm();  // Отправляем POST запрос для удаления комментариев
    })
});