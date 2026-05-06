<?php use App\Core\View; ?>
<div class="empty-state">
    <div class="empty-state__icon" aria-hidden="true">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
            <path d="M12 11h4M12 16h4M8 11h.01M8 16h.01"/>
        </svg>
    </div>
    <p class="empty-state__title"><?= View::e($emptyMessage ?? 'Ничего не найдено') ?></p>
    <?php if (!empty($emptySubtext ?? '')): ?>
        <p class="empty-state__text"><?= View::e($emptySubtext) ?></p>
    <?php endif; ?>
    <?php if (!empty($emptyLinkUrl ?? '')): ?>
        <a href="<?= View::e($emptyLinkUrl) ?>" class="btn btn--primary">
            <?= View::e($emptyLinkText ?? 'Перейти') ?>
        </a>
    <?php endif; ?>
</div>
