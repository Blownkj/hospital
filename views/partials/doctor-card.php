<?php use App\Core\View; ?>
<?php $clickable = $clickable ?? false; ?>
<?php if ($clickable): ?>
<a href="<?= BASE_URL ?>/doctors/<?= (int)$doctor['id'] ?>" class="doctor-card clickable">
<?php else: ?>
<div class="doctor-card">
<?php endif; ?>
    <div class="doctor-avatar"><?= View::e(View::initials($doctor['full_name'])) ?></div>
    <div class="doctor-name"><?= View::e($doctor['full_name']) ?></div>
    <div class="doctor-spec"><?= View::e($doctor['specialization']) ?></div>
    <div class="doctor-bio"><?= View::e(mb_strimwidth($doctor['bio'] ?? '', 0, 90, '...')) ?></div>
    <?php if (!empty($doctor['avg_rating'])): ?>
        <div class="doctor-rating">
            <span class="stars"><?= View::stars($doctor['avg_rating']) ?></span>
            <span><?= View::e($doctor['avg_rating']) ?> (<?= (int)$doctor['review_count'] ?> отз.)</span>
        </div>
    <?php else: ?>
        <div class="doctor-rating text-muted">Отзывов пока нет</div>
    <?php endif; ?>
    <?php if (!$clickable): ?>
        <a href="<?= BASE_URL ?>/doctors/<?= (int)$doctor['id'] ?>"
           class="btn btn-secondary" style="margin-top:12px;display:block;text-align:center">
            Подробнее →
        </a>
        <?php if (empty($_SESSION['user_role']) || $_SESSION['user_role'] === 'patient'): ?>
            <a href="<?= BASE_URL ?>/patient/book?doctor_id=<?= (int)$doctor['id'] ?>" class="btn-book">Записаться на приём</a>
        <?php endif; ?>
    <?php endif; ?>
<?php if ($clickable): ?></a>
<?php else: ?>
</div>
<?php endif; ?>
