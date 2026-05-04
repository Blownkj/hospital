<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';

$statusMap = [
    'pending'     => ['⏳ Ожидает',     'pending'],
    'confirmed'   => ['✓ Подтверждена', 'confirmed'],
    'in_progress' => ['▶ Идёт приём',   'in-progress'],
    'completed'   => ['✅ Завершена',    'completed'],
    'cancelled'   => ['✗ Отменена',     'cancelled'],
];
?>

<div class="page-header">
    <div>
        <a href="<?= BASE_URL ?>/patient/dashboard" class="back-link">← Личный кабинет</a>
        <h1 class="page-title">Мои записи</h1>
    </div>
    <a href="<?= BASE_URL ?>/patient/book" class="btn btn-primary">+ Записаться</a>
</div>

<?php if ($flash): ?>
    <div class="alert alert-success">✅ <?= View::e($flash) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= View::e($error) ?></div>
<?php endif; ?>

<?php if (empty($appointments)): ?>
    <div class="card text-center" style="padding:48px">
        <div style="font-size:40px;margin-bottom:12px">📅</div>
        <p class="text-muted mb-2">У вас пока нет записей</p>
        <a href="<?= BASE_URL ?>/patient/book" class="btn btn-primary">Записаться к врачу</a>
    </div>
<?php else: ?>
    <div class="card" style="padding:0">
        <?php foreach ($appointments as $appt):
            [$label, $statusClass] = $statusMap[$appt['status']] ?? ['—','pending'];
            $isPast    = strtotime($appt['scheduled_at']) < time();
            $canCancel = in_array($appt['status'], ['pending','confirmed'], true) && !$isPast;
            $isLab     = $appt['appointment_type'] === 'lab_test';
        ?>
            <div class="appt-row">
                <div class="appt-info">
                    <div class="appt-doctor">
                        <?= $isLab ? '🧪 ' : '' ?>
                        <?= View::e($isLab ? ($appt['lab_test_name'] ?? 'Анализ') : $appt['doctor_name']) ?>
                        <span>
                            — <?= View::e($appt['specialization']) ?>
                        </span>
                    </div>
                    <div class="appt-time">
                        📅 <?= date('d.m.Y', strtotime($appt['scheduled_at'])) ?>
                        &nbsp; 🕐 <?= date('H:i',  strtotime($appt['scheduled_at'])) ?>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <span class="badge badge-<?= $statusClass ?>"><?= $label ?></span>

                    <?php if ($canCancel): ?>
                        <form method="POST"
                              action="<?= BASE_URL ?>/patient/appointments/cancel"
                              onsubmit="return confirm('Отменить запись?')">
                            <input type="hidden" name="csrf_token"
                                   value="<?= View::e(\App\Core\Session::generateCsrfToken()) ?>">
                            <input type="hidden" name="appointment_id" value="<?= (int)$appt['id'] ?>">
                            <button type="submit" class="btn btn-danger">Отменить</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>