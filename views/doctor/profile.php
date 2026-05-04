<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<a href="<?= BASE_URL ?>/doctor/dashboard" class="back-link">← Дашборд</a>

<?php if ($flash): ?>
    <div class="alert alert-success">✅ <?= View::e($flash) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= View::e($error) ?></div>
<?php endif; ?>

<div class="page-header">
    <h1 class="page-title">Мой профиль</h1>
</div>

<div class="card">

    <!-- Текущее фото -->
    <div style="text-align:center;margin-bottom:24px">
        <?php if ($profile['photo_url']): ?>
            <img src="<?= View::e($profile['photo_url']) ?>" alt=""
                 style="width:100px;height:100px;border-radius:50%;object-fit:cover">
        <?php else: ?>
            <div style="width:100px;height:100px;border-radius:50%;background:#e8eaf0;
                        display:flex;align-items:center;justify-content:center;
                        font-size:36px;margin:0 auto">👨‍⚕️</div>
        <?php endif; ?>
        <div style="margin-top:10px;font-size:16px;font-weight:600">
            <?= View::e($profile['full_name']) ?>
        </div>
        <div style="color:#4a90e2;font-size:13px"><?= View::e($profile['specialization']) ?></div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/doctor/profile">
        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">

        <div class="form-group">
            <label>Ссылка на фото (URL)</label>
            <input type="url" name="photo_url"
                   value="<?= View::e($profile['photo_url'] ?? '') ?>"
                   placeholder="https://example.com/photo.jpg">
            <small class="text-muted">Вставьте прямую ссылку на изображение</small>
        </div>

        <div class="form-group">
            <label>О себе</label>
            <textarea name="bio" rows="5"
                      placeholder="Расскажите о своём опыте, специализации..."><?= View::e($profile['bio'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%">
            Сохранить изменения
        </button>
    </form>
</div>