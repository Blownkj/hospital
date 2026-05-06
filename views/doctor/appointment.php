<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';

$isActive = $appt['status'] === 'in_progress';
$isDone   = $appt['status'] === 'completed';
$age = (int) date('Y') - (int) substr($appt['patient_birth_date'], 0, 4);
$genderMap = ['m' => 'Мужской', 'f' => 'Женский', 'other' => 'Другой'];
$typeLabels = ['drug' => 'Препарат', 'procedure' => 'Процедура', 'referral' => 'Направление'];
?>

<a href="<?= BASE_URL ?>/doctor/dashboard" class="back-link">← Список приёмов</a>

<?php include ROOT_PATH . '/views/partials/flash.php'; ?>

<!-- Шапка -->
<div class="page-header">
    <div>
        <h1 class="page-title"><?= View::e($appt['patient_name']) ?></h1>
        <p class="u-text-muted u-text-sm">
            <?= $age ?> лет · <?= View::e($genderMap[$appt['gender']] ?? '—') ?> ·
            <?= date('d.m.Y H:i', strtotime($appt['scheduled_at'])) ?>
        </p>
    </div>
    <?php if (!$isActive && !$isDone): ?>
    <form method="POST" action="<?= BASE_URL ?>/doctor/appointment/<?= (int)$appt['id'] ?>/start">
        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
        <button class="btn btn--primary">
            <?php icon('play-circle', 16) ?> Начать приём
        </button>
    </form>
    <?php elseif ($isDone): ?>
        <span class="badge badge--success badge--lg">
            <span class="badge__dot" aria-hidden="true"></span>
            Завершён
        </span>
    <?php endif; ?>
</div>

<div class="appt-detail-grid">

<!-- Левая колонка: данные пациента + хронические + история -->
<div>
    <!-- Карточка пациента -->
    <div class="card u-mb-4">
        <div class="card__body">
            <h2 class="card__title u-mb-4">
                <?php icon('user-round', 18) ?> Данные пациента
            </h2>
            <div class="table-wrap">
                <table class="table table--compact">
                    <tbody>
                        <tr>
                            <td class="td-muted u-w-110">Email</td>
                            <td><?= View::e($appt['patient_email']) ?></td>
                        </tr>
                        <tr>
                            <td class="td-muted">Телефон</td>
                            <td><?= View::e($appt['patient_phone'] ?: '—') ?></td>
                        </tr>
                        <tr>
                            <td class="td-muted">Дата рождения</td>
                            <td><?= date('d.m.Y', strtotime($appt['patient_birth_date'])) ?> (<?= $age ?> лет)</td>
                        </tr>
                        <tr>
                            <td class="td-muted">Пол</td>
                            <td><?= View::e($genderMap[$appt['gender']] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <td class="td-muted">Адрес</td>
                            <td><?= View::e($appt['address'] ?: '—') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Хронические заболевания -->
    <?php if ($appt['chronic_diseases']): ?>
    <div class="alert alert--warning u-mb-4" role="alert">
        <span class="alert__icon"><?php icon('alert-triangle', 18) ?></span>
        <span class="alert__body">
            <strong>Хронические заболевания:</strong><br>
            <?= View::e($appt['chronic_diseases']) ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- История предыдущих приёмов -->
    <div class="card">
        <div class="card__body">
            <h2 class="card__title u-mb-4">
                <?php icon('clipboard-list', 18) ?> История приёмов
            </h2>
            <?php if (empty($history)): ?>
                <p class="u-text-muted u-text-sm">Первый визит.</p>
            <?php else: ?>
                <?php foreach ($history as $h): ?>
                <div class="history-item">
                    <div class="history-item__title">
                        <?= View::e($h['doctor_name']) ?>
                        <span class="u-fw-normal u-text-muted"> — <?= View::e($h['specialization']) ?></span>
                    </div>
                    <div class="history-item__meta"><?= date('d.m.Y', strtotime($h['scheduled_at'])) ?></div>
                    <?php if ($h['diagnosis']): ?>
                    <div class="history-item__diag">Диагноз: <?= View::e($h['diagnosis']) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Правая колонка: протокол + назначения -->
<div>
    <!-- Протокол -->
    <div class="card u-mb-4">
        <div class="card__body">
            <h2 class="card__title u-mb-4">
                <?php icon('file-text', 18) ?> Протокол приёма
            </h2>

            <?php if ($isDone): ?>
                <div class="u-text-sm protocol-grid">
                    <div>
                        <div class="protocol-label">Жалобы</div>
                        <p><?= View::e($visit['complaints'] ?? '—') ?></p>
                    </div>
                    <div>
                        <div class="protocol-label">Осмотр</div>
                        <p><?= View::e($visit['examination'] ?? '—') ?></p>
                    </div>
                    <div>
                        <div class="protocol-label">Диагноз</div>
                        <p class="u-fw-medium"><?= View::e($visit['diagnosis'] ?? '—') ?></p>
                    </div>
                </div>
            <?php elseif ($isActive): ?>
            <form method="POST" action="<?= BASE_URL ?>/doctor/appointment/<?= (int)$appt['id'] ?>/protocol">
                <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                <div class="form__group">
                    <label class="form__label" for="complaints">Жалобы пациента</label>
                    <textarea class="form__control" id="complaints" name="complaints" rows="3"
                              placeholder="Что беспокоит пациента..."><?= View::e($visit['complaints'] ?? '') ?></textarea>
                </div>
                <div class="form__group">
                    <label class="form__label" for="examination">Осмотр</label>
                    <textarea class="form__control" id="examination" name="examination" rows="3"
                              placeholder="Данные осмотра, анализов..."><?= View::e($visit['examination'] ?? '') ?></textarea>
                </div>
                <div class="form__group">
                    <label class="form__label" for="diagnosis">Диагноз</label>
                    <textarea class="form__control" id="diagnosis" name="diagnosis" rows="2"
                              placeholder="Установленный диагноз..."><?= View::e($visit['diagnosis'] ?? '') ?></textarea>
                </div>
                <div class="form-actions u-flex-wrap">
                    <button type="submit" class="btn btn--secondary u-flex-1">
                        <?php icon('save', 16) ?> Сохранить
                    </button>
                    <button type="submit" name="finish" value="1" class="btn btn--primary u-flex-1"
                            onclick="return confirm('Завершить приём и сохранить протокол?')">
                        <?php icon('check-circle-2', 16) ?> Завершить приём
                    </button>
                </div>
            </form>
            <?php else: ?>
                <p class="u-text-muted u-text-sm">Сначала начните приём.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Назначения -->
    <div class="card">
        <div class="card__body">
            <h2 class="card__title u-mb-4">
                <?php icon('pill', 18) ?> Назначения
            </h2>

            <?php if (empty($prescriptions)): ?>
                <p class="u-text-muted u-text-sm u-mb-4">Назначений нет.</p>
            <?php else: ?>
                <?php foreach ($prescriptions as $pr): ?>
                <div class="rx-item">
                    <div class="rx-item__body">
                        <div class="rx-item__name">
                            <span class="badge badge--neutral"><?= View::e($typeLabels[$pr['type']] ?? $pr['type']) ?></span>
                            <strong><?= View::e($pr['name']) ?></strong>
                            <?php if ($pr['dosage']): ?>
                                <span class="rx-item__dose"> — <?= View::e($pr['dosage']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($pr['notes']): ?>
                            <div class="rx-item__note"><?= View::e($pr['notes']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if ($isActive): ?>
                    <form method="POST" action="<?= BASE_URL ?>/doctor/appointment/<?= (int)$appt['id'] ?>/prescription/delete">
                        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                        <input type="hidden" name="prescription_id" value="<?= (int)$pr['id'] ?>">
                        <button type="submit" class="btn btn--danger btn--sm"
                                onclick="return confirm('Удалить назначение?')" aria-label="Удалить">
                            <?php icon('x', 14) ?>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($isActive): ?>
            <form method="POST"
                  action="<?= BASE_URL ?>/doctor/appointment/<?= (int)$appt['id'] ?>/prescription/add"
                  class="u-mt-4">
                <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                <div class="form-grid-2--sm">
                    <select class="form__control" name="type" required>
                        <option value="">Тип</option>
                        <option value="drug">Препарат</option>
                        <option value="procedure">Процедура</option>
                        <option value="referral">Направление</option>
                    </select>
                    <input class="form__control" type="text" name="name" placeholder="Название *" required>
                </div>
                <div class="form-grid-2--sm-3">
                    <input class="form__control" type="text" name="dosage" placeholder="Доза / срок">
                    <input class="form__control" type="text" name="notes" placeholder="Примечания">
                </div>
                <button type="submit" class="btn btn--secondary btn--block">+ Добавить назначение</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div><!-- /right -->
</div><!-- /grid -->

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
