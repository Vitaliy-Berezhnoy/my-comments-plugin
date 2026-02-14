<?php
/**
 * Шаблон уведомления о результате отправки
 * 
 */
?>

<div class="<?php echo match ($_SESSION['comment_status']['type']) {
    'success' => 'alert alert-success',
    'warning' => 'alert alert-warning',
    'error' => 'alert alert-danger',
    default => 'alert alert-info',
};
?>" role="alert">
    <?php echo esc_html($_SESSION['comment_status']['message']); ?>
</div>
