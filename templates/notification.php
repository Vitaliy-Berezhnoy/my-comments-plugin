<?php
/**
 * Шаблон уведомления о результате отправки
 * @var array $submission_result Массив с ключами 'success' и 'message'
 */
?>

<?php if ($submission_result): ?>
    <div class="alert alert-<?php echo $submission_prepared['success'] ? 'success' : 'danger'; ?>">
        <?php echo htmlspecialchars($submission_result['message']); ?>
    </div>
<?php endif; ?>

<div class="<?php 
    echo $submission_result['success'] 
        ? 'alert alert-success'
        : 'notice notice-error';
?>">
    <p><?php echo htmlspecialchars($submission_result['message']); ?></p>
</div>
