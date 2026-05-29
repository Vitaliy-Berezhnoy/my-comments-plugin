/**
 * Ключ для хранения вводимого/редактируемого комментария в sessionStorage.
 * Используется для сохранения данных формы между перезагрузками страницы.
 */
const STORAGE_KEY_SAVED_COMMENT = 'saved_comment_data';

/**
 * Кэшируем элементы DOM для ускорения доступа к ним.
 */
const commentNameField = document.getElementById('comment_name');
const commentTextField = document.getElementById('comment_text');
const saveButton = document.getElementById('save-comment-btn');

/**
 * Восстанавливает данные комментария из sessionStorage при загрузке страницы.
 * Если данные найдены и успешно распарсены, заполняет поля формы.
 * В случае ошибки парсинга (битые данные) очищает storage.
 * Активирует кнопку сохранения после восстановления данных.
 */
function restoreCommentData() {
    const savedData = sessionStorage.getItem(STORAGE_KEY_SAVED_COMMENT);

    if (savedData) {
        try {
            const { name, text } = JSON.parse(savedData);
            // Заполняем поля. Если значения null/undefined, используем пустую строку.
            commentNameField.value = name ?? '';
            commentTextField.value = text ?? '';
        } catch(e) {
            console.error('Ошибка парсинга данных из sessionStorage:', e);
            // Чистим "битые" данные, чтобы не мешали при следующей загрузке
            sessionStorage.removeItem(STORAGE_KEY_SAVED_COMMENT);
        }
    }

    // После восстановления (или если данных нет) проверяем состояние кнопки
    activateSaveCommentButton();
}

/**
 * Сохраняет текущее состояние полей комментария в sessionStorage.
 * Вызывается при каждом изменении текста в полях ввода.
 */
function saveCommentData() {
    const data = {
        name: commentNameField.value,
        text: commentTextField.value
    };
    sessionStorage.setItem(STORAGE_KEY_SAVED_COMMENT, JSON.stringify(data));
}

/**
 * Активирует или деактивирует кнопку "Сохранить комментарий".
 * Кнопка активна, если хотя бы одно из полей (имя или текст) содержит непустые символы.
 */
function activateSaveCommentButton() {
    const hasName = commentNameField.value.trim().length > 0;
    const hasText = commentTextField.value.trim().length > 0;

    saveButton.disabled = !(hasName || hasText);
}

/**
 * Обработчик события нажатия на кнопку "Сохранить комментарий".
 * Проверяет заполненность полей. Если оба поля заполнены, отправляет форму и очищает storage.
 * Если поля пусты — показывает модальное окно с предупреждением.
 */
function onSaveComment() {
    const hasName = commentNameField.value.trim().length > 0;
    const hasText = commentTextField.value.trim().length > 0;

    if (hasName && hasText) {
        // Находим форму и устанавливаем скрытый флаг для бэкенда
        const form = document.getElementById('form_comment');
        form.querySelector('input[name="save_comment"]').value = '1';
        // Очищаем временное хранилище
        sessionStorage.removeItem(STORAGE_KEY_SAVED_COMMENT);
        // Отправляем форму
        form.submit();
    } else {
        // Показываем модальное окно с предупреждением, если поля не заполнены
        const fieldsNotFilledModal = document.getElementById('fields-not-filled-modal');
        const modalWindow = new bootstrap.Modal(fieldsNotFilledModal);
        modalWindow.show();
    }
}

/**
 * Обработчик нажатия кнопки "Очистить форму" в модальном окне.
 * Сбрасывает значения полей ввода и очищает данные из sessionStorage.
 */
function clearCommentForm() {
    commentNameField.value = '';
    commentTextField.value = '';
    sessionStorage.removeItem(STORAGE_KEY_SAVED_COMMENT);

    // Деактивируем кнопку после очистки полей
    activateSaveCommentButton();
}

/**
 * Единый обработчик для событий ввода в полях формы.
 * Вызывает функции сохранения данных и обновления состояния кнопки.
 */
function handleInputChange() {
    saveCommentData();
    activateSaveCommentButton();
}

/**
 * Инициализация скрипта при полной загрузке DOM.
 */
document.addEventListener('DOMContentLoaded', function() {
    // 1. Восстанавливаем данные из sessionStorage (если есть)
    restoreCommentData();

    // 2. Устанавливаем начальное состояние кнопки
    activateSaveCommentButton();

    // 3. Навешиваем обработчики на поля ввода
    commentNameField.addEventListener('input', handleInputChange);
    commentTextField.addEventListener('input', handleInputChange);

    // 4. Навешиваем обработчик на кнопку "Сохранить комментарий"
    saveButton.addEventListener('click', onSaveComment);

    // 5. Навешиваем обработчик на кнопку "Очистить форму" в модальном окне
    document.getElementById('cancel-comment-save-btn').addEventListener('click', clearCommentForm);
});