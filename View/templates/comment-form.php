<form id="form_comment" method="post" action="" class="needs-validation">
    <?php wp_nonce_field('add_comment_action', 'add_comment_nonce'); ?>

    <!-- Скрытое поле для передачи якоря -->
    <input type="hidden" name="anchor" value="form_comment">

    <input type="hidden" name="save_comment" value="">

    <div class="row g-3">
        <!-- Поле имени -->
        <div class="col-12">
            <label for="comment_name" class="form-label fw-medium">
                <?php esc_html_e('Ваше имя', 'my-comments'); ?>
            </label>
            <input 
                type="text"
                class="form-control"
                id="comment_name"
                name="comment_name"
                value="<?php echo isset($_POST['comment_name']) ? htmlspecialchars($_POST['comment_name']) : ''; ?>"
                placeholder="Введите ваше имя"
            >
        </div>

        <!-- Поле комментария -->
        <div class="col-12">
            <label for="comment_text" class="form-label fw-medium">
                <?php esc_html_e('Комментарий', 'my-comments'); ?>
            </label>
            <textarea
                class="form-control"
                id="comment_text"
                name="comment_text"
                rows="4"
                placeholder="Поделитесь своими впечатлениями, замечаниями или предложениями с автором сайта."
            ><?php echo isset($_POST['comment_text']) ? esc_textarea($_POST['comment_text']) : ''; ?></textarea>
        </div>

        <!-- Кнопка отправки -->
        <div class="col-12">
            <button
                type="button"
                id="save-comment-btn"
                class="btn btn-primary px-4 py-2"
                disabled
            >
                <?php esc_html_e('Сохранить комментарий', 'my-comments'); ?>
            </button>
        </div>
    </div>
    <!-- Подключение модального окна -->
    <?php include plugin_dir_path(__DIR__) . 'modal-window/fields-not-filled.php' ?>
</form>

    <!-- JavaScript для активации кнопки 'Сохранить комментарий' -->
<script>
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
</script>
