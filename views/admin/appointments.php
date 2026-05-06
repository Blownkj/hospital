<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';

$statusLabels = [
    'pending'     => ['Ожидает',     'pending'],
    'confirmed'   => ['Подтверждена','confirmed'],
    'in_progress' => ['Идёт приём',  'in-progress'],
    'completed'   => ['Завершена',   'completed'],
    'cancelled'   => ['Отменена',    'cancelled'],
];
?>

<a href="<?= BASE_URL ?>/admin/dashboard" class="back-link">← Дашборд</a>

<?php include ROOT_PATH . '/views/partials/flash.php'; ?>

<div class="page-header">
    <h1 class="page-title">Все записи</h1>
    <div class="u-flex u-gap-2 u-ai-center u-flex-wrap">
        <form method="GET" action="<?= BASE_URL ?>/admin/appointments/export"
              class="u-flex u-gap-2 u-ai-center">
            <input type="date" name="from" class="form__control u-w-140" value="">
            <input type="date" name="to"   class="form__control u-w-140" value="">
            <button type="submit" class="btn btn--secondary btn--sm">
                <?php icon('trending-up', 14) ?> Скачать CSV
            </button>
        </form>
    </div>
</div>

<!-- Фильтры -->
<div class="card u-mb-4">
    <div class="card__body">
        <form method="GET" action="<?= BASE_URL ?>/admin/appointments"
              class="form-row">
            <div class="form__group u-m-0 u-flex-1 filter-group">
                <label class="form__label" for="filter-status">Статус</label>
                <select class="form__control" id="filter-status" name="status">
                    <option value="">Все статусы</option>
                    <?php foreach ($statusLabels as $val => [$lbl]): ?>
                    <option value="<?= $val ?>" <?= $status === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__group u-m-0 u-flex-1 filter-group">
                <label class="form__label" for="filter-date">Дата</label>
                <input class="form__control" type="date" id="filter-date" name="date"
                       value="<?= View::e($date) ?>">
            </div>
            <button type="submit" class="btn btn--primary btn--sm">Применить</button>
            <a href="<?= BASE_URL ?>/admin/appointments" class="btn btn--ghost btn--sm">Сбросить</a>
        </form>
    </div>
</div>

<!-- Таблица -->
<div class="card card--flush u-mb-4">
    <?php if (empty($appointments)): ?>
        <p class="u-text-muted u-text-center u-p-8">Записей не найдено.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Пациент</th>
                    <th>Врач / услуга</th>
                    <th>Дата и время</th>
                    <th>Статус</th>
                    <th class="td-right">Действия</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($appointments as $a):
                [$statusLabel, $statusCls] = $statusLabels[$a['status']] ?? ['—', 'pending'];
            ?>
            <tr>
                <td>
                    <div class="u-fw-medium"><?= View::e($a['patient_name']) ?></div>
                    <?php if ($a['patient_phone']): ?>
                    <div class="u-text-xs u-text-muted"><?= View::e($a['patient_phone']) ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <?= View::e($a['doctor_name'] ?? 'Лаборатория') ?>
                    <?php if ($a['specialization']): ?>
                    <div class="u-text-xs u-text-muted"><?= View::e($a['specialization']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="u-nowrap"><?= date('d.m.Y H:i', strtotime($a['scheduled_at'])) ?></td>
                <td><?php include ROOT_PATH . '/views/partials/status-badge.php'; ?></td>
                <td class="td-right">
                    <div class="u-flex u-gap-1 u-flex-wrap u-jc-end">
                    <?php if ($a['status'] === 'pending'): ?>
                        <form method="POST" action="<?= BASE_URL ?>/admin/appointment/<?= (int)$a['id'] ?>/confirm">
                            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                            <button class="btn btn--primary btn--sm" title="Подтвердить">
                                <?php icon('check', 13) ?>
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if (in_array($a['status'], ['pending','confirmed'])): ?>
                        <form method="POST" action="<?= BASE_URL ?>/admin/appointment/<?= (int)$a['id'] ?>/cancel"
                              onsubmit="return confirm('Отменить запись?')">
                            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                            <button class="btn btn--danger btn--sm" title="Отменить">
                                <?php icon('x', 13) ?>
                            </button>
                        </form>
                        <button class="btn btn--ghost btn--sm" title="Перенести"
                                onclick="toggleReschedule(<?= (int)$a['id'] ?>)">
                            <?php icon('calendar', 13) ?>
                        </button>
                        <div id="rs-<?= (int)$a['id'] ?>" class="u-hidden rs-form">
                            <form method="POST" action="<?= BASE_URL ?>/admin/appointment/<?= (int)$a['id'] ?>/reschedule"
                                  class="u-flex u-gap-2 u-ai-center">
                                <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                                <input class="form__control u-text-xs" type="datetime-local" name="new_datetime" required>
                                <button class="btn btn--primary btn--sm">OK</button>
                            </form>
                        </div>
                    <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php if ($paginator->totalPages > 1):
    $qs = static fn(int $p): string =>
        http_build_query(['status' => $status, 'date' => $date, 'page' => $p]);
?>
<div class="u-flex u-ai-center u-jc-between u-flex-wrap u-gap-3">
    <span class="u-text-sm u-text-muted">
        Всего: <?= $paginator->total ?> &nbsp;|&nbsp;
        Страница <?= $paginator->currentPage ?> из <?= $paginator->totalPages ?>
    </span>
    <nav class="pagination" aria-label="Страницы">
        <?php if ($paginator->hasPrev()): ?>
            <a href="?<?= $qs($paginator->prevPage()) ?>" class="pagination__item">←</a>
        <?php else: ?>
            <span class="pagination__item pagination__item--disabled">←</span>
        <?php endif; ?>

        <?php foreach ($paginator->pages() as $p): ?>
            <?php if ($p === $paginator->currentPage): ?>
                <span class="pagination__item pagination__item--current"><?= $p ?></span>
            <?php else: ?>
                <a href="?<?= $qs($p) ?>" class="pagination__item"><?= $p ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($paginator->hasNext()): ?>
            <a href="?<?= $qs($paginator->nextPage()) ?>" class="pagination__item">→</a>
        <?php else: ?>
            <span class="pagination__item pagination__item--disabled">→</span>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>

<script>
function toggleReschedule(id) {
    const el = document.getElementById('rs-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
