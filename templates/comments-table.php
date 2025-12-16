<?php
/**
 * Template for displaying comments table (enhanced with Bootstrap)
 * @var array $comments List of comment objects
 * @var string $table_id Optional table ID
 */
?>
<?php if (empty($comments)): ?>
    <div class="alert alert-info" role="alert">
        <?php esc_html_e('Комментариев пока нет.', 'my-comments'); ?>
    </div>
<?php else: ?>
    <div class="table-responsive mt-4">
        <table id="<?php echo htmlspecialchars($table_id); ?>" class="table table-striped table-hover table-bordered">
            <thead class="table-light">
                <tr>
                    <th scope="col"><?php esc_html_e('ID', 'my-comments'); ?></th>
                    <th scope="col"><?php esc_html_e('Имя', 'my-comments'); ?></th>
                    <th scope="col"><?php esc_html_e('Комментарий', 'my-comments'); ?></th>
                    <th scope="col"><?php esc_html_e('Дата', 'my-comments'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td class="fw-bold"><?php echo htmlspecialchars($comment->id); ?></td>
                        <td><?php echo htmlspecialchars($comment->name); ?></td>
                        <td>
                            <div class="text-wrap w-100">
                                <?php echo htmlspecialchars($comment->comment); ?>
                            </div>
                        </td>
                        <td class="text-muted small"><?php echo htmlspecialchars($comment->created_at); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

