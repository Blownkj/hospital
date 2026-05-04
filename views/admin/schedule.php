<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';

$dayNames = [1=>'Понедельник',2=>'Вторник',3=>'Среда',4=>'Четверг',5=>'Пятница',6=>'Суббота',7=>'Воскресенье'];
?>

<a href="<?= BASE_URL ?>/admin/dashboard" class="back-link">← Дашборд</a>

<?php if ($flash): ?><div class="alert alert-success">✅ <?= View::e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error">⚠️ <?= View::e($error) ?></div><?php endif; ?>

<div class="page-header">
    <h1 class="page-title">Расписание врача</h1>
</div>

<!-- Выбор врача -->
<div class="card" style="margin-bottom:16px">
    <form method="GET" action="<?= BASE_URL ?>/admin/schedule"
          style="display:flex;gap:10px;align-items:center">
        <label style="font-size:13px;color:var(--color-text-secondary)">Врач:</label>
        <select name="doctor_id" onchange="this.form.submit()"
                style="padding:8px 12px;border:1px solid #dde0e8;border-radius:8px;font-size:13px;flex:1;max-width:360px">
            <?php foreach ($doctors as $doc): ?>
            <option value="<?= (int)$doc['id'] ?>" <?= $doc['id'] == $selectedId ? 'selected' : '' ?>>
                <?= View::e($doc['full_name']) ?> — <?= View::e($doc['specialization']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- Форма расписания -->
<?php if ($selectedId): ?>
<form method="POST" action="<?= BASE_URL ?>/admin/schedule/<?= $selectedId ?>/save">
    <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">

    <div class="card">
        <div class="card-title">Рабочие дни</div>
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="border-bottom:1px solid var(--color-border-secondary)">
                    <th style="padding:10px 12px;text-align:left;font-weight:500;width:160px">День</th>
                    <th style="padding:10px 12px;text-align:left;font-weight:500">Рабочий</th>
                    <th style="padding:10px 12px;text-align:left;font-weight:500">Начало</th>
                    <th style="padding:10px 12px;text-align:left;font-weight:500">Конец</th>
                    <th style="padding:10px 12px;text-align:left;font-weight:500">Слот (мин)</th>
                </tr>
            </thead>
            <tbody>
            <?php for ($dow = 1; $dow <= 7; $dow++):
                $row = $scheduleByDay[$dow] ?? null;
                $active = $row !== null;
                $inp = 'style="padding:7px 10px;border:1px solid #dde0e8;border-radius:6px;font-size:13px;width:100px"';
            ?>
            <tr style="border-bottom:0.5px solid var(--color-border-tertiary)">
                <td style="padding:10px 12px;font-weight:500"><?= $dayNames[$dow] ?></td>
                <td style="padding:10px 12px">
                    <input type="checkbox" name="days[<?= $dow ?>][active]" value="1"
                           <?= $active ? 'checked' : '' ?>
                           style="width:16px;height:16px;cursor:pointer">
                </td>
                <td style="padding:10px 12px">
                    <input type="time" name="days[<?= $dow ?>][start]"
                           value="<?= View::e($row['start_time'] ?? '09:00') ?>" <?= $inp ?>>
                </td>
                <td style="padding:10px 12px">
                    <input type="time" name="days[<?= $dow ?>][end]"
                           value="<?= View::e($row['end_time'] ?? '18:00') ?>" <?= $inp ?>>
                </td>
                <td style="padding:10px 12px">
                    <select name="days[<?= $dow ?>][slot]"
                            style="padding:7px 10px;border:1px solid #dde0e8;border-radius:6px;font-size:13px">
                        <?php foreach ([15, 20, 30, 45, 60] as $m): ?>
                        <option value="<?= $m ?>" <?= (int)($row['slot_duration_min'] ?? 30) === $m ? 'selected' : '' ?>>
                            <?= $m ?> мин
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <?php endfor; ?>
            </tbody>
        </table>

        <div style="margin-top:16px">
            <button type="submit" class="btn btn-primary" style="padding:10px 24px">
                💾 Сохранить расписание
            </button>
        </div>
    </div>
</form>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>