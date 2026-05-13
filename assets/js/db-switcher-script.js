    // Функция активирует кнопку "Применить выбор" 
    // если выбранная БД отличается от подключенной БД
    function toggleApplySelectionButton() {
        const nameDbChoice = document.querySelector('input[name="db_choice"]:checked').id;
        const applySelectionBtn =document.getElementById('apply-selection-btn');
        const currentDb = nameActiveDb.currentDb // Текущая активная БД
        applySelectionBtn.disabled = (nameDbChoice === currentDb);
    }

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        toggleApplySelectionButton();
        document.querySelectorAll('input[name="db_choice"]').forEach(choice => {
            choice.addEventListener('change', toggleApplySelectionButton);
        });
    })