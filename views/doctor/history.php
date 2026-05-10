<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<div class="page-header u-mb-6">
    <h1 class="page-title">История приёмов</h1>
    <a href="<?= BASE_URL ?>/doctor/dashboard" class="btn btn--secondary btn--sm">← Дашборд</a>
</div>

<?php if (empty($history)): ?>
    <div class="empty-state">
        <?php icon('clipboard-list', 48) ?>
        <p class="empty-state__text">Завершённых приёмов пока нет.</p>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card__body u-p-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Дата и время</th>
                        <th>Пациент</th>
                        <th>Диагноз</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $row): ?>
                        <tr>
                            <td class="u-text-nowrap">
                                <?= View::e(date('d.m.Y H:i', strtotime($row['scheduled_at']))) ?>
                            </td>
                            <td><?= View::e($row['patient_name']) ?></td>
                            <td><?= $row['diagnosis'] ? View::e($row['diagnosis']) : '<span class="u-text-muted">—</span>' ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/doctor/appointment/<?= (int)$row['id'] ?>"
                                   class="btn btn--ghost btn--sm">Открыть</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/footer.php'; ?>
