<form method="post" action="" class="needs-validation">
    <?php wp_nonce_field('add_comment_action', 'add_comment_nonce'); ?>

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
                placeholder="Введите ваше имя"
                required
            >
            <div class="invalid-feedback">
                Пожалуйста, укажите имя.
            </div>
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
                required
            ></textarea>
            <div class="invalid-feedback">
                Пожалуйста, введите комментарий.
            </div>
        </div>

        <!-- Кнопка отправки -->
        <div class="col-12">
            <button
                type="submit"
                name="submit_comment"
                class="btn btn-primary px-4 py-2"
            >
                <?php esc_html_e('Отправить комментарий', 'my-comments'); ?>
            </button>
        </div>
    </div>
</form>
