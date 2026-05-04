<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<a href="<?= BASE_URL ?>/patient/dashboard" class="back-link">← Личный кабинет</a>
<h1 class="page-title mb-3">Запись к врачу</h1>

<?php include ROOT_PATH . '/views/partials/flash.php'; ?>

<!-- Шаг 1: Специализация -->
<div class="booking-step">
    <div class="booking-step-title">
        <?= $selectedDoctor ? '<span class="step-done">✅</span>' : '1.' ?>
        Выберите специализацию
    </div>

    <?php if (!$selectedDoctor): ?>
        <div class="spec-grid">
            <?php foreach ($specs as $spec): ?>
                <a href="<?= BASE_URL ?>/patient/book?spec_id=<?= (int)$spec['id'] ?>"
                   class="spec-card <?= ((int)($selectedSpec['id'] ?? 0) === (int)$spec['id']) ? 'active' : '' ?>">
                    <div class="spec-icon"><?= $specIcons[$spec['name']] ?? '👨‍⚕️' ?></div>
                    <div class="spec-name"><?= View::e($spec['name']) ?></div>
                    <div class="spec-count"><?= (int)$spec['count'] ?> врача(-ей)</div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="display:flex;align-items:center;gap:12px">
            <span style="font-size:28px"><?= $specIcons[$selectedDoctor['specialization']] ?? '👨‍⚕️' ?></span>
            <span style="font-weight:600"><?= View::e($selectedDoctor['specialization']) ?></span>
            <a href="<?= BASE_URL ?>/patient/book" style="margin-left:auto;font-size:13px;color:#dc2626">Сменить</a>
        </div>
    <?php endif; ?>
</div>

<!-- Шаг 2: Врач (если выбрана специализация, но не врач) -->
<?php if ($selectedSpec && !$selectedDoctor): ?>
<div class="booking-step">
    <div class="booking-step-title">2. Выберите врача</div>
    <div class="doctors-grid">
        <?php foreach ($filteredDoctors as $doctor): ?>
            <a href="<?= BASE_URL ?>/patient/book?doctor_id=<?= (int)$doctor['id'] ?>"
               class="doctor-card clickable">
                <div class="doctor-avatar"><?= View::e(View::initials($doctor['full_name'])) ?></div>
                <div class="doctor-name"><?= View::e($doctor['full_name']) ?></div>
                <div class="doctor-spec"><?= View::e($doctor['specialization']) ?></div>
                <div class="doctor-bio"><?= View::e(mb_strimwidth($doctor['bio'] ?? '', 0, 90, '...')) ?></div>
                <?php if ($doctor['avg_rating']): ?>
                    <div class="doctor-rating">
                        <span class="stars"><?= View::stars($doctor['avg_rating']) ?></span>
                        <span><?= View::e($doctor['avg_rating']) ?></span>
                    </div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Шаг 3: Дата -->
<?php if ($selectedDoctor): ?>
<div class="booking-step">
    <div class="booking-step-title">
        <?= $selectedDate ? '<span class="step-done">✅</span>' : '2.' ?>
        Врач: <?= View::e($selectedDoctor['full_name']) ?>
        <a href="<?= BASE_URL ?>/patient/book?spec_id=<?= (int)$selectedDoctor['specialization_id'] ?>"
           style="font-size:12px;color:#dc2626;font-weight:400;margin-left:8px">Сменить</a>
    </div>

    <?php if (empty($workingDays)): ?>
        <p class="text-muted">Нет доступных дат в ближайшие 2 месяца.</p>
    <?php else: ?>
        <div class="date-pills">
            <?php foreach ($workingDays as $day): ?>
                <?php
                    $dow = ['','Пн','Вт','Ср','Чт','Пт','Сб','Вс'][(int)date('N',strtotime($day))];
                ?>
                <a href="<?= BASE_URL ?>/patient/book?doctor_id=<?= (int)$selectedDoctor['id'] ?>&date=<?= $day ?>"
                   class="date-pill <?= $day === $selectedDate ? 'active' : '' ?>">
                    <?= date('d.m', strtotime($day)) ?>
                    <span style="opacity:.7"><?= $dow ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Шаг 4: Время -->
<?php if ($selectedDate): ?>
<div class="booking-step">
    <div class="booking-step-title">
        3. Выберите время
        <span style="font-size:13px;font-weight:400;color:#888">
            — <?= date('d.m.Y', strtotime($selectedDate)) ?>
        </span>
    </div>

    <?php
    $available = array_filter($slots, fn($s) => $s['available']);
    ?>

    <?php if (empty($slots)): ?>
        <p class="text-muted">В этот день врач не работает.</p>
    <?php elseif (empty($available)): ?>
        <p class="text-muted">Все слоты заняты — выберите другую дату.</p>
    <?php else: ?>
        <form method="POST" action="<?= BASE_URL ?>/patient/book">
            <input type="hidden" name="csrf_token"
                   value="<?= View::e(\App\Core\Session::generateCsrfToken()) ?>">
            <input type="hidden" name="doctor_id" value="<?= (int)$selectedDoctor['id'] ?>">
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

            <div id="confirm-block" style="display:none">
                <div class="confirm-block">
                    📅 <strong><?= date('d.m.Y', strtotime($selectedDate)) ?></strong>
                    в <strong id="confirm-time">—</strong>
                    · <?= View::e($selectedDoctor['full_name']) ?>
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