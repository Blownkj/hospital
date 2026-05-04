<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<a href="<?= BASE_URL ?>/admin/dashboard" class="back-link">← Дашборд</a>

<?php include ROOT_PATH . '/views/partials/flash.php'; ?>

<div class="page-header">
    <h1 class="page-title">Модерация отзывов</h1>
</div>

<!-- ── На модерации ── -->
<h2 style="font-size:16px;font-weight:600;margin-bottom:12px">
    На модерации
    <?php if (!empty($pending)): ?>
        <span class="badge badge-pending" style="font-size:12px"><?= count($pending) ?></span>
    <?php endif; ?>
</h2>

<?php if (empty($pending)): ?>
<div class="card" style="text-align:center;padding:32px;margin-bottom:24px">
    <p class="text-muted">Нет отзывов на модерации.</p>
</div>
<?php else: ?>
<?php foreach ($pending as $r):
    $stars = str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']);
?>
<div class="card" style="margin-bottom:14px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px">
        <div style="flex:1">
            <div style="font-size:14px;font-weight:500;margin-bottom:4px">
                <?= View::e($r['patient_name']) ?>
                <span class="text-muted" style="font-weight:400"> → <?= View::e($r['doctor_name']) ?></span>
            </div>
            <div style="color:#f59e0b;font-size:16px;margin-bottom:6px"><?= $stars ?></div>
            <?php if ($r['text']): ?>
                <p style="font-size:13px;color:var(--color-text-secondary);line-height:1.6">
                    <?= View::e($r['text']) ?>
                </p>
            <?php endif; ?>
            <div class="muted-sm" style="margin-top:6px">
                <?= date('d.m.Y H:i', strtotime($r['created_at'])) ?>
            </div>
        </div>
        <div class="flex-row">
            <form method="POST" action="<?= BASE_URL ?>/admin/review/<?= (int)$r['id'] ?>/approve">
                <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                <button class="btn btn-primary" style="padding:7px 16px;font-size:13px">✓ Опубликовать</button>
            </form>
            <form method="POST" action="<?= BASE_URL ?>/admin/review/<?= (int)$r['id'] ?>/delete"
                  onsubmit="return confirm('Удалить отзыв?')">
                <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                <button class="btn btn-danger" style="padding:7px 16px;font-size:13px">✕ Удалить</button>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- ── Одобренные отзывы ── -->
<h2 style="font-size:16px;font-weight:600;margin:28px 0 12px">
    Одобренные отзывы
    <?php if (!empty($approved)): ?>
        <span class="badge badge-completed" style="font-size:12px"><?= count($approved) ?></span>
    <?php endif; ?>
</h2>

<?php if (empty($approved)): ?>
<div class="card" style="text-align:center;padding:32px">
    <p class="text-muted">Одобренных отзывов пока нет.</p>
</div>
<?php else: ?>
<?php foreach ($approved as $r):
    $stars = str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']);
?>
<div class="card" style="margin-bottom:14px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px">
        <div style="flex:1;min-width:0">
            <div style="font-size:14px;font-weight:500;margin-bottom:4px">
                <?= View::e($r['patient_name']) ?>
                <span class="text-muted" style="font-weight:400"> → <?= View::e($r['doctor_name']) ?></span>
            </div>
            <div style="color:#f59e0b;font-size:16px;margin-bottom:6px"><?= $stars ?></div>
            <?php if ($r['text']): ?>
                <p style="font-size:13px;color:var(--color-text-secondary);line-height:1.6">
                    <?= View::e($r['text']) ?>
                </p>
            <?php endif; ?>
            <div class="muted-sm" style="margin-top:4px">
                <?= date('d.m.Y H:i', strtotime($r['created_at'])) ?>
            </div>

            <?php if ($r['admin_reply']): ?>
            <div style="margin-top:12px;padding:10px 14px;background:var(--color-bg-secondary,#f8f9fa);border-left:3px solid var(--color-primary,#3b82f6);border-radius:4px">
                <div style="font-size:12px;font-weight:600;margin-bottom:4px;color:var(--color-primary,#3b82f6)">Ответ клиники</div>
                <p style="font-size:13px;line-height:1.6;margin:0"><?= View::e($r['admin_reply']) ?></p>
                <div class="muted-sm" style="margin-top:4px">
                    <?= date('d.m.Y H:i', strtotime($r['admin_reply_at'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Форма ответа -->
            <form method="POST" action="<?= BASE_URL ?>/admin/review/<?= (int)$r['id'] ?>/reply"
                  style="margin-top:12px">
                <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                <textarea name="reply" rows="3"
                          placeholder="Напишите ответ от клиники..."
                          style="width:100%;box-sizing:border-box;font-size:13px;padding:8px 10px;border:1px solid var(--border);border-radius:6px;resize:vertical"
                          required minlength="5"><?= View::e($r['admin_reply'] ?? '') ?></textarea>
                <button class="btn btn-primary" style="margin-top:6px;padding:7px 16px;font-size:13px">
                    <?= $r['admin_reply'] ? '✎ Обновить ответ' : '✉ Ответить' ?>
                </button>
            </form>
        </div>
        <div>
            <form method="POST" action="<?= BASE_URL ?>/admin/review/<?= (int)$r['id'] ?>/delete"
                  onsubmit="return confirm('Удалить отзыв?')">
                <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                <button class="btn btn-danger" style="padding:7px 16px;font-size:13px">✕ Удалить</button>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
