<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<a href="<?= BASE_URL ?>/patient/dashboard" class="back-link">← Личный кабинет</a>
<h1 class="page-title mb-3">Запись на анализ</h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= View::e($error) ?></div>
<?php endif; ?>

<!-- Выбор анализа -->
<div class="booking-step">
    <div class="booking-step-title">
        <?= $selectedTest ? '<span class="step-done">✅</span> Анализ выбран' : '1. Выберите анализ' ?>
    </div>

    <?php if (!$selectedTest): ?>
        <?php foreach ($grouped as $category => $tests): ?>
            <div style="margin-bottom:20px">
                <div style="font-size:13px;font-weight:600;color:#888;text-transform:uppercase;
                            letter-spacing:.05em;margin-bottom:10px">
                    <?= View::e($category) ?>
                </div>
                <div class="lab-grid">
                    <?php foreach ($tests as $test): ?>
                        <a href="<?= BASE_URL ?>/patient/book/analysis?test_id=<?= (int)$test['id'] ?>"
                           class="lab-card">
                            <div class="lab-name"><?= View::e($test['name']) ?></div>
                            <?php if ($test['preparation']): ?>
                                <div class="lab-prep">🔔 <?= View::e($test['preparation']) ?></div>
                            <?php endif; ?>
                            <div class="lab-price"><?= number_format((float)$test['price'], 0, '.', ' ') ?> ₽</div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <div>
                <div style="font-size:15px;font-weight:600"><?= View::e($selectedTest['name']) ?></div>
                <?php if ($selectedTest['preparation']): ?>
                    <div class="alert alert-warning mt-1" style="margin-bottom:0">
                        🔔 Подготовка: <?= View::e($selectedTest['preparation']) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div style="display:flex;align-items:center;gap:16px">
                <span style="font-size:16px;font-weight:700">
                    <?= number_format((float)$selectedTest['price'], 0, '.', ' ') ?> ₽
                </span>
                <a href="<?= BASE_URL ?>/patient/book/analysis"
                   style="font-size:13px;color:#dc2626">Сменить</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Выбор даты -->
<?php if ($selectedTest && !empty($availableDates)): ?>
<div class="booking-step">
    <div class="booking-step-title">
        <?= $selectedDate ? '<span class="step-done">✅</span>' : '2.' ?>
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
                <span style="opacity:.7"><?= $dow ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Выбор времени -->
<?php if ($selectedDate): ?>
<div class="booking-step">
    <div class="booking-step-title">
        3. Выберите время
        <span style="font-size:13px;font-weight:400;color:#888">
            — <?= date('d.m.Y', strtotime($selectedDate)) ?>
        </span>
    </div>

    <?php $available = array_filter($slots, fn($s) => $s['available']); ?>

    <?php if (empty($available)): ?>
        <p class="text-muted">Все слоты на этот день заняты. Выберите другую дату.</p>
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
                                data-time="<?= View::e($slot['time']) ?>"
                                onclick="selectSlot('<?= View::e($slot['time']) ?>', this)">
                            <?= View::e($slot['time']) ?>
                        </button>
                    <?php else: ?>
                        <span class="slot-taken"><?= View::e($slot['time']) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div id="confirm-block" style="display:none">
                <div class="confirm-block">
                    🧪 <strong><?= View::e($selectedTest['name']) ?></strong>
                    · <?= date('d.m.Y', strtotime($selectedDate)) ?>
                    в <strong id="confirm-time">—</strong>
                    · <?= number_format((float)$selectedTest['price'], 0, '.', ' ') ?> ₽
                </div>
                <button type="submit" class="btn btn-primary">
                    Подтвердить запись
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>