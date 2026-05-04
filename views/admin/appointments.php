<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';

$statusLabels = [
    'pending'     => ['⏳ Ожидает',     'pending'],
    'confirmed'   => ['✓ Подтверждена', 'confirmed'],
    'in_progress' => ['▶ Идёт приём',   'in-progress'],
    'completed'   => ['✅ Завершена',    'completed'],
    'cancelled'   => ['✕ Отменена',     'cancelled'],
];
?>

<a href="<?= BASE_URL ?>/admin/dashboard" class="back-link">← Дашборд</a>

<?php if ($flash): ?><div class="alert alert-success">✅ <?= View::e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error">⚠️ <?= View::e($error) ?></div><?php endif; ?>


<div class="page-header">
    <h1 class="page-title">Все записи</h1>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <form method="GET" action="<?= BASE_URL ?>/admin/appointments/export"
              style="display:flex;gap:6px;align-items:center">
            <input type="date" name="from" class="form-control"
                   style="width:140px" value="">
            <input type="date" name="to"   class="form-control"
                   style="width:140px" value="">
            <button type="submit" class="btn btn-primary btn-sm">
                ⬇ Скачать CSV
            </button>
        </form>
    </div>
</div>

<!-- Фильтры -->
<div class="card" style="margin-bottom:16px">
    <form method="GET" action="<?= BASE_URL ?>/admin/appointments"
          style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:1;min-width:160px">
            <label style="font-size:12px">Статус</label>
            <select name="status" style="padding:8px 10px;border:1px solid #dde0e8;border-radius:8px;font-size:13px;width:100%">
                <option value="">Все статусы</option>
                <?php foreach ($statusLabels as $val => [$lbl]) : ?>
                <option value="<?= $val ?>" <?= $status === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:160px">
            <label style="font-size:12px">Дата</label>
            <input type="date" name="date" value="<?= View::e($date) ?>"
                   style="padding:8px 10px;border:1px solid #dde0e8;border-radius:8px;font-size:13px;width:100%">
        </div>
        <button type="submit" class="btn btn-primary" style="padding:9px 18px;font-size:13px">Применить</button>
        <a href="<?= BASE_URL ?>/admin/appointments" class="btn-secondary" style="padding:9px 18px;font-size:13px;border-radius:8px;text-decoration:none;display:inline-block">Сбросить</a>
    </form>
</div>

<!-- Таблица -->
<div class="card" style="padding:0;overflow:hidden">
    <?php if (empty($appointments)): ?>
        <p class="text-muted text-center" style="padding:32px">Записей не найдено.</p>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead>
            <tr style="border-bottom:1px solid var(--color-border-secondary)">
                <th style="padding:12px 16px;text-align:left;font-weight:500">Пациент</th>
                <th style="padding:12px 16px;text-align:left;font-weight:500">Врач / услуга</th>
                <th style="padding:12px 16px;text-align:left;font-weight:500">Дата и время</th>
                <th style="padding:12px 16px;text-align:left;font-weight:500">Статус</th>
                <th style="padding:12px 16px;text-align:left;font-weight:500">Действия</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($appointments as $a):
            [$lbl, $cls] = $statusLabels[$a['status']] ?? ['—', 'pending'];
        ?>
        <tr style="border-bottom:0.5px solid var(--color-border-tertiary)">
            <td style="padding:11px 16px">
                <?= View::e($a['patient_name']) ?>
                <?php if ($a['patient_phone']): ?>
                <div class="text-muted" style="font-size:11px"><?= View::e($a['patient_phone']) ?></div>
                <?php endif; ?>
            </td>
            <td style="padding:11px 16px">
                <?= View::e($a['doctor_name'] ?? 'Лаборатория') ?>
                <?php if ($a['specialization']): ?>
                <div class="text-muted" style="font-size:11px"><?= View::e($a['specialization']) ?></div>
                <?php endif; ?>
            </td>
            <td style="padding:11px 16px"><?= date('d.m.Y H:i', strtotime($a['scheduled_at'])) ?></td>
            <td style="padding:11px 16px"><span class="badge badge-<?= $cls ?>"><?= $lbl ?></span></td>
            <td style="padding:11px 16px">
                <div style="display:flex;gap:6px;flex-wrap:wrap">
                <?php if ($a['status'] === 'pending'): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/appointment/<?= (int)$a['id'] ?>/confirm">
                        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                        <button class="btn btn-primary" style="padding:4px 10px;font-size:11px">✓</button>
                    </form>
                <?php endif; ?>
                <?php if (in_array($a['status'], ['pending','confirmed'])): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/appointment/<?= (int)$a['id'] ?>/cancel"
                          onsubmit="return confirm('Отменить запись?')">
                        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                        <button class="btn btn-danger" style="padding:4px 10px;font-size:11px">✕</button>
                    </form>
                    <!-- Перенос -->
                    <button class="btn-secondary" style="padding:4px 10px;font-size:11px;border-radius:6px;cursor:pointer"
                        onclick="toggleReschedule(<?= (int)$a['id'] ?>)">⇄</button>
                    <div id="rs-<?= (int)$a['id'] ?>" style="display:none;margin-top:6px;width:100%">
                        <form method="POST" action="<?= BASE_URL ?>/admin/appointment/<?= (int)$a['id'] ?>/reschedule"
                              style="display:flex;gap:6px;align-items:center">
                            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                            <input type="datetime-local" name="new_datetime" required
                                   style="padding:5px 8px;border:1px solid #dde0e8;border-radius:6px;font-size:12px">
                            <button class="btn btn-primary" style="padding:5px 10px;font-size:11px">OK</button>
                        </form>
                    </div>
                <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<script>
function toggleReschedule(id) {
    const el = document.getElementById('rs-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>