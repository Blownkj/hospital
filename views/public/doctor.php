<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';

$dayNames = [1=>'Пн',2=>'Вт',3=>'Ср',4=>'Чт',5=>'Пт',6=>'Сб',7=>'Вс'];
$rating   = $doctor->avgRating;
$fullStars = (int)round($rating);
?>

<a href="<?= BASE_URL ?>/doctors" class="back-link">
    <?php icon('chevron-right', 16, 'icon') ?>
    Все врачи
</a>

<!-- Шапка врача -->
<div class="card u-mb-4">
    <div class="card__body">
        <div class="doctor-profile">
            <?php if ($doctor->photoUrl): ?>
                <img class="doctor-profile__photo"
                     src="<?= View::e($doctor->photoUrl) ?>" alt="">
            <?php else: ?>
                <div class="doctor-profile__avatar">
                    <?= View::e(View::initials($doctor->fullName)) ?>
                </div>
            <?php endif; ?>

            <div class="doctor-profile__body">
                <h1 class="doctor-profile__name"><?= View::e($doctor->fullName) ?></h1>
                <div class="doctor-profile__spec"><?= View::e($doctor->specialization) ?></div>

                <?php if ($rating > 0): ?>
                <div class="doctor-profile__rating">
                    <div class="doctor-profile__stars" aria-label="Рейтинг <?= $rating ?> из 5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                 class="<?= $i <= $fullStars ? 'filled' : '' ?>" aria-hidden="true">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        <?php endfor; ?>
                    </div>
                    <span class="doctor-profile__rating-val"><?= $rating ?></span>
                    <span class="doctor-profile__rating-count">(<?= count($reviews) ?> отзывов)</span>
                </div>
                <?php endif; ?>

                <?php if ($doctor->bio !== ''): ?>
                    <p class="doctor-profile__bio"><?= View::e($doctor->bio) ?></p>
                <?php endif; ?>

                <?php if (empty($_SESSION['user_role']) || $_SESSION['user_role'] === 'patient'): ?>
                    <a href="<?= BASE_URL ?>/patient/book?doctor_id=<?= $doctor->id ?>"
                       class="btn btn--primary u-mt-4">
                        Записаться на приём
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Расписание -->
<div class="card u-mb-4">
    <div class="card__body">
        <h2 class="card__title u-mb-4">Расписание приёма</h2>
        <?php if (empty($scheduleByDay)): ?>
            <p class="u-text-sm u-text-subtle">Расписание не указано.</p>
        <?php else: ?>
            <div class="schedule-grid">
                <?php for ($d = 1; $d <= 7; $d++): ?>
                    <?php if (isset($scheduleByDay[$d])): ?>
                        <?php $s = $scheduleByDay[$d]; ?>
                        <div class="schedule-day">
                            <div class="schedule-day__name"><?= $dayNames[$d] ?></div>
                            <div class="schedule-day__time"><?= substr($s['start_time'],0,5) ?>–<?= substr($s['end_time'],0,5) ?></div>
                        </div>
                    <?php else: ?>
                        <div class="schedule-day schedule-day--off">
                            <div class="schedule-day__name"><?= $dayNames[$d] ?></div>
                            <div class="schedule-day__time">выходной</div>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Отзывы -->
<div class="card">
    <div class="card__body">
        <h2 class="card__title u-mb-4">Отзывы пациентов</h2>
        <?php if (empty($reviews)): ?>
            <p class="u-text-sm u-text-subtle u-text-center u-py-5">
                Отзывов пока нет.
            </p>
        <?php else: ?>
            <div class="review-list">
                <?php foreach ($reviews as $r): ?>
                    <?php $rStars = (int)$r['rating']; ?>
                    <div class="review-item">
                        <div class="review-item__header">
                            <div class="review-item__name"><?= View::e($r['patient_name']) ?></div>
                            <div class="review-item__meta">
                                <div class="review-item__stars" aria-label="Оценка <?= $rStars ?> из 5">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                             class="<?= $i <= $rStars ? 'filled' : '' ?>" aria-hidden="true">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <span class="review-item__date"><?= date('d.m.Y', strtotime($r['created_at'])) ?></span>
                            </div>
                        </div>
                        <p class="review-item__text"><?= View::e($r['text']) ?></p>
                        <?php if (!empty($r['admin_reply'])): ?>
                            <div class="review-item__reply">
                                <div class="review-item__reply-label">Ответ клиники</div>
                                <p class="review-item__reply-text"><?= View::e($r['admin_reply']) ?></p>
                                <div class="review-item__reply-date"><?= date('d.m.Y', strtotime($r['admin_reply_at'])) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
