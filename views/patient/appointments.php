<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';

$statusMap = [
    'pending'     => ['Ожидает',     'pending'],
    'confirmed'   => ['Подтверждена','confirmed'],
    'in_progress' => ['Идёт приём',  'in-progress'],
    'completed'   => ['Завершена',   'completed'],
    'cancelled'   => ['Отменена',    'cancelled'],
];
?>

<div class="page-header">
    <div>
        <a href="<?= BASE_URL ?>/patient/dashboard" class="back-link">← Личный кабинет</a>
        <h1 class="page-title">Мои записи</h1>
    </div>
    <a href="<?= BASE_URL ?>/patient/book" class="btn btn--primary">+ Записаться</a>
</div>

<?php include ROOT_PATH . '/views/partials/flash.php'; ?>

<?php if (empty($appointments)): ?>
    <?php
    $emptyMessage = 'У вас пока нет записей';
    $emptyLinkUrl = BASE_URL . '/patient/book';
    $emptyLinkText = 'Записаться к врачу';
    include ROOT_PATH . '/views/partials/empty-state.php';
    ?>
<?php else: ?>
    <div class="card u-p-0">
        <?php foreach ($appointments as $appt):
            [$statusLabel, $statusCls] = $statusMap[$appt['status']] ?? ['—', 'pending'];
            $isPast    = strtotime($appt['scheduled_at']) < time();
            $canCancel = in_array($appt['status'], ['pending','confirmed'], true) && !$isPast;
            include ROOT_PATH . '/views/partials/appointment-row.php';
        endforeach; ?>
    </div>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
