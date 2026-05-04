<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';

$isActive = $appt['status'] === 'in_progress';
$isDone   = $appt['status'] === 'completed';
$age = (int) date('Y') - (int) substr($appt['patient_birth_date'], 0, 4);
$genderMap = ['m' => 'Мужской', 'f' => 'Женский', 'other' => 'Другой'];
?>

<a href="<?= BASE_URL ?>/doctor/dashboard" class="back-link">← Список приёмов</a>

<?php include ROOT_PATH . '/views/partials/flash.php'; ?>

<!-- Шапка -->
<div class="page-header">
    <div>
        <h1 class="page-title"><?= View::e($appt['patient_name']) ?></h1>
        <p class="text-muted">
            <?= $age ?> лет · <?= View::e($genderMap[$appt['gender']] ?? '—') ?> ·
            <?= date('d.m.Y H:i', strtotime($appt['scheduled_at'])) ?>
        </p>
    </div>
    <?php if (!$isActive && !$isDone): ?>
    <form method="POST" action="<?= BASE_URL ?>/doctor/appointment/<?= (int)$appt['id'] ?>/start">
        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
        <button class="btn btn-primary" style="padding:10px 24px">▶ Начать приём</button>
    </form>
    <?php elseif ($isDone): ?>
        <span class="badge badge-completed" style="font-size:14px;padding:8px 16px">✅ Завершён</span>
    <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

<!-- Левая колонка: данные пациента + хронические + история -->
<div>
    <!-- Карточка пациента -->
    <div class="card">
        <div class="card-title">👤 Данные пациента</div>
        <table class="info-table">
            <tr><td class="text-muted" style="width:40%">Email</td>
                <td><?= View::e($appt['patient_email']) ?></td></tr>
            <tr><td class="text-muted">Телефон</td>
                <td><?= View::e($appt['patient_phone'] ?: '—') ?></td></tr>
            <tr><td class="text-muted">Дата рождения</td>
                <td><?= date('d.m.Y', strtotime($appt['patient_birth_date'])) ?> (<?= $age ?> лет)</td></tr>
            <tr><td class="text-muted">Пол</td>
                <td><?= View::e($genderMap[$appt['gender']] ?? '—') ?></td></tr>
            <tr><td class="text-muted">Адрес</td>
                <td><?= View::e($appt['address'] ?: '—') ?></td></tr>
        </table>
    </div>

    <!-- Хронические заболевания -->
    <?php if ($appt['chronic_diseases']): ?>
    <div class="alert alert-warning" style="margin-bottom:16px">
        <strong>⚠ Хронические заболевания:</strong><br>
        <?= View::e($appt['chronic_diseases']) ?>
    </div>
    <?php endif; ?>

    <!-- История предыдущих приёмов -->
    <div class="card">
        <div class="card-title">📋 История приёмов</div>
        <?php if (empty($history)): ?>
            <p class="text-muted" style="font-size:13px">Первый визит.</p>
        <?php else: ?>
            <?php foreach ($history as $h): ?>
            <div style="padding:10px 0;border-bottom:1px solid var(--border);font-size:13px">
                <div style="font-weight:500"><?= View::e($h['doctor_name']) ?>
                    <span class="text-muted" style="font-weight:400"> — <?= View::e($h['specialization']) ?></span>
                </div>
                <div class="text-muted"><?= date('d.m.Y', strtotime($h['scheduled_at'])) ?></div>
                <?php if ($h['diagnosis']): ?>
                <div style="margin-top:3px">Диагноз: <?= View::e($h['diagnosis']) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Правая колонка: протокол + назначения -->
<div>
    <!-- Протокол -->
    <div class="card">
        <div class="card-title">📝 Протокол приёма</div>

        <?php if ($isDone): ?>
            <!-- Только просмотр для завершённых -->
            <div style="font-size:13px">
                <p><strong>Жалобы:</strong><br><?= View::e($visit['complaints'] ?? '—') ?></p>
                <p style="margin-top:10px"><strong>Осмотр:</strong><br><?= View::e($visit['examination'] ?? '—') ?></p>
                <p style="margin-top:10px"><strong>Диагноз:</strong><br><?= View::e($visit['diagnosis'] ?? '—') ?></p>
            </div>
        <?php elseif ($isActive): ?>
        <form method="POST" action="<?= BASE_URL ?>/doctor/appointment/<?= (int)$appt['id'] ?>/protocol">
            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
            <div class="form-group">
                <label>Жалобы пациента</label>
                <textarea name="complaints" rows="3" placeholder="Что беспокоит пациента..."><?= View::e($visit['complaints'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Осмотр</label>
                <textarea name="examination" rows="3" placeholder="Данные осмотра, анализов..."><?= View::e($visit['examination'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Диагноз</label>
                <textarea name="diagnosis" rows="2" placeholder="Установленный диагноз..."><?= View::e($visit['diagnosis'] ?? '') ?></textarea>
            </div>
            <div class="flex-row" style="flex-wrap:wrap;gap:10px">
                <button type="submit" class="btn btn-secondary" style="flex:1">💾 Сохранить</button>
                <button type="submit" name="finish" value="1" class="btn btn-primary" style="flex:1"
                    onclick="return confirm('Завершить приём и сохранить протокол?')">
                    ✅ Завершить приём
                </button>
            </div>
        </form>
        <?php else: ?>
            <p class="text-muted" style="font-size:13px">Сначала начните приём.</p>
        <?php endif; ?>
    </div>

    <!-- Назначения -->
    <div class="card">
        <div class="card-title">💊 Назначения</div>

        <?php if (empty($prescriptions)): ?>
            <p class="text-muted" style="font-size:13px;margin-bottom:14px">Назначений нет.</p>
        <?php else: ?>
            <?php
            $typeLabels = ['drug' => '💊 Препарат', 'procedure' => '🔧 Процедура', 'referral' => '📄 Направление'];
            foreach ($prescriptions as $pr): ?>
            <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:8px 0;border-bottom:1px solid var(--border);gap:8px">
                <div style="font-size:13px">
                    <div>
                        <span class="badge badge-pending" style="font-size:11px;margin-right:6px"><?= $typeLabels[$pr['type']] ?? $pr['type'] ?></span>
                        <strong><?= View::e($pr['name']) ?></strong>
                        <?php if ($pr['dosage']): ?><span class="text-muted"> — <?= View::e($pr['dosage']) ?></span><?php endif; ?>
                    </div>
                    <?php if ($pr['notes']): ?>
                        <div class="text-muted" style="margin-top:3px"><?= View::e($pr['notes']) ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($isActive): ?>
                <form method="POST" action="<?= BASE_URL ?>/doctor/appointment/<?= (int)$appt['id'] ?>/prescription/delete">
                    <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                    <input type="hidden" name="prescription_id" value="<?= (int)$pr['id'] ?>">
                    <button type="submit" class="btn btn-danger" style="padding:3px 8px;font-size:11px"
                        onclick="return confirm('Удалить назначение?')">✕</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($isActive): ?>
        <form method="POST" action="<?= BASE_URL ?>/doctor/appointment/<?= (int)$appt['id'] ?>/prescription/add" style="margin-top:14px">
            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px">
                <select name="type" required style="padding:8px 10px;border:1px solid #dde0e8;border-radius:8px;font-size:13px">
                    <option value="">Тип</option>
                    <option value="drug">💊 Препарат</option>
                    <option value="procedure">🔧 Процедура</option>
                    <option value="referral">📄 Направление</option>
                </select>
                <input type="text" name="name" placeholder="Название *" required style="padding:8px 10px;border:1px solid #dde0e8;border-radius:8px;font-size:13px">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px">
                <input type="text" name="dosage" placeholder="Доза / срок" style="padding:8px 10px;border:1px solid #dde0e8;border-radius:8px;font-size:13px">
                <input type="text" name="notes" placeholder="Примечания" style="padding:8px 10px;border:1px solid #dde0e8;border-radius:8px;font-size:13px">
            </div>
            <button type="submit" class="btn btn-secondary btn-block" style="font-size:13px">+ Добавить назначение</button>
        </form>
        <?php endif; ?>
    </div>
</div><!-- /right -->
</div><!-- /grid -->

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>