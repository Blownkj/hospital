<?php use App\Core\View; ?>
<?php $isLab = ($appt['appointment_type'] ?? '') === 'lab_test'; ?>
<div class="appt-row">
    <div class="appt-info">
        <div class="appt-doctor">
            <?= $isLab ? '🧪 ' : '' ?>
            <?= View::e($isLab ? ($appt['lab_test_name'] ?? 'Анализ') : $appt['doctor_name']) ?>
            <span>— <?= View::e($appt['specialization']) ?></span>
        </div>
        <div class="appt-time">
            📅 <?= date('d.m.Y', strtotime($appt['scheduled_at'])) ?>
            &nbsp; 🕐 <?= date('H:i', strtotime($appt['scheduled_at'])) ?>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <span class="badge badge-<?= View::e($statusCls ?? '') ?>"><?= View::e($statusLabel ?? '') ?></span>
        <?php if (!empty($canCancel)): ?>
            <form method="POST" action="<?= BASE_URL ?>/patient/appointments/cancel"
                  onsubmit="return confirm('Отменить запись?')">
                <input type="hidden" name="csrf_token"
                       value="<?= View::e(\App\Core\Session::generateCsrfToken()) ?>">
                <input type="hidden" name="appointment_id" value="<?= (int)$appt['id'] ?>">
                <button type="submit" class="btn btn-danger">Отменить</button>
            </form>
        <?php endif; ?>
    </div>
</div>
