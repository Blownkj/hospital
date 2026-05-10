<?php
use App\Core\View;
use App\Core\Session;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

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

<div class="page-header u-mb-6">
    <h1 class="page-title">Панель администратора</h1>
    <form method="POST" action="<?= BASE_URL ?>/logout" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= View::e(App\Core\Session::generateCsrfToken()) ?>">
        <button type="submit" class="btn btn--ghost btn--sm">Выйти</button>
    </form>
</div>

<!-- Статистика -->
<div class="stats-grid u-mb-8">
    <?php
    $metrics = [
        ['Пациентов',        $stats['total_patients'],       'users'],
        ['Всего записей',    $stats['total_appointments'],   'calendar'],
        ['Сегодня',          $stats['appointments_today'],   'calendar-check'],
        ['Завершено в мес.', $stats['completed_this_month'], 'check-circle-2'],
    ];
    foreach ($metrics as [$label, $val, $iconName]): ?>
    <div class="stat-card">
        <div class="stat-card__icon"><?php icon($iconName, 28) ?></div>
        <div class="stat-card__value"><?= (int)$val ?></div>
        <div class="stat-card__label"><?= $label ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Графики -->
<div class="charts-grid u-mb-6">
    <div class="card">
        <div class="card__body">
            <h2 class="card__title u-mb-4">
                <?php icon('trending-up', 18) ?> Записи за 14 дней
            </h2>
            <canvas id="chartLine" height="100"
                data-labels="<?= htmlspecialchars(json_encode(array_column($byDay, 'day')), ENT_QUOTES) ?>"
                data-counts="<?= htmlspecialchars(json_encode(array_map('intval', array_column($byDay, 'cnt'))), ENT_QUOTES) ?>"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card__body">
            <h2 class="card__title u-mb-4">
                <?php icon('star', 18) ?> Топ врачей
            </h2>
            <canvas id="chartBar" height="160"
                data-labels="<?= htmlspecialchars(json_encode(array_column($topDoctors, 'full_name')), ENT_QUOTES) ?>"
                data-counts="<?= htmlspecialchars(json_encode(array_map('intval', array_column($topDoctors, 'cnt'))), ENT_QUOTES) ?>"></canvas>
        </div>
    </div>
</div>

<!-- Навигация -->
<div class="dash-grid">
    <a href="<?= BASE_URL ?>/admin/appointments" class="dash-widget">
        <div class="dash-widget__icon"><?php icon('clipboard-list', 28) ?></div>
        <div class="dash-widget__label">Все записи</div>
        <div class="dash-widget__sub">Управление и фильтрация</div>
    </a>
    <a href="<?= BASE_URL ?>/admin/schedule" class="dash-widget">
        <div class="dash-widget__icon"><?php icon('calendar', 28) ?></div>
        <div class="dash-widget__label">Расписание</div>
        <div class="dash-widget__sub">Рабочие дни врачей</div>
    </a>
    <a href="<?= BASE_URL ?>/admin/reviews" class="dash-widget">
        <div class="dash-widget__icon"><?php icon('star', 28) ?></div>
        <div class="dash-widget__label">Отзывы</div>
        <div class="dash-widget__sub">Очередь модерации</div>
    </a>
    <a href="<?= BASE_URL ?>/admin/doctors" class="dash-widget">
        <div class="dash-widget__icon"><?php icon('stethoscope', 28) ?></div>
        <div class="dash-widget__label">Врачи</div>
        <div class="dash-widget__sub">Добавить и управлять</div>
    </a>
    <a href="<?= BASE_URL ?>/admin/services" class="dash-widget">
        <div class="dash-widget__icon"><?php icon('clipboard-list', 28) ?></div>
        <div class="dash-widget__label">Услуги</div>
        <div class="dash-widget__sub">Прайс-лист услуг</div>
    </a>
    <a href="<?= BASE_URL ?>/admin/lab-tests" class="dash-widget">
        <div class="dash-widget__icon"><?php icon('flask-conical', 28) ?></div>
        <div class="dash-widget__label">Анализы</div>
        <div class="dash-widget__sub">Прайс-лист анализов</div>
    </a>
</div>

<script src="<?= BASE_URL ?>/js/vendor/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>/js/admin-charts.js"></script>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
