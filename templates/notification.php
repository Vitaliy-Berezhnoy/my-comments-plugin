<?php
/**
 * Шаблон уведомления о результате отправки
 * @var array $submission_result Массив с ключами 'success' и 'message'
 */
?>


<?php if (!empty($submission_result)): ?>
    <div class="<?php
        echo $submission_result['success']
            ? 'alert alert-success'
            : 'alert bg-warning';
    ?>">
        <p><?php echo esc_html($submission_result['message']); ?></p>
    </div>
<?php endif; ?>
