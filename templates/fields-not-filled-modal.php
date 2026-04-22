<?php
/**
 * Модальное окно с просьбой заполнить все поля
 *  в форме для добавления нового комментария.
 */
?>

<div class="modal fade" id="fields-not-filled-modal" tabindex="-1" aria-labelledby="fieldsNotFilledModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Заголовок окна с крестиком -->
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="fieldsNotFilledModalLabel">Внимание</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Тело окна  -->
      <div class="modal-body">
        Пожалуйста, заполните оба поля или отмените операцию.
      </div>
      <!-- Нижний блок с кнопками -->
      <div class="modal-footer">
          <button type="button" id="cancel-comment-save-btn" class="btn btn-outline-dark" data-bs-dismiss="modal">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle me-1" viewBox="0 0 16 16">
                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
              </svg>
              Очистить форму
          </button>

          <button type="button" id="continue-comment-btn" class="btn btn-outline-success" data-bs-dismiss="modal">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 12 12" style="margin-left: 6px;">
                  <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l5 5a.5.5 0 0 1 0 .708l-5 5a.5.5 0 0 1-.708-.708L8.293 6.5 4.646 2.854a.5.5 0 0 1 0-.708z"/>
              </svg>
              Продолжить
          </button>
      </div>
    </div>
  </div>
</div>
