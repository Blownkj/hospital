<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
$typeLabels = ['drug' => '💊 Препарат', 'procedure' => '🔧 Процедура', 'referral' => '📄 Направление'];
?>

<a href="<?= BASE_URL ?>/patient/dashboard" class="back-link">← Личный кабинет</a>

<?php if ($flash): ?>
    <div class="alert alert-success">✅ <?= View::e($flash) ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Медицинская карта</h1>
        <p class="text-muted"><?= View::e($patient['full_name']) ?></p>
    </div>
</div>

<!-- Хронические заболевания -->
<?php if ($patient['chronic_diseases']): ?>
<div class="alert alert-warning" style="margin-bottom:20px">
    <strong>📋 Хронические заболевания:</strong><br>
    <?= View::e($patient['chronic_diseases']) ?>
</div>
<?php endif; ?>

<!-- История визитов -->
<?php if (empty($visits)): ?>
<div class="card" style="text-align:center;padding:48px">
    <div style="font-size:40px;margin-bottom:12px">🗂</div>
    <p class="text-muted">Визитов пока нет.</p>
    <a href="<?= BASE_URL ?>/patient/book" class="btn btn-primary" style="display:inline-block;margin-top:12px">
        Записаться к врачу
    </a>
</div>
<?php else: ?>

<p class="text-muted" style="margin-bottom:16px;font-size:13px">
    Всего визитов: <?= count($visits) ?>
</p>

<?php foreach ($visits as $v): ?>
<div class="card" style="margin-bottom:16px">
    <!-- Шапка визита -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;margin-bottom:14px">
        <div>
            <div style="font-size:15px;font-weight:600">
                <?= View::e($v['doctor_name']) ?>
                <span style="color:#4a90e2;font-weight:400;font-size:13px"> — <?= View::e($v['specialization']) ?></span>
            </div>
            <div class="text-muted" style="font-size:13px;margin-top:3px">
                📅 <?= date('d.m.Y', strtotime($v['scheduled_at'])) ?>
                🕐 <?= date('H:i', strtotime($v['started_at'])) ?>
                <?php if ($v['ended_at']): ?>
                — <?= date('H:i', strtotime($v['ended_at'])) ?>
                <?php endif; ?>
            </div>
        </div>
        <span class="badge badge-completed">✅ Завершён</span>
    </div>

    <!-- Протокол -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;font-size:13px;margin-bottom:<?= empty($v['prescriptions']) ? '0' : '14px' ?>">
        <div>
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#999;margin-bottom:5px">Жалобы</div>
            <div><?= $v['complaints'] ? View::e($v['complaints']) : '<span class="text-muted">—</span>' ?></div>
        </div>
        <div>
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#999;margin-bottom:5px">Осмотр</div>
            <div><?= $v['examination'] ? View::e($v['examination']) : '<span class="text-muted">—</span>' ?></div>
        </div>
        <div>
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#999;margin-bottom:5px">Диагноз</div>
            <div style="font-weight:500"><?= $v['diagnosis'] ? View::e($v['diagnosis']) : '<span class="text-muted">—</span>' ?></div>
        </div>
    </div>

    <!-- Назначения -->
    <?php if (!empty($v['prescriptions'])): ?>
    <div style="border-top:1px solid #f0f0f5;padding-top:12px">
        <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#999;margin-bottom:8px">
            Назначения
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
            <?php foreach ($v['prescriptions'] as $pr): ?>
            <div style="background:#f7f8fa;border:1px solid #e8e8f0;border-radius:8px;padding:7px 12px;font-size:13px">
                <span style="color:#888;margin-right:4px"><?= $typeLabels[$pr['type']] ?? $pr['type'] ?></span>
                <strong><?= View::e($pr['name']) ?></strong>
                <?php if ($pr['dosage']): ?>
                    <span class="text-muted"> — <?= View::e($pr['dosage']) ?></span>
                <?php endif; ?>
                <?php if ($pr['notes']): ?>
                    <div class="text-muted" style="font-size:12px;margin-top:2px"><?= View::e($pr['notes']) ?></div>
                <?php endif; ?>
            </div>
            <a href="<?= BASE_URL ?>/patient/visit/<?= (int)$v['visit_id'] ?>/print"
                target="_blank" class="btn btn-secondary"
                style="font-size:12px;padding:6px 14px;margin-top:10px;display:inline-block">
                    🖨 Распечатать назначения
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>