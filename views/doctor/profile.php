<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<a href="<?= BASE_URL ?>/doctor/dashboard" class="back-link">← Дашборд</a>

<?php if ($flash): ?>
    <div class="alert alert--success" role="alert">
        <span class="alert__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
            </svg>
        </span>
        <span class="alert__body"><?= View::e($flash) ?></span>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert--error" role="alert">
        <span class="alert__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
            </svg>
        </span>
        <span class="alert__body"><?= View::e($error) ?></span>
    </div>
<?php endif; ?>

<div class="page-header">
    <h1 class="page-title">Мой профиль</h1>
</div>

<div class="card">
    <div class="card__body">
        <div class="u-text-center u-mb-6">
            <?php if ($profile['photo_url']): ?>
                <img src="<?= View::e($profile['photo_url']) ?>" alt=""
                     class="doctor-profile__photo u-mx-auto">
            <?php else: ?>
                <div class="doctor-profile__avatar u-mx-auto">
                    <?= View::e(View::initials($profile['full_name'])) ?>
                </div>
            <?php endif; ?>
            <div class="u-mt-3 u-text-base u-fw-semibold">
                <?= View::e($profile['full_name']) ?>
            </div>
            <div class="u-text-primary u-text-sm"><?= View::e($profile['specialization']) ?></div>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/doctor/profile">
            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">

            <div class="form__group">
                <label class="form__label" for="photo_url">Ссылка на фото (URL)</label>
                <input class="form__control" type="url" id="photo_url" name="photo_url"
                       value="<?= View::e($profile['photo_url'] ?? '') ?>"
                       placeholder="https://example.com/photo.jpg">
                <p class="form__hint">Вставьте прямую ссылку на изображение</p>
            </div>

            <div class="form__group">
                <label class="form__label" for="bio">О себе</label>
                <textarea class="form__control" id="bio" name="bio" rows="5"
                          placeholder="Расскажите о своём опыте, специализации..."><?= View::e($profile['bio'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn--primary btn--block">
                Сохранить изменения
            </button>
        </form>
    </div>
</div>
