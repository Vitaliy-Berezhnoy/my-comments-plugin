// Ключ для хранения вводимого/редактируемого комментария в sessionStorage
const STORAGE_KEY_SAVED_COMMENT = 'saved_comment_data';

// Функция восстановления комментария из sessionStorage при загрузке страницы
function restoreCommentData() {
    const commentNameInput = document.getElementById('comment_name');
    const commentTextarea = document.getElementById('comment_text');
    const savedData = sessionStorage.getItem(STORAGE_KEY_SAVED_COMMENT);

    if (savedData) {
        try {
            const { name, text } = JSON.parse(savedData);
            if (name) commentNameInput.value = name;
            if (text) commentTextarea.value = text;
        } catch(e) {
            console.error('Ошибка парсинга sessionStorage:', e);
        }
    }

    // После восстановления активируем кнопку
    activateSaveCommentButton();
}

// Функция для сохранения коментария в sessionStorage при каждом изменении полей
function saveCommentData() {
    const commentNameInput = document.getElementById('comment_name');
    const commentTextarea = document.getElementById('comment_text');
        const data = {
            name: commentNameInput.value,
            text: commentTextarea.value
        };
        sessionStorage.setItem(STORAGE_KEY_SAVED_COMMENT, JSON.stringify(data));
}

// Функция для активации кнопки 'Сохранить комментарий'
function activateSaveCommentButton() {
    let isNameFilled = document.getElementById('comment_name').value.trim().length > 0;
    let isCommentFilled = document.getElementById('comment_text').value.trim().length > 0;

    document.getElementById('save-comment-btn').disabled = !(isNameFilled || isCommentFilled);
}

// Функция обработки при нажатии кнопки "Сохранить комментарий"
function onSaveComment() {
    let isNameFilled = document.getElementById('comment_name').value.trim().length > 0;
    let isCommentFilled = document.getElementById('comment_text').value.trim().length > 0;
    if (isNameFilled && isCommentFilled) {
        // Отправляем форму
        const form = document.getElementById('form_comment');
        form.querySelector('input[name="save_comment"]').value = '1';
        sessionStorage.removeItem(STORAGE_KEY_SAVED_COMMENT);
        form.submit();
    } else {
        // Выводим окно "Заполните все поля"
        const fieldsNotFilledModal = document.getElementById('fields-not-filled-modal');
        const modalWindow = new bootstrap.Modal(fieldsNotFilledModal);
        modalWindow.show();
    }
}

// Функция обработки при нажатии в модальном окне кнопки "Очистить форму"
function clearСommentForm() {
    document.getElementById('comment_name').value = '';
    document.getElementById('comment_text').value = '';
    sessionStorage.removeItem(STORAGE_KEY_SAVED_COMMENT);
    activateSaveCommentButton();
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {

    restoreCommentData()
    activateSaveCommentButton();
    document.getElementById('comment_name').addEventListener('input', saveCommentData);
    document.getElementById('comment_name').addEventListener('input', activateSaveCommentButton);
    document.getElementById('comment_text').addEventListener('input', saveCommentData);
    document.getElementById('comment_text').addEventListener('input', activateSaveCommentButton);

    document.getElementById('save-comment-btn').addEventListener('click', onSaveComment);

    document.getElementById('cancel-comment-save-btn').addEventListener('click', clearСommentForm);
});
