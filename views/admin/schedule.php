<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';

$dayNames = [1=>'Понедельник',2=>'Вторник',3=>'Среда',4=>'Четверг',5=>'Пятница',6=>'Суббота',7=>'Воскресенье'];
?>

<a href="<?= BASE_URL ?>/admin/dashboard" class="back-link">← Дашборд</a>

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
    <h1 class="page-title">Расписание врача</h1>
</div>

<!-- Выбор врача -->
<div class="card u-mb-4">
    <div class="card__body">
        <form method="GET" action="<?= BASE_URL ?>/admin/schedule"
              class="u-flex u-gap-3 u-ai-center">
            <label class="form__label u-m-0 u-nowrap">Врач:</label>
            <select class="form__control u-flex-1 u-w-360" name="doctor_id" onchange="this.form.submit()">
                <?php foreach ($doctors as $doc): ?>
                <option value="<?= (int)$doc['id'] ?>" <?= $doc['id'] == $selectedId ? 'selected' : '' ?>>
                    <?= View::e($doc['full_name']) ?> — <?= View::e($doc['specialization']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- Форма расписания -->
<?php if ($selectedId): ?>
<form method="POST" action="<?= BASE_URL ?>/admin/schedule/<?= $selectedId ?>/save">
    <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">

    <div class="card">
        <div class="card__body">
            <h2 class="card__title u-mb-4">Рабочие дни</h2>
            <div class="table-wrap">
                <table class="table table--compact">
                    <thead>
                        <tr>
                            <th class="td-w160">День</th>
                            <th>Рабочий</th>
                            <th>Начало</th>
                            <th>Конец</th>
                            <th>Слот (мин)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php for ($dow = 1; $dow <= 7; $dow++):
                        $row = $scheduleByDay[$dow] ?? null;
                        $active = $row !== null;
                    ?>
                    <tr>
                        <td class="u-fw-medium"><?= $dayNames[$dow] ?></td>
                        <td>
                            <input type="checkbox" name="days[<?= $dow ?>][active]" value="1"
                                   <?= $active ? 'checked' : '' ?>
                                   class="sched-checkbox">
                        </td>
                        <td>
                            <input class="form__control u-w-110" type="time" name="days[<?= $dow ?>][start]"
                                   value="<?= View::e($row['start_time'] ?? '09:00') ?>">
                        </td>
                        <td>
                            <input class="form__control u-w-110" type="time" name="days[<?= $dow ?>][end]"
                                   value="<?= View::e($row['end_time'] ?? '18:00') ?>">
                        </td>
                        <td>
                            <select class="form__control sched-slot" name="days[<?= $dow ?>][slot]">
                                <?php foreach ([15, 20, 30, 45, 60] as $m): ?>
                                <option value="<?= $m ?>"
                                    <?= (int)($row['slot_duration_min'] ?? 30) === $m ? 'selected' : '' ?>>
                                    <?= $m ?> мин
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="u-mt-5">
                <button type="submit" class="btn btn--primary">
                    <?php icon('save', 16) ?> Сохранить расписание
                </button>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
