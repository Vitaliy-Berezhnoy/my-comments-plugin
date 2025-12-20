<?php
/**
 * Пагинация для таблицы комментариев
 * @var array $pagination_data
 */
$current_page = $pagination_data['current_page'];
$total_pages = ceil($pagination_data['total'] / $pagination_data['per_page']);
?>

<nav aria-label="Навигация по комментариям">
    <ul class="pagination pagination-sm mb-0">
        <!-- Кнопка "Назад" -->
        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link"
               href="?paged=<?php echo $current_page - 1; ?>#<?php echo $table_id; ?>">
                &laquo;
            </a>
        </li>

        <!-- Номера страниц -->
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                <a class="page-link"
                   href="?paged=<?php echo $i; ?>#<?php echo $table_id; ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>

        <!-- Кнопка "Вперед" -->
        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link"
               href="?paged=<?php echo $current_page + 1; ?>#<?php echo $table_id; ?>">
                &raquo;
            </a>
        </li>
    </ul>
</nav>