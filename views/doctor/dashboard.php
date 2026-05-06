<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<?php include ROOT_PATH . '/views/partials/flash.php'; ?>

<div class="page-header u-mb-6">
    <div>
        <h1 class="page-title">
            Добрый день, <?= View::e(explode(' ', $profile['full_name'])[1] ?? $profile['full_name']) ?>!
        </h1>
        <p class="u-text-muted u-text-sm"><?= View::e($profile['specialization']) ?></p>
    </div>
    <div class="u-flex u-gap-2">
        <a href="<?= BASE_URL ?>/doctor/profile" class="btn btn--secondary btn--sm">Профиль</a>
        <form method="POST" action="<?= BASE_URL ?>/logout" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= View::e(App\Core\Session::generateCsrfToken()) ?>">
            <button type="submit" class="btn btn--ghost btn--sm">Выйти</button>
        </form>
    </div>
</div>

<!-- Статистика -->
<div class="stats-grid u-mb-8">
    <div class="stat-card">
        <div class="stat-card__icon"><?php icon('calendar', 28) ?></div>
        <div class="stat-card__value"><?= (int)$stats['this_month'] ?></div>
        <div class="stat-card__label">Приёмов за месяц</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon"><?php icon('check-circle-2', 28) ?></div>
        <div class="stat-card__value"><?= (int)$stats['completed'] ?></div>
        <div class="stat-card__label">Всего завершено</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon"><?php icon('clock', 28) ?></div>
        <div class="stat-card__value"><?= (int)$stats['upcoming'] ?></div>
        <div class="stat-card__label">Предстоящих</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon"><?php icon('star', 28) ?></div>
        <div class="stat-card__value">
            <?= $stats['avg_rating'] > 0 ? $stats['avg_rating'] : '—' ?>
        </div>
        <div class="stat-card__label">
            Рейтинг
            <?php if ($stats['review_count'] > 0): ?>
                <span class="u-text-xs">(<?= $stats['review_count'] ?> отз.)</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Приёмы на сегодня -->
<div class="card u-mb-5">
    <div class="card__body">
        <h2 class="card__title u-mb-4">
            <?php icon('calendar', 18) ?> Приёмы сегодня
            <span class="u-text-sm u-fw-normal u-text-muted u-ms-1"><?= date('d.m.Y') ?></span>
        </h2>

        <?php if (empty($today)): ?>
            <p class="u-text-muted u-text-center u-p-6">На сегодня записей нет.</p>
        <?php else: ?>
            <?php foreach ($today as $appt):
                $statusMap = [
                    'pending'     => ['Ожидает',     'pending'],
                    'confirmed'   => ['Подтверждена','confirmed'],
                    'in_progress' => ['Идёт приём',  'in-progress'],
                ];
                [$statusLabel, $statusCls] = $statusMap[$appt['status']] ?? ['—', 'pending'];
                $age = (int) date('Y') - (int) substr($appt['patient_birth_date'], 0, 4);
            ?>
            <div class="appt-row appt-row--col">
                <div class="u-flex u-jc-between u-w-full u-ai-center u-flex-wrap u-gap-3">
                    <div>
                        <div class="appt-doctor">
                            <?= View::e($appt['patient_name']) ?>
                            <span>, <?= $age ?> лет</span>
                        </div>
                        <div class="appt-time">
                            <?php icon('clock', 13) ?>
                            <?= date('H:i', strtotime($appt['scheduled_at'])) ?>
                            <?php if ($appt['chronic_diseases']): ?>
                            ·
                            <span class="chronic-warn">
                                <?php icon('alert-triangle', 12) ?>
                                Хронические: <?= View::e(mb_substr($appt['chronic_diseases'], 0, 60)) ?>...
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="u-flex u-ai-center u-gap-3">
                        <?php include ROOT_PATH . '/views/partials/status-badge.php'; ?>
                        <a href="<?= BASE_URL ?>/doctor/appointment/<?= (int)$appt['id'] ?>"
                           class="btn btn--primary btn--sm">
                            <?= $appt['status'] === 'in_progress' ? 'Продолжить' : 'Открыть' ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Последние завершённые -->
<?php if (!empty($recent)): ?>
<div class="card">
    <div class="card__body">
        <h2 class="card__title u-mb-4">
            <?php icon('clipboard-list', 18) ?> Последние приёмы
        </h2>
        <?php foreach ($recent as $appt): ?>
        <div class="appt-row">
            <div class="appt-info">
                <div class="appt-doctor"><?= View::e($appt['patient_name']) ?></div>
                <div class="appt-time">
                    <?= date('d.m.Y H:i', strtotime($appt['scheduled_at'])) ?>
                    <?php if ($appt['diagnosis']): ?>
                    · <span class="u-text-xs">Диагноз: <?= View::e(mb_substr($appt['diagnosis'], 0, 50)) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <span class="badge badge--success">
                <span class="badge__dot" aria-hidden="true"></span>
                Завершён
            </span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
