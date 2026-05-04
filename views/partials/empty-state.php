<?php use App\Core\View; ?>
<div class="card text-center" style="padding:48px">
    <div style="font-size:40px;margin-bottom:12px"><?= $emptyIcon ?? '📋' ?></div>
    <p class="text-muted mb-2"><?= View::e($emptyMessage ?? 'Ничего не найдено') ?></p>
    <?php if (!empty($emptyLinkUrl ?? '')): ?>
        <a href="<?= View::e($emptyLinkUrl) ?>" class="btn btn-primary">
            <?= View::e($emptyLinkText ?? 'Перейти') ?>
        </a>
    <?php endif; ?>
</div>
