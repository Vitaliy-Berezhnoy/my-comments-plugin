<?php
/**
 * Модальное окно для вывода уведомлений 
 */
?>

<div class="modal fade <?php echo match ($comment_status['type']) {
    'success' => 'modal-success',
    'warning' => 'modal-warning',
    'error' => 'modal-error',
    default => 'modal-info',
}; ?>" id="commentStatusModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header <?php echo match ($comment_status['type']) {
          'success' => 'bg-success text-white',
          'warning' => 'bg-warning text-dark',
          'error' => 'bg-danger text-white',
          default => 'bg-info text-white',
      }; ?>">
        <h5 class="modal-title">
          <?php echo match ($comment_status['type']) {
              'success' => 'Успех!',
              'warning' => 'Внимание!',
              'error' => 'Ошибка!',
              default => 'Информация',
          }; ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php echo $comment_status['message']; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>

<script>
// Автоматическое открытие модального окна при загрузке
document.addEventListener('DOMContentLoaded', function() {
  var modal = new bootstrap.Modal(document.getElementById('commentStatusModal'));
  modal.show();
});
</script>
