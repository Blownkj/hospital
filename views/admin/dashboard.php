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
        ['Ожидают подтв.',   $stats['pending_count'],        'clock'],
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
            <canvas id="chartLine" height="100"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card__body">
            <h2 class="card__title u-mb-4">
                <?php icon('star', 18) ?> Топ врачей
            </h2>
            <canvas id="chartBar" height="160"></canvas>
        </div>
    </div>
</div>

<!-- Ожидают подтверждения -->
<div class="card u-mb-6">
    <div class="card__body">
        <div class="section-header u-mb-4">
            <h2 class="card__title u-m-0">
                <?php icon('clock', 18) ?> Ожидают подтверждения
                <span class="badge badge--warning u-ms-1"><?= count($pending) ?></span>
            </h2>
            <a href="<?= BASE_URL ?>/admin/appointments" class="u-text-sm u-text-primary">Все записи →</a>
        </div>

        <?php if (empty($pending)): ?>
            <p class="u-text-muted u-text-center u-p-6">Новых заявок нет.</p>
        <?php else: ?>
            <?php foreach (array_slice($pending, 0, 5) as $a): ?>
            <div class="appt-row">
                <div class="appt-info">
                    <div class="appt-doctor">
                        <?= View::e($a['patient_name']) ?>
                        <span>→ <?= View::e($a['doctor_name'] ?? 'Анализ') ?></span>
                    </div>
                    <div class="appt-time">
                        <?php icon('calendar', 13) ?>
                        <?= date('d.m.Y H:i', strtotime($a['scheduled_at'])) ?>
                        <?php if ($a['patient_phone']): ?>
                        · <?= View::e($a['patient_phone']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="u-flex u-gap-2">
                    <form method="POST" action="<?= BASE_URL ?>/admin/appointment/<?= (int)$a['id'] ?>/confirm">
                        <input type="hidden" name="csrf_token" value="<?= View::e(Session::generateCsrfToken()) ?>">
                        <button class="btn btn--primary btn--sm">
                            <?php icon('check', 14) ?> Подтвердить
                        </button>
                    </form>
                    <form method="POST" action="<?= BASE_URL ?>/admin/appointment/<?= (int)$a['id'] ?>/cancel">
                        <input type="hidden" name="csrf_token" value="<?= View::e(Session::generateCsrfToken()) ?>">
                        <button class="btn btn--danger btn--sm">
                            <?php icon('x', 14) ?> Отклонить
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const dayLabels    = <?= json_encode(array_column($byDay, 'day')) ?>;
const dayCounts    = <?= json_encode(array_map('intval', array_column($byDay, 'cnt'))) ?>;
const doctorLabels = <?= json_encode(array_column($topDoctors, 'full_name')) ?>;
const doctorCounts = <?= json_encode(array_map('intval', array_column($topDoctors, 'cnt'))) ?>;

const fmtLabels = dayLabels.map(d => { const [,m,day] = d.split('-'); return day + '.' + m; });

new Chart(document.getElementById('chartLine').getContext('2d'), {
    type: 'line',
    data: {
        labels: fmtLabels,
        datasets: [{
            label: 'Записей', data: dayCounts,
            borderColor: '#14b8a6', backgroundColor: 'rgba(20,184,166,0.08)',
            borderWidth: 2, tension: 0.3, fill: true, pointRadius: 3,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
    }
});

new Chart(document.getElementById('chartBar').getContext('2d'), {
    type: 'bar',
    data: {
        labels: doctorLabels.map(n => n.split(' ').slice(0,2).join(' ')),
        datasets: [{
            label: 'Приёмов', data: doctorCounts,
            backgroundColor: ['#14b8a6','#0d9488','#0f766e','#f59e0b','#6366f1'],
            borderRadius: 4,
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } }, y: { grid: { display: false } } }
    }
});
</script>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
