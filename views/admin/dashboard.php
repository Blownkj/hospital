<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<?php if ($flash): ?><div class="alert alert-success">✅ <?= View::e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error">⚠️ <?= View::e($error) ?></div><?php endif; ?>

<div class="page-header">
    <h1 class="page-title">Панель администратора</h1>
    <a href="<?= BASE_URL ?>/logout" class="btn btn-danger" style="padding:8px 18px;font-size:13px">Выйти</a>
</div>

<!-- Статистика -->
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px">
    <?php
    $metrics = [
        ['Пациентов',        $stats['total_patients'],        '#4a90e2'],
        ['Всего записей',    $stats['total_appointments'],    '#7c3aed'],
        ['Сегодня',          $stats['appointments_today'],    '#059669'],
        ['Ожидают подтв.',   $stats['pending_count'],         '#d97706'],
        ['Завершено в мес.', $stats['completed_this_month'],  '#16a34a'],
    ];
    foreach ($metrics as [$label, $val, $color]): ?>
    <div style="background:var(--color-background-secondary);border-radius:var(--border-radius-md);padding:14px 16px;text-align:center">
        <div style="font-size:24px;font-weight:500;color:<?= $color ?>"><?= (int)$val ?></div>
        <div style="font-size:12px;color:var(--color-text-secondary);margin-top:3px"><?= $label ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Графики -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:20px">
    <div class="card">
        <div class="card-title">📈 Записи за 14 дней</div>
        <canvas id="chartLine" height="100"></canvas>
    </div>
    <div class="card">
        <div class="card-title">🏆 Топ врачей</div>
        <canvas id="chartBar" height="160"></canvas>
    </div>
</div>

<!-- Ожидают подтверждения -->
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
        <div class="card-title" style="margin:0">⏳ Ожидают подтверждения (<?= count($pending) ?>)</div>
        <a href="<?= BASE_URL ?>/admin/appointments" style="font-size:13px;color:#4a90e2">Все записи →</a>
    </div>

    <?php if (empty($pending)): ?>
        <p class="text-muted text-center" style="padding:16px 0">Новых заявок нет.</p>
    <?php else: ?>
        <?php foreach (array_slice($pending, 0, 5) as $a): ?>
        <div class="appt-row">
            <div class="appt-info">
                <div class="appt-doctor">
                    <?= View::e($a['patient_name']) ?>
                    <span class="text-muted" style="font-weight:400"> → <?= View::e($a['doctor_name'] ?? 'Анализ') ?></span>
                </div>
                <div class="appt-time">
                    <?= date('d.m.Y H:i', strtotime($a['scheduled_at'])) ?>
                    <?php if ($a['patient_phone']): ?>· <?= View::e($a['patient_phone']) ?><?php endif; ?>
                </div>
            </div>
            <div style="display:flex;gap:8px">
                <form method="POST" action="<?= BASE_URL ?>/admin/appointment/<?= (int)$a['id'] ?>/confirm">
                    <input type="hidden" name="csrf_token" value="<?= View::e(Session::generateCsrfToken()) ?>">
                    <button class="btn btn-primary" style="padding:5px 12px;font-size:12px">✓ Подтвердить</button>
                </form>
                <form method="POST" action="<?= BASE_URL ?>/admin/appointment/<?= (int)$a['id'] ?>/cancel">
                    <input type="hidden" name="csrf_token" value="<?= View::e(Session::generateCsrfToken()) ?>">
                    <button class="btn btn-danger" style="padding:5px 12px;font-size:12px">✕ Отклонить</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Навигация -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:20px">
    <a href="<?= BASE_URL ?>/admin/appointments" class="dash-widget">
        <div class="dash-widget-icon">📋</div>
        <div class="dash-widget-label">Все записи</div>
        <div class="dash-widget-sub">Управление и фильтрация</div>
    </a>
    <a href="<?= BASE_URL ?>/admin/schedule" class="dash-widget">
        <div class="dash-widget-icon">🗓</div>
        <div class="dash-widget-label">Расписание</div>
        <div class="dash-widget-sub">Рабочие дни врачей</div>
    </a>
    <a href="<?= BASE_URL ?>/admin/reviews" class="dash-widget">
        <div class="dash-widget-icon">⭐</div>
        <div class="dash-widget-label">Отзывы</div>
        <div class="dash-widget-sub">Очередь модерации</div>
    </a>
    <a href="<?= BASE_URL ?>/admin/doctors" class="dash-widget">
        <div class="dash-widget-icon">👨‍⚕️</div>
        <div class="dash-widget-label">Врачи</div>
        <div class="dash-widget-sub">Добавить и управлять</div>
    </a>
    <a href="<?= BASE_URL ?>/admin/services" class="dash-widget">
        <div class="dash-widget-icon">💊</div>
        <div class="dash-widget-label">Услуги</div>
        <div class="dash-widget-sub">Прайс-лист услуг</div>
    </a>
    <a href="<?= BASE_URL ?>/admin/lab-tests" class="dash-widget">
        <div class="dash-widget-icon">🧪</div>
        <div class="dash-widget-label">Анализы</div>
        <div class="dash-widget-sub">Прайс-лист анализов</div>
    </a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
// Данные из PHP
const dayLabels = <?= json_encode(array_column($byDay, 'day')) ?>;
const dayCounts = <?= json_encode(array_map('intval', array_column($byDay, 'cnt'))) ?>;
const doctorLabels = <?= json_encode(array_column($topDoctors, 'full_name')) ?>;
const doctorCounts = <?= json_encode(array_map('intval', array_column($topDoctors, 'cnt'))) ?>;

// Форматируем даты dd.mm
const fmtLabels = dayLabels.map(d => {
    const [y,m,day] = d.split('-');
    return day + '.' + m;
});

const lineCtx = document.getElementById('chartLine').getContext('2d');
new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: fmtLabels,
        datasets: [{
            label: 'Записей',
            data: dayCounts,
            borderColor: '#4a90e2',
            backgroundColor: 'rgba(74,144,226,0.08)',
            borderWidth: 2,
            tension: 0.3,
            fill: true,
            pointRadius: 3,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});

const barCtx = document.getElementById('chartBar').getContext('2d');
new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: doctorLabels.map(n => n.split(' ').slice(0,2).join(' ')),
        datasets: [{
            label: 'Приёмов',
            data: doctorCounts,
            backgroundColor: ['#4a90e2','#7c3aed','#059669','#d97706','#dc2626'],
            borderRadius: 4,
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, ticks: { stepSize: 1 } },
            y: { grid: { display: false } }
        }
    }
});
</script>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>