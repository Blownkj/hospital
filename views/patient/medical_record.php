<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';

$typeLabels = [
    'drug'      => 'Препарат',
    'procedure' => 'Процедура',
    'referral'  => 'Направление',
];
?>

<a href="<?= BASE_URL ?>/patient/dashboard" class="back-link">← Личный кабинет</a>

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

<div class="page-header">
    <div>
        <h1 class="page-title">Медицинская карта</h1>
        <p class="u-text-muted u-text-sm"><?= View::e($patient['full_name']) ?></p>
    </div>
</div>

<!-- Хронические заболевания -->
<?php if ($patient['chronic_diseases']): ?>
<div class="alert alert--warning u-mb-5" role="alert">
    <span class="alert__icon"><?php icon('clipboard-list', 18) ?></span>
    <span class="alert__body">
        <strong>Хронические заболевания:</strong><br>
        <?= View::e($patient['chronic_diseases']) ?>
    </span>
</div>
<?php endif; ?>

<!-- История визитов -->
<?php if (empty($visits)): ?>
    <?php
    $emptyMessage = 'Визитов пока нет';
    $emptyLinkUrl = BASE_URL . '/patient/book';
    $emptyLinkText = 'Записаться к врачу';
    include ROOT_PATH . '/views/partials/empty-state.php';
    ?>
<?php else: ?>

<p class="u-text-muted u-text-sm u-mb-4">
    Всего визитов: <?= count($visits) ?>
</p>

<?php foreach ($visits as $v): ?>
<div class="card u-mb-4">
    <div class="card__body">
        <div class="visit-header">
            <div>
                <div class="visit-doctor__name">
                    <?= View::e($v['doctor_name']) ?>
                    <span class="visit-doctor__spec">— <?= View::e($v['specialization']) ?></span>
                </div>
                <div class="visit-time">
                    <?php icon('calendar', 13) ?>
                    <?= date('d.m.Y', strtotime($v['scheduled_at'])) ?>
                    <?php icon('clock', 13) ?>
                    <?= date('H:i', strtotime($v['started_at'])) ?>
                    <?php if ($v['ended_at']): ?>
                        — <?= date('H:i', strtotime($v['ended_at'])) ?>
                    <?php endif; ?>
                </div>
            </div>
            <span class="badge badge--success">
                <span class="badge__dot" aria-hidden="true"></span>
                Завершён
            </span>
        </div>

        <!-- Протокол -->
        <div class="protocol-grid">
            <div>
                <div class="protocol-label">Жалобы</div>
                <div><?= $v['complaints'] ? View::e($v['complaints']) : '<span class="u-text-muted">—</span>' ?></div>
            </div>
            <div>
                <div class="protocol-label">Осмотр</div>
                <div><?= $v['examination'] ? View::e($v['examination']) : '<span class="u-text-muted">—</span>' ?></div>
            </div>
            <div>
                <div class="protocol-label">Диагноз</div>
                <div class="u-fw-medium"><?= $v['diagnosis'] ? View::e($v['diagnosis']) : '<span class="u-text-muted">—</span>' ?></div>
            </div>
        </div>

        <!-- Назначения -->
        <?php if (!empty($v['prescriptions'])): ?>
        <div class="rx-list">
            <?php foreach ($v['prescriptions'] as $pr): ?>
            <div class="rx-tag">
                <span class="rx-tag__type"><?= View::e($typeLabels[$pr['type']] ?? $pr['type']) ?></span>
                <span class="rx-tag__name"><?= View::e($pr['name']) ?></span>
                <?php if ($pr['dosage']): ?>
                    <span class="rx-tag__dose"> — <?= View::e($pr['dosage']) ?></span>
                <?php endif; ?>
                <?php if ($pr['notes']): ?>
                    <span class="rx-tag__note"><?= View::e($pr['notes']) ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="u-mt-4">
            <a href="<?= BASE_URL ?>/patient/visit/<?= (int)$v['visit_id'] ?>/print"
               target="_blank" class="btn btn--secondary btn--sm">
                <?php icon('printer', 14) ?> Распечатать назначения
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
