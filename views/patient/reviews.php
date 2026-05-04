<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<a href="<?= BASE_URL ?>/patient/dashboard" class="back-link">← Личный кабинет</a>

<?php if ($flash): ?>
    <div class="alert alert-success">✅ <?= View::e($flash) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= View::e($error) ?></div>
<?php endif; ?>

<div class="page-header">
    <h1 class="page-title">Мои отзывы</h1>
</div>

<!-- Форма нового отзыва -->
<?php if (!empty($canReview)): ?>
<div class="card" style="margin-bottom:20px">
    <div class="card-title">✍️ Оставить отзыв</div>
    <form method="POST" action="<?= BASE_URL ?>/patient/reviews/submit">
        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">

        <div class="form-group">
            <label>Врач</label>
            <select name="doctor_id" required>
                <option value="">— Выберите врача —</option>
                <?php foreach ($canReview as $d): ?>
                <option value="<?= (int)$d['id'] ?>">
                    <?= View::e($d['full_name']) ?> — <?= View::e($d['specialization']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Оценка</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:14px">
                    <input type="radio" name="rating" value="<?= $i ?>" required>
                    <?= str_repeat('⭐', $i) ?>
                </label>
                <?php endfor; ?>
            </div>
        </div>

        <div class="form-group">
            <label>Отзыв</label>
            <textarea name="text" rows="4" placeholder="Расскажите о вашем визите..." required minlength="10"></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%">Отправить на модерацию</button>
    </form>
</div>
<?php endif; ?>

<!-- Мои отзывы -->
<div class="card">
    <div class="card-title">📋 История отзывов</div>

    <?php if (empty($myReviews)): ?>
        <p class="text-muted text-center" style="padding:24px 0">Вы ещё не оставляли отзывов.</p>
    <?php else: ?>
        <?php foreach ($myReviews as $r): ?>
        <div style="padding:14px 0;border-bottom:1px solid var(--border)">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px">
                <div>
                    <div style="font-weight:600;font-size:14px"><?= View::e($r['doctor_name']) ?></div>
                    <div class="text-muted" style="font-size:12px"><?= View::e($r['specialization']) ?></div>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span><?= str_repeat('⭐', (int)$r['rating']) ?></span>
                    <?php if ($r['is_approved']): ?>
                        <span class="badge badge-completed">✅ Опубликован</span>
                    <?php else: ?>
                        <span class="badge badge-pending">⏳ На модерации</span>
                    <?php endif; ?>
                </div>
            </div>
            <p style="margin-top:8px;font-size:14px"><?= View::e($r['text']) ?></p>
            <div class="text-muted" style="font-size:12px"><?= date('d.m.Y', strtotime($r['created_at'])) ?></div>
            <?php if (!empty($r['admin_reply'])): ?>
            <div style="margin-top:10px;padding:10px 14px;background:var(--color-bg-secondary,#f8f9fa);border-left:3px solid var(--color-primary,#3b82f6);border-radius:4px">
                <div style="font-size:12px;font-weight:600;margin-bottom:4px;color:var(--color-primary,#3b82f6)">Ответ клиники</div>
                <p style="font-size:13px;line-height:1.6;margin:0"><?= View::e($r['admin_reply']) ?></p>
                <div class="text-muted" style="font-size:11px;margin-top:4px">
                    <?= date('d.m.Y', strtotime($r['admin_reply_at'])) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>