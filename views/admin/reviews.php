<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<a href="<?= BASE_URL ?>/admin/dashboard" class="back-link">← Дашборд</a>

<?php include ROOT_PATH . '/views/partials/flash.php'; ?>

<div class="page-header">
    <h1 class="page-title">Модерация отзывов</h1>
</div>

<!-- На модерации -->
<h2 class="section-heading">
    На модерации
    <?php if (!empty($pending)): ?>
        <span class="badge badge--warning"><?= count($pending) ?></span>
    <?php endif; ?>
</h2>

<?php if (empty($pending)): ?>
<div class="card u-text-center u-p-8 u-mb-6">
    <p class="u-text-muted">Нет отзывов на модерации.</p>
</div>
<?php else: ?>
<?php foreach ($pending as $r): ?>
<div class="admin-review">
    <div class="admin-review__header">
        <div>
            <div class="admin-review__who">
                <?= View::e($r['patient_name']) ?>
                <span>→ <?= View::e($r['doctor_name']) ?></span>
            </div>
            <div class="admin-review__stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                         class="<?= $i <= (int)$r['rating'] ? 'filled' : '' ?>" aria-hidden="true">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                <?php endfor; ?>
            </div>
        </div>
        <div class="admin-review__actions">
            <form method="POST" action="<?= BASE_URL ?>/admin/review/<?= (int)$r['id'] ?>/approve">
                <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                <button class="btn btn--primary btn--sm">
                    <?php icon('check', 14) ?> Опубликовать
                </button>
            </form>
            <form method="POST" action="<?= BASE_URL ?>/admin/review/<?= (int)$r['id'] ?>/delete"
                  onsubmit="return confirm('Удалить отзыв?')">
                <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                <button class="btn btn--danger btn--sm">
                    <?php icon('x', 14) ?> Удалить
                </button>
            </form>
        </div>
    </div>
    <?php if ($r['text']): ?>
        <p class="admin-review__text"><?= View::e($r['text']) ?></p>
    <?php endif; ?>
    <div class="admin-review__date"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- Одобренные отзывы -->
<h2 class="section-heading u-mt-8">
    Одобренные отзывы
    <?php if (!empty($approved)): ?>
        <span class="badge badge--success"><?= count($approved) ?></span>
    <?php endif; ?>
</h2>

<?php if (empty($approved)): ?>
<div class="card u-text-center u-p-8">
    <p class="u-text-muted">Одобренных отзывов пока нет.</p>
</div>
<?php else: ?>
<?php foreach ($approved as $r): ?>
<div class="admin-review">
    <div class="admin-review__header">
        <div class="u-flex-1 u-min-w-0">
            <div class="admin-review__who">
                <?= View::e($r['patient_name']) ?>
                <span>→ <?= View::e($r['doctor_name']) ?></span>
            </div>
            <div class="admin-review__stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                         class="<?= $i <= (int)$r['rating'] ? 'filled' : '' ?>" aria-hidden="true">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                <?php endfor; ?>
            </div>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/admin/review/<?= (int)$r['id'] ?>/delete"
              onsubmit="return confirm('Удалить отзыв?')">
            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
            <button class="btn btn--danger btn--sm">
                <?php icon('x', 14) ?> Удалить
            </button>
        </form>
    </div>

    <?php if ($r['text']): ?>
        <p class="admin-review__text"><?= View::e($r['text']) ?></p>
    <?php endif; ?>
    <div class="admin-review__date"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></div>

    <?php if ($r['admin_reply']): ?>
    <div class="admin-review__reply">
        <div class="admin-review__reply-label">Ответ клиники</div>
        <p class="admin-review__reply-text"><?= View::e($r['admin_reply']) ?></p>
        <div class="u-text-xs u-text-muted u-mt-1">
            <?= date('d.m.Y H:i', strtotime($r['admin_reply_at'])) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Форма ответа -->
    <form method="POST" action="<?= BASE_URL ?>/admin/review/<?= (int)$r['id'] ?>/reply"
          class="u-mt-4">
        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
        <div class="form__group u-mb-2">
            <textarea class="form__control" name="reply" rows="3"
                      placeholder="Напишите ответ от клиники..."
                      required minlength="5"><?= View::e($r['admin_reply'] ?? '') ?></textarea>
        </div>
        <button class="btn btn--secondary btn--sm">
            <?php icon('message-circle', 14) ?>
            <?= $r['admin_reply'] ? 'Обновить ответ' : 'Ответить' ?>
        </button>
    </form>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php
$qs = fn(int $p) => http_build_query(array_merge($_GET, ['page' => $p]));
include ROOT_PATH . '/views/partials/pagination.php';
?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
