<?php use App\Core\View; ?>
<?php
$clickable = $clickable ?? false;
$rating    = $doctor->avgRating;
$fullStars = (int)round($rating);
$tag       = $clickable ? 'a' : 'div';
$href      = $clickable ? ' href="' . BASE_URL . '/doctors/' . $doctor->id . '"' : '';
?>
<<?= $tag ?><?= $href ?> class="doctor-card">
    <div class="doctor-card__media">
        <?php if ($doctor->photoUrl): ?>
            <img src="<?= View::e($doctor->photoUrl) ?>"
                 alt="Фото <?= View::e($doctor->fullName) ?>"
                 loading="lazy" decoding="async">
        <?php else: ?>
            <div class="doctor-card__avatar" aria-label="Аватар <?= View::e($doctor->fullName) ?>">
                <?= View::e(View::initials($doctor->fullName)) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="doctor-card__body">
        <div class="doctor-card__name"><?= View::e($doctor->fullName) ?></div>
        <div class="doctor-card__spec"><?= View::e($doctor->specialization) ?></div>
        <?php if ($doctor->bio !== ''): ?>
            <div class="doctor-card__bio"><?= View::e(mb_strimwidth($doctor->bio, 0, 90, '…')) ?></div>
        <?php endif; ?>

        <?php if ($rating > 0): ?>
        <div class="doctor-card__rating">
            <span class="doctor-card__stars" aria-label="Рейтинг <?= View::e((string)$rating) ?> из 5">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                         class="<?= $i <= $fullStars ? 'filled' : '' ?>"
                         aria-hidden="true">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                <?php endfor; ?>
            </span>
            <span><?= View::e((string)$rating) ?> (<?= $doctor->reviewCount ?> отз.)</span>
        </div>
        <?php else: ?>
        <div class="doctor-card__rating u-text-subtle">Отзывов пока нет</div>
        <?php endif; ?>
    </div>

    <?php if (!$clickable): ?>
    <div class="doctor-card__footer">
        <a href="<?= BASE_URL ?>/doctors/<?= $doctor->id ?>"
           class="btn btn--secondary btn--sm u-flex-1 u-jc-center">Подробнее</a>
        <?php if (empty($_SESSION['user_role']) || $_SESSION['user_role'] === 'patient'): ?>
            <a href="<?= BASE_URL ?>/patient/book?doctor_id=<?= $doctor->id ?>"
               class="btn btn--primary btn--sm u-flex-1 u-jc-center">Записаться</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</<?= $tag ?>>
