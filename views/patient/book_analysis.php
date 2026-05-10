<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<a href="<?= BASE_URL ?>/patient/dashboard" class="back-link">← Личный кабинет</a>
<h1 class="page-title u-mb-6">Запись на анализ</h1>

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

<!-- Шаг 1: Выбор анализа -->
<div class="booking-step">
    <div class="booking-step-title">
        <?php if ($selectedTest): ?>
            <span class="step-done">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
            </span>
            Анализ выбран
        <?php else: ?>
            <span class="step-num">1</span>
            Выберите анализ
        <?php endif; ?>
    </div>

    <?php if (!$selectedTest): ?>
        <?php foreach ($grouped as $category => $tests): ?>
            <div class="u-mb-5">
                <div class="lab-category">
                    <?= View::e($category) ?>
                </div>
                <div class="lab-grid">
                    <?php foreach ($tests as $test): ?>
                        <a href="<?= BASE_URL ?>/patient/book/analysis?test_id=<?= (int)$test['id'] ?>"
                           class="lab-card">
                            <div class="lab-name"><?= View::e($test['name']) ?></div>
                            <?php if ($test['preparation']): ?>
                                <div class="lab-prep">
                                    <?php icon('bell', 12) ?>
                                    <?= View::e($test['preparation']) ?>
                                </div>
                            <?php endif; ?>
                            <div class="lab-price"><?= number_format((float)$test['price'], 0, '.', ' ') ?> ₽</div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="u-flex u-ai-start u-jc-between u-flex-wrap u-gap-3">
            <div>
                <div class="u-text-base u-fw-semibold"><?= View::e($selectedTest['name']) ?></div>
                <?php if ($selectedTest['preparation']): ?>
                    <div class="alert alert--warning u-mt-2 u-mb-0">
                        <span class="alert__icon"><?php icon('bell', 16) ?></span>
                        <span class="alert__body">Подготовка: <?= View::e($selectedTest['preparation']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="u-flex u-ai-center u-gap-4">
                <span class="u-text-lg u-fw-bold">
                    <?= number_format((float)$selectedTest['price'], 0, '.', ' ') ?> ₽
                </span>
                <a href="<?= BASE_URL ?>/patient/book/analysis" class="btn btn--ghost btn--sm">Сменить</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Шаг 2: Выбор даты -->
<?php if ($selectedTest && !empty($availableDates)): ?>
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
        Выберите дату
    </div>
    <div class="date-pills">
        <?php foreach ($availableDates as $day): ?>
            <?php
                $dow = ['','Пн','Вт','Ср','Чт','Пт','Сб','Вс'][(int)date('N',strtotime($day))];
            ?>
            <a href="<?= BASE_URL ?>/patient/book/analysis?test_id=<?= (int)$selectedTest['id'] ?>&date=<?= $day ?>"
               class="date-pill <?= $day === $selectedDate ? 'active' : '' ?>">
                <?= date('d.m', strtotime($day)) ?>
                <span class="u-opacity-70 u-text-xs"><?= $dow ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Шаг 3: Выбор времени -->
<?php if ($selectedDate): ?>
<div class="booking-step">
    <div class="booking-step-title">
        <span class="step-num">3</span>
        Выберите время
        <span class="u-text-sm u-fw-normal u-text-muted">
            — <?= date('d.m.Y', strtotime($selectedDate)) ?>
        </span>
    </div>

    <?php $available = array_filter($slots, fn($s) => $s['available']); ?>

    <?php if (empty($available)): ?>
        <p class="u-text-muted">Все слоты на этот день заняты. Выберите другую дату.</p>
    <?php else: ?>
        <form method="POST" action="<?= BASE_URL ?>/patient/book/analysis">
            <input type="hidden" name="csrf_token"
                   value="<?= View::e(\App\Core\Session::generateCsrfToken()) ?>">
            <input type="hidden" name="lab_test_id"
                   value="<?= (int)$selectedTest['id'] ?>">
            <input type="hidden" name="date" value="<?= View::e($selectedDate) ?>">
            <input type="hidden" name="time" id="selected-time" value="">

            <div class="time-slots">
                <?php foreach ($slots as $slot): ?>
                    <?php if ($slot['available']): ?>
                        <button type="button" class="slot-btn"
                                data-time="<?= View::e($slot['time']) ?>">
                            <?= View::e($slot['time']) ?>
                        </button>
                    <?php else: ?>
                        <span class="slot-taken"><?= View::e($slot['time']) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div id="confirm-block" class="u-hidden">
                <div class="confirm-block">
                    <?php icon('flask-conical', 16) ?>
                    <strong><?= View::e($selectedTest['name']) ?></strong>
                    · <?= date('d.m.Y', strtotime($selectedDate)) ?>
                    в <strong id="confirm-time">—</strong>
                    · <?= number_format((float)$selectedTest['price'], 0, '.', ' ') ?> ₽
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
