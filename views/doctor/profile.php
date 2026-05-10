<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<a href="<?= BASE_URL ?>/doctor/dashboard" class="back-link">← Дашборд</a>

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

        <dl class="profile-info">
            <div class="profile-info__row">
                <dt class="profile-info__label">О себе</dt>
                <dd class="profile-info__value">
                    <?= $profile['bio'] ? nl2br(View::e($profile['bio'])) : '<span class="u-text-muted">Не указано</span>' ?>
                </dd>
            </div>
        </dl>

        <p class="u-text-muted u-text-sm u-mt-4">
            Для изменения данных профиля (ФИО, описание, фото) обратитесь к администратору.
        </p>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/footer.php'; ?>
