/**
 * Ключ для хранения списка ID (комментариев, выбранных для удаления) в sessionStorage.
 * Используется для сохранения состояния между перезагрузками страницы.
 */
const STORAGE_KEY = 'my-comments-selected-ids';

/**
 * Кэшируем элементы DOM для ускорения доступа.
 */
const clearBtn = document.getElementById('clear-selected-btn'); // Кнопка "Снять все отметки"
const deleteBtn = document.getElementById('delete-selected-btn'); // Кнопка "Удалить выбранные"

/**
 * Сохраняет текущие ID выбранных комментариев в sessionStorage.
 * Объединяет уже сохраненные ID с новыми, а также удаляет ID, если пользователь снял с них отметку.
 */
function saveSelectedIds() {
    // Получаем текущие сохранённые ID из хранилища
    const savedIds = sessionStorage.getItem(STORAGE_KEY);
    const existingIds = savedIds ? JSON.parse(savedIds) : [];

    // Получаем массив ID чекбоксов, которые отмечены на текущей странице
    const currentCheckedIds = Array.from(
        document.querySelectorAll('.comment-checkbox:checked')
    ).map(cb => cb.value);

    // Объединяем существующие и текущие ID в один Set (убирает дубликаты)
    const allCheckedIdsSet = new Set([...existingIds, ...currentCheckedIds]);

    // Получаем множество ID чекбоксов, которые НЕ отмечены на текущей странице
    const currentUncheckedIdsSet = new Set(
        [...document.querySelectorAll('.comment-checkbox:not(:checked)')]
            .map(cb => cb.value)
    );

    // Удаляем из общего списка те ID, которые были сняты пользователем на этой странице
    const updatedIdsSet = allCheckedIdsSet.difference(currentUncheckedIdsSet);

    // Сохраняем обновленный список обратно в sessionStorage
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(updatedIdsSet)));

    updateUI(); // Обновляем интерфейс после сохранения
}

/**
 * Восстанавливает отметки на чекбоксах при загрузке страницы на основе данных из sessionStorage.
 */
function restoreSelectedIds() {
    const savedIds = sessionStorage.getItem(STORAGE_KEY);
    if (savedIds) {
        const ids = JSON.parse(savedIds);
        ids.forEach(id => {
            const checkbox = document.querySelector(`.comment-checkbox[value="${id}"]`);
            if (checkbox) checkbox.checked = true;
        });
    }
    updateUI(); // Обновляем интерфейс после восстановления
}

/**
 * Склоняет слово «комментарий» в зависимости от количества.
 * @param {number} num - Количество комментариев.
 * @returns {string} - Верное окончание слова.
 */
function inflectWordComment(num) {
    if (num === 1) return 'комментарий';
    if (num >= 2 && num <= 4) return 'комментария';
    return 'комментариев';
}

/**
 * Обновляет текстовый счетчик выбранных комментариев в интерфейсе.
 */
function updateSelectionCount() {
    const savedIds = sessionStorage.getItem(STORAGE_KEY);
    const count = savedIds ? JSON.parse(savedIds).length : 0;
    const countElement = document.getElementById('selected-count');
    const wordComment = inflectWordComment(count);

    if (countElement) {
        countElement.innerHTML = `Выбрано для удаления: <strong>${count}</strong> ${wordComment}`;
    }
}

/**
 * Обновляет состояние кнопок "Удалить" и "Снять все отметки" (активна/неактивна).
 */
function updateButtonState() {
    const savedIds = sessionStorage.getItem(STORAGE_KEY);
    const hasSelected = savedIds && JSON.parse(savedIds).length > 0;
    deleteBtn.disabled = !hasSelected;
    clearBtn.disabled = !hasSelected;
}

/**
 * Обновляет весь UI: счетчик и состояние кнопок.
 */
function updateUI() {
    updateSelectionCount();
    updateButtonState();
}

/**
 * Очищает данные о выбранных комментариях из sessionStorage.
 * Вызывается после успешной отправки формы удаления.
 */
function clearSelectedIds() {
    sessionStorage.removeItem(STORAGE_KEY);
}

/**
 * Обработчик кнопки "Снять все отметки".
 * Снимает отметки со всех чекбоксов и очищает хранилище после подтверждения пользователя.
 */
function clearAllSelections() {
    if (confirm('Вы уверены, что хотите снять все отметки?')) {
        // Снимаем отметки со всех чекбоксов на текущей странице
        document.querySelectorAll('.comment-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        // Очищаем хранилище
        sessionStorage.removeItem(STORAGE_KEY);
        // Обновляем интерфейс
        updateUI();
    } else {
        // Если пользователь нажал «Отмена» — сбрасываем фокус с кнопки
        clearBtn.blur();
    }
}

/**
 * Формирует скрытые поля формы с ID комментариев для удаления и отправляет POST-запрос.
 */
function submitDeleteForm() {
    // Получаем массив ID из хранилища
    const savedIds = sessionStorage.getItem(STORAGE_KEY);
    const idsToDelete = savedIds ? JSON.parse(savedIds) : [];

    const form = document.getElementById('delete-comments-form');

    // Удаляем старые поля comment_ids[], если они остались от предыдущей отправки
    form.querySelectorAll('input[name="comment_ids[]"]').forEach(input => input.remove());

    // Добавляем новые скрытые поля для каждого ID, который нужно удалить
    idsToDelete.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'comment_ids[]';
        input.value = id;
        form.appendChild(input);
    });

    // Активируем поле, сигнализирующее серверу о массовом удалении
    form.querySelector('input[name="delete_selected_comments"]').value = '1';

    // Очищаем хранилище перед отправкой (чтобы не отправить повторно)
    clearSelectedIds();

    // Отправляем форму на сервер
    form.submit();
}


// Инициализация скрипта при полной загрузке DOM
document.addEventListener('DOMContentLoaded', function() {

    // 1. Восстанавливаем состояние чекбоксов из кеша при загрузке страницы
    restoreSelectedIds();

    // 2. Навешиваем обработчик изменения на все чекбоксы комментариев
    document.querySelectorAll('.comment-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', saveSelectedIds);
    });

    // 3. Навешиваем обработчик на кнопку "Снять все отметки"
    if (clearBtn) {
        clearBtn.addEventListener('click', clearAllSelections);
    }

     /**
      * Обработчик события открытия модального окна подтверждения удаления.
      * Обновляет текст внутри модального окна с актуальным количеством выбранных элементов.
      */
     const modal = document.getElementById('confirm-delete-modal');
     modal.addEventListener('show.bs.modal', function () {
         const savedIds = sessionStorage.getItem(STORAGE_KEY);
         const count = savedIds ? JSON.parse(savedIds).length : 0;
         const countElement = document.getElementById('selected-count-modal');
         const wordComment = inflectWordComment(count);
         if (countElement) {
             countElement.innerHTML = `Выбрано для удаления: <strong>${count}</strong> ${wordComment}`;
         }
     });

     /**
      * Обработчик события закрытия модального окна.
      * Перезагружает страницу, чтобы сбросить состояние интерфейса (если удаление отменено).
      */
     modal.addEventListener('hide.bs.modal', function() {
        location.reload();
     });

     // 4. Навешиваем обработчик на кнопку "Подтвердить удаление" в модальном окне
     const confirmBtn = document.getElementById('confirm-delete-btn');
     confirmBtn.addEventListener('click', function() {
         submitDeleteForm(); // Отправляем данные на сервер для удаления комментариев
     });
});