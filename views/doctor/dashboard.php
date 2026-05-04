<?php use App\Core\View; require ROOT_PATH . '/views/layout/public_header.php'; ?>

<?php if ($flash): ?><div class="alert alert-success">✅ <?= View::e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error">⚠️ <?= View::e($error) ?></div><?php endif; ?>

<!-- Статистика -->
<div class="dash-grid" style="margin-bottom:24px">
    <div class="dash-widget">
        <div class="dash-widget-icon">📅</div>
        <div style="font-size:24px;font-weight:700;color:#4a90e2">
            <?= (int)$stats['this_month'] ?>
        </div>
        <div class="dash-widget-label">Приёмов за месяц</div>
    </div>
    <div class="dash-widget">
        <div class="dash-widget-icon">✅</div>
        <div style="font-size:24px;font-weight:700;color:#16a34a">
            <?= (int)$stats['completed'] ?>
        </div>
        <div class="dash-widget-label">Всего завершено</div>
    </div>
    <div class="dash-widget">
        <div class="dash-widget-icon">⏳</div>
        <div style="font-size:24px;font-weight:700;color:#d97706">
            <?= (int)$stats['upcoming'] ?>
        </div>
        <div class="dash-widget-label">Предстоящих</div>
    </div>
    <div class="dash-widget">
        <div class="dash-widget-icon">⭐</div>
        <div style="font-size:24px;font-weight:700;color:#7c3aed">
            <?= $stats['avg_rating'] > 0
                ? $stats['avg_rating'] . ' <span style="font-size:14px;color:#aaa">/ 5</span>'
                : '—' ?>
        </div>
        <div class="dash-widget-label">
            Рейтинг
            <?php if ($stats['review_count'] > 0): ?>
                <span class="text-muted" style="font-size:11px">
                    (<?= $stats['review_count'] ?> отз.)
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="page-header">
    <div>
        <h1 class="page-title">Добрый день, <?= View::e(explode(' ', $profile['full_name'])[1] ?? $profile['full_name']) ?>!</h1>
        <p class="text-muted"><?= View::e($profile['specialization']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/doctor/profile" 
        class="btn btn-secondary" style="padding:8px 18px;font-size:13px">
        Мой профиль
    </a>
    <a href="<?= BASE_URL ?>/logout" class="btn btn-danger" style="padding:8px 18px;font-size:13px">Выйти</a>
</div>

<!-- Приёмы на сегодня -->
<div class="card">
    <div class="card-title">📅 Приёмы сегодня
        <span style="font-size:13px;font-weight:400;color:#888;margin-left:8px"><?= date('d.m.Y') ?></span>
    </div>

    <?php if (empty($today)): ?>
        <p class="text-muted text-center" style="padding:20px 0">На сегодня записей нет.</p>
    <?php else: ?>
        <?php foreach ($today as $appt): ?>
            <?php
                $statusLabels = [
                    'pending'     => ['⏳ Ожидает',    'pending'],
                    'confirmed'   => ['✓ Подтверждена', 'confirmed'],
                    'in_progress' => ['▶ Идёт приём',   'in-progress'],
                ];
                [$label, $cls] = $statusLabels[$appt['status']] ?? ['—', 'pending'];
                $age = (int) date('Y') - (int) substr($appt['patient_birth_date'], 0, 4);
            ?>
            <div class="appt-row" style="align-items:flex-start;flex-direction:column;gap:10px">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;flex-wrap:wrap;gap:8px">
                    <div>
                        <div class="appt-doctor" style="font-size:15px">
                            <?= View::e($appt['patient_name']) ?>
                            <span style="font-weight:400;color:#888">, <?= $age ?> лет</span>
                        </div>
                        <div class="appt-time">
                            🕐 <?= date('H:i', strtotime($appt['scheduled_at'])) ?>
                            <?php if ($appt['chronic_diseases']): ?>
                            · <span style="color:#dc2626;font-size:12px">⚠ Хронические: <?= View::e(mb_substr($appt['chronic_diseases'], 0, 60)) ?>...</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <span class="badge badge-<?= $cls ?>"><?= $label ?></span>
                        <a href="<?= BASE_URL ?>/doctor/appointment/<?= (int)$appt['id'] ?>"
                           class="btn btn-primary" style="padding:7px 16px;font-size:13px">
                            <?= $appt['status'] === 'in_progress' ? '▶ Продолжить' : 'Открыть' ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Последние завершённые -->
<?php if (!empty($recent)): ?>
<div class="card">
    <div class="card-title">📋 Последние приёмы</div>
    <?php foreach ($recent as $appt): ?>
    <div class="appt-row">
        <div class="appt-info">
            <div class="appt-doctor"><?= View::e($appt['patient_name']) ?></div>
            <div class="appt-time">
                <?= date('d.m.Y H:i', strtotime($appt['scheduled_at'])) ?>
                <?php if ($appt['diagnosis']): ?>
                · <span style="color:#555;font-size:12px">Диагноз: <?= View::e(mb_substr($appt['diagnosis'], 0, 50)) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <span class="badge badge-completed">✅ Завершён</span>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>