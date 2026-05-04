<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';

$dayNames = [1=>'Пн',2=>'Вт',3=>'Ср',4=>'Чт',5=>'Пт',6=>'Сб',7=>'Вс'];
?>

<a href="<?= BASE_URL ?>/doctors" class="back-link">← Все врачи</a>

<!-- Шапка врача -->
<div class="card" style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap">
    <?php if ($doctor['photo_url']): ?>
    <img src="<?= View::e($doctor['photo_url']) ?>" alt=""
         style="width:120px;height:120px;border-radius:50%;object-fit:cover;flex-shrink:0">
    <?php else: ?>
    <div style="width:120px;height:120px;border-radius:50%;background:#e8eaf0;display:flex;align-items:center;justify-content:center;font-size:40px;flex-shrink:0">👨‍⚕️</div>
    <?php endif; ?>

    <div style="flex:1">
        <h1 style="font-size:22px;font-weight:700;margin-bottom:4px"><?= View::e($doctor['full_name']) ?></h1>
        <div style="color:#4a90e2;font-size:14px;margin-bottom:10px"><?= View::e($doctor['specialization']) ?></div>

        <?php if ($rating > 0): ?>
        <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px">
            <span style="font-size:18px"><?= str_repeat('⭐', round($rating)) ?></span>
            <span style="font-weight:600"><?= $rating ?></span>
            <span class="text-muted" style="font-size:13px">(<?= count($reviews) ?> отзывов)</span>
        </div>
        <?php endif; ?>

        <?php if ($doctor['bio']): ?>
        <p style="font-size:14px;color:#555;line-height:1.6"><?= View::e($doctor['bio']) ?></p>
        <?php endif; ?>

        <?php if (empty($_SESSION['user_role']) || $_SESSION['user_role'] === 'patient'): ?>
        <a href="<?= BASE_URL ?>/patient/book?doctor_id=<?= (int)$doctor['id'] ?>"
           class="btn btn-primary" style="display:inline-block;margin-top:14px">
            Записаться на приём
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Расписание -->
<div class="card" style="margin-top:16px">
    <div class="card-title">🗓 Расписание приёма</div>
    <?php if (empty($scheduleByDay)): ?>
        <p class="text-muted" style="font-size:13px">Расписание не указано.</p>
    <?php else: ?>
    <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php for ($d = 1; $d <= 7; $d++): ?>
            <?php if (isset($scheduleByDay[$d])): ?>
            <?php $s = $scheduleByDay[$d]; ?>
            <div style="background:#f0f7ff;border:1px solid #c8dff7;border-radius:8px;padding:10px 14px;text-align:center;min-width:70px">
                <div style="font-weight:600;font-size:13px;color:#1d4ed8"><?= $dayNames[$d] ?></div>
                <div style="font-size:12px;color:#555;margin-top:3px">
                    <?= substr($s['start_time'],0,5) ?>–<?= substr($s['end_time'],0,5) ?>
                </div>
            </div>
            <?php else: ?>
            <div style="background:#f5f5f7;border:1px solid #e0e0e8;border-radius:8px;padding:10px 14px;text-align:center;min-width:70px;opacity:.5">
                <div style="font-weight:600;font-size:13px;color:#999"><?= $dayNames[$d] ?></div>
                <div style="font-size:12px;color:#bbb;margin-top:3px">выходной</div>
            </div>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Отзывы -->
<div class="card" style="margin-top:16px">
    <div class="card-title">💬 Отзывы пациентов</div>
    <?php if (empty($reviews)): ?>
        <p class="text-muted text-center" style="padding:20px 0">Отзывов пока нет.</p>
    <?php else: ?>
        <?php foreach ($reviews as $r): ?>
        <div style="padding:14px 0;border-bottom:1px solid var(--border)">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
                <div style="font-weight:600;font-size:14px"><?= View::e($r['patient_name']) ?></div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span><?= str_repeat('⭐', (int)$r['rating']) ?></span>
                    <span class="text-muted" style="font-size:12px"><?= date('d.m.Y', strtotime($r['created_at'])) ?></span>
                </div>
            </div>
            <p style="margin-top:8px;font-size:14px;color:#444"><?= View::e($r['text']) ?></p>
            <?php if (!empty($r['admin_reply'])): ?>
            <div style="margin-top:10px;padding:10px 14px;background:var(--color-bg-secondary,#f8f9fa);border-left:3px solid var(--color-primary,#3b82f6);border-radius:4px">
                <div style="font-size:12px;font-weight:600;margin-bottom:4px;color:var(--color-primary,#3b82f6)">Ответ клиники</div>
                <p style="font-size:13px;line-height:1.6;margin:0;color:#444"><?= View::e($r['admin_reply']) ?></p>
                <div class="text-muted" style="font-size:11px;margin-top:4px">
                    <?= date('d.m.Y', strtotime($r['admin_reply_at'])) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>