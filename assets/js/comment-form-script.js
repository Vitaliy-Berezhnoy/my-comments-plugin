
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
    activateSaveCommentButton();
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {

    activateSaveCommentButton();
    document.getElementById('comment_name').addEventListener('input', activateSaveCommentButton);
    document.getElementById('comment_text').addEventListener('input', activateSaveCommentButton);

    document.getElementById('save-comment-btn').addEventListener('click', onSaveComment);

    document.getElementById('cancel-comment-save-btn').addEventListener('click', clearСommentForm);
});
