<?php
/** Expects: $statusCls (pending|confirmed|in-progress|completed|cancelled), $statusLabel */
use App\Core\View;

$modifierMap = [
    'pending'     => 'badge--warning',
    'confirmed'   => 'badge--success',
    'in-progress' => 'badge--info',
    'in_progress' => 'badge--info',
    'completed'   => 'badge--success',
    'cancelled'   => 'badge--danger',
];
$modifier = $modifierMap[$statusCls ?? ''] ?? 'badge--neutral';
?>
<span class="badge <?= $modifier ?>">
    <span class="badge__dot" aria-hidden="true"></span>
    <?= View::e($statusLabel ?? '') ?>
</span>
