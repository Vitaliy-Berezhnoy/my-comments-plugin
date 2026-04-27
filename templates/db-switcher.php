<div id="db-switcher" class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5>Выбор базы данных для комментариев</h5>
        </div>
        <div class="card-body">
            <form method="post" action="" class="needs-validation" novalidate>
                <?php wp_nonce_field('save_db_choice_action', 'save_db_choice_nonce'); ?>

                <!-- Скрытое поле для передачи якоря -->
                <input type="hidden" name="anchor" value="db-switcher">
                
                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           name="db_choice"
                           id="mysql"
                           value="mysql" <?php echo $current_db === 'mysql' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="mysql">
                        MySQL (основная база)
                    </label>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input"
                           type="radio"
                           name="db_choice"
                           id="postgres"
                           value="postgres" <?php echo $current_db === 'postgres' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="postgres">
                        PostgreSQL (альтернативная база)
                    </label>
                </div>
                <button type="submit"
                        id="apply-selection-btn"
                        name="save_db_choice"
                        class="btn btn-primary mt-3"
                        disabled>
                    Применить выбор
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Функция активирует кнопку "Применить выбор" 
    // если выбранная БД отличается от подключенной БД
    function toggleApplySelectionButton() {
        const selectedRadio = document.querySelectorAll('input[name="db_choice"]:checked');
        const nameDbChoice = selectedRadio[0].id;
        const applySelectionBtn =document.getElementById('apply-selection-btn');
        const currentDb = '<?php echo $current_db; ?>'; // передаём PHP‑переменную в JS
        applySelectionBtn.disabled = (nameDbChoice === currentDb);
    }


    document.addEventListener('DOMContentLoaded', function() {
        toggleApplySelectionButton();
        document.querySelectorAll('input[name="db_choice"]').forEach(choice => {
            choice.addEventListener('change', toggleApplySelectionButton);
        });
    })
</script>