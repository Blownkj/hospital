<?php use App\Core\View; ?>
<?php $isLab = ($appt['appointment_type'] ?? '') === 'lab_test'; ?>
<?php
$modifierMap = [
    'pending'     => 'badge--warning',
    'confirmed'   => 'badge--success',
    'in-progress' => 'badge--info',
    'in_progress' => 'badge--info',
    'completed'   => 'badge--success',
    'cancelled'   => 'badge--danger',
];
$badgeModifier = $modifierMap[$statusCls ?? ''] ?? 'badge--neutral';
?>
<div class="appt-row">
    <div class="appt-info">
        <div class="appt-doctor">
            <?php if ($isLab): ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                     aria-label="Анализ" class="icon-inline--primary">
                    <path d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/>
                    <path d="M8.5 2h7"/><path d="M7 16h10"/>
                </svg>
            <?php else: ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true" class="icon-inline--primary">
                    <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6 6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/>
                    <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4"/>
                    <circle cx="18" cy="11.5" r="2.5"/>
                </svg>
            <?php endif; ?>
            <?= View::e($isLab ? ($appt['lab_test_name'] ?? 'Анализ') : $appt['doctor_name']) ?>
            <span>— <?= View::e($appt['specialization']) ?></span>
        </div>
        <div class="appt-time">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true" class="icon-inline">
                <path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/>
            </svg>
            <?= date('d.m.Y', strtotime($appt['scheduled_at'])) ?>
            &nbsp;
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true" class="icon-inline">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            <?= date('H:i', strtotime($appt['scheduled_at'])) ?>
        </div>
    </div>
    <div class="appt-actions">
        <span class="badge <?= $badgeModifier ?>">
            <span class="badge__dot" aria-hidden="true"></span>
            <?= View::e($statusLabel ?? '') ?>
        </span>
        <?php if (!empty($canCancel)): ?>
            <form method="POST" action="<?= BASE_URL ?>/patient/appointments/cancel"
                  onsubmit="return confirm('Отменить запись?')">
                <input type="hidden" name="csrf_token"
                       value="<?= View::e(\App\Core\Session::generateCsrfToken()) ?>">
                <input type="hidden" name="appointment_id" value="<?= (int)$appt['id'] ?>">
                <button type="submit" class="btn btn--danger btn--sm">Отменить</button>
            </form>
        <?php endif; ?>
    </div>
</div>
