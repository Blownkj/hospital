<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';

$specIconMap = [
    'Терапия'       => 'stethoscope',
    'Кардиология'   => 'heart',
    'Неврология'    => 'brain',
    'Хирургия'      => 'scissors',
    'Офтальмология' => 'eye',
    'Ортопедия'     => 'bone',
    'Гинекология'   => 'flower',
];
?>

<a href="<?= BASE_URL ?>/patient/dashboard" class="back-link">← Личный кабинет</a>
<h1 class="page-title u-mb-6">Запись к врачу</h1>

<?php include ROOT_PATH . '/views/partials/flash.php'; ?>

<!-- Шаг 1: Специализация -->
<div class="booking-step">
    <div class="booking-step-title">
        <?php if ($selectedDoctor): ?>
            <span class="step-done">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
            </span>
        <?php else: ?>
            <span class="step-num">1</span>
        <?php endif; ?>
        Выберите специализацию
    </div>

    <?php if (!$selectedDoctor): ?>
        <div class="spec-grid">
            <?php foreach ($specs as $spec): ?>
                <a href="<?= BASE_URL ?>/patient/book?spec_id=<?= (int)$spec['id'] ?>"
                   class="spec-card <?= ((int)($selectedSpec['id'] ?? 0) === (int)$spec['id']) ? 'active' : '' ?>">
                    <?php if (!empty($spec['image_url'])): ?>
                        <img class="spec-card__img" src="<?= View::e($spec['image_url']) ?>"
                             alt="<?= View::e($spec['name']) ?>" loading="lazy" width="40" height="40">
                    <?php else: ?>
                        <div class="spec-card__icon">
                            <?php icon($specIconMap[$spec['name']] ?? 'stethoscope', 20) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div class="spec-card__name"><?= View::e($spec['name']) ?></div>
                        <div class="spec-card__count"><?= (int)$spec['count'] ?> врача(-ей)</div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="u-flex u-ai-center u-gap-3">
            <div class="spec-card__icon">
                <?php icon($specIconMap[$selectedDoctor->specialization] ?? 'stethoscope', 20) ?>
            </div>
            <span class="u-fw-semibold"><?= View::e($selectedDoctor->specialization) ?></span>
            <a href="<?= BASE_URL ?>/patient/book" class="btn btn--ghost btn--sm u-ms-auto">Сменить</a>
        </div>
    <?php endif; ?>
</div>

<!-- Шаг 2: Врач (если выбрана специализация, но не врач) -->
<?php if ($selectedSpec && !$selectedDoctor): ?>
<div class="booking-step">
    <div class="booking-step-title">
        <span class="step-num">2</span>
        Выберите врача
    </div>
    <div class="doctors-grid">
        <?php foreach ($filteredDoctors as $doctor): ?>
            <a href="<?= BASE_URL ?>/patient/book?doctor_id=<?= $doctor->id ?>"
               class="doctor-card">
                <div class="doctor-card__avatar">
                    <?= View::e(View::initials($doctor->fullName)) ?>
                </div>
                <div class="doctor-card__body">
                    <div class="doctor-card__name"><?= View::e($doctor->fullName) ?></div>
                    <div class="doctor-card__spec"><?= View::e($doctor->specialization) ?></div>
                    <div class="doctor-card__bio"><?= View::e(mb_strimwidth($doctor->bio, 0, 90, '...')) ?></div>
                    <?php if ($doctor->avgRating > 0): ?>
                        <div class="doctor-card__rating">
                            <span class="doctor-card__stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                         class="<?= $i <= round($doctor->avgRating) ? 'filled' : '' ?>"
                                         aria-hidden="true">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                <?php endfor; ?>
                            </span>
                            <span class="u-text-xs u-text-muted"><?= View::e((string)$doctor->avgRating) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Шаг 3: Дата -->
<?php if ($selectedDoctor): ?>
<div class="booking-step">
    <div class="booking-step-title">
        <?php if ($selectedDate): ?>
            <span class="step-done">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
            </span>
        <?php else: ?>
            <span class="step-num">2</span>
        <?php endif; ?>
        Врач: <?= View::e($selectedDoctor->fullName) ?>
        <a href="<?= BASE_URL ?>/patient/book?spec_id=<?= $selectedDoctor->specializationId ?>"
           class="btn btn--ghost btn--sm u-ms-auto">Сменить</a>
    </div>

    <?php if (empty($workingDays)): ?>
        <p class="u-text-muted">Нет доступных дат в ближайшие 2 месяца.</p>
    <?php else: ?>
        <div class="date-pills">
            <?php foreach ($workingDays as $day): ?>
                <?php
                    $dow = ['','Пн','Вт','Ср','Чт','Пт','Сб','Вс'][(int)date('N',strtotime($day))];
                ?>
                <a href="<?= BASE_URL ?>/patient/book?doctor_id=<?= $selectedDoctor->id ?>&date=<?= $day ?>"
                   class="date-pill <?= $day === $selectedDate ? 'active' : '' ?>">
                    <?= date('d.m', strtotime($day)) ?>
                    <span class="u-opacity-70 u-text-xs"><?= $dow ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Шаг 4: Время -->
<?php if ($selectedDate): ?>
<div class="booking-step">
    <div class="booking-step-title">
        <span class="step-num">3</span>
        Выберите время
        <span class="u-text-sm u-fw-normal u-text-muted">
            — <?= date('d.m.Y', strtotime($selectedDate)) ?>
        </span>
    </div>

    <?php
    $available = array_filter($slots, fn($s) => $s['available']);
    ?>

    <?php if (empty($slots)): ?>
        <p class="u-text-muted">В этот день врач не работает.</p>
    <?php elseif (empty($available)): ?>
        <p class="u-text-muted">Все слоты заняты — выберите другую дату.</p>
    <?php else: ?>
        <form method="POST" action="<?= BASE_URL ?>/patient/book">
            <input type="hidden" name="csrf_token"
                   value="<?= View::e(\App\Core\Session::generateCsrfToken()) ?>">
            <input type="hidden" name="doctor_id" value="<?= $selectedDoctor->id ?>">
            <input type="hidden" name="date"      value="<?= View::e($selectedDate) ?>">
            <input type="hidden" name="time"      id="selected-time" value="">

            <div class="time-slots">
                <?php foreach ($slots as $slot): ?>
                    <?php if ($slot['available']): ?>
                        <button type="button" class="slot-btn"
                                data-time="<?= View::e($slot['time']) ?>"
                                onclick="selectSlot('<?= View::e($slot['time']) ?>', this)">
                            <?= View::e($slot['time']) ?>
                        </button>
                    <?php else: ?>
                        <span class="slot-taken"><?= View::e($slot['time']) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div id="confirm-block" class="u-hidden">
                <div class="confirm-block">
                    <?php icon('calendar', 16) ?>
                    <strong><?= date('d.m.Y', strtotime($selectedDate)) ?></strong>
                    в <strong id="confirm-time">—</strong>
                    · <?= View::e($selectedDoctor->fullName) ?>
                </div>
                <button type="submit" class="btn btn--primary">
                    Подтвердить запись
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
