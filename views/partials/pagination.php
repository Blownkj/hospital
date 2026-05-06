<?php
/**
 * Pagination partial.
 * Required vars: $paginator (App\Core\Paginator), $qs (callable(int):string)
 */
if (!isset($paginator) || $paginator->totalPages <= 1) {
    return;
}
?>
<nav class="pagination" aria-label="Страницы">
    <?php if ($paginator->hasPrev()): ?>
        <a href="?<?= $qs($paginator->prevPage()) ?>" class="pagination__item">←</a>
    <?php else: ?>
        <span class="pagination__item pagination__item--disabled">←</span>
    <?php endif; ?>

    <?php foreach ($paginator->pages() as $p): ?>
        <?php if ($p === $paginator->currentPage): ?>
            <span class="pagination__item pagination__item--current"><?= $p ?></span>
        <?php else: ?>
            <a href="?<?= $qs($p) ?>" class="pagination__item"><?= $p ?></a>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($paginator->hasNext()): ?>
        <a href="?<?= $qs($paginator->nextPage()) ?>" class="pagination__item">→</a>
    <?php else: ?>
        <span class="pagination__item pagination__item--disabled">→</span>
    <?php endif; ?>
</nav>
