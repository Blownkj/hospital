<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="section-title">Наши специалисты</div>

<div class="doctors-grid">
    <?php foreach ($doctors as $doctor): ?>
        <div class="doctor-card">
            <div class="doctor-avatar">
                <?= View::e(View::initials($doctor['full_name'])) ?>
            </div>
            <div class="doctor-name"><?= View::e($doctor['full_name']) ?></div>
            <div class="doctor-spec"><?= View::e($doctor['specialization']) ?></div>
            <div class="doctor-bio"><?= View::e($doctor['bio'] ?? '') ?></div>
            <?php if ($doctor['avg_rating']): ?>
                <div class="doctor-rating">
                    <span class="stars"><?= View::stars($doctor['avg_rating']) ?></span>
                    <span><?= View::e($doctor['avg_rating']) ?> (<?= (int)$doctor['review_count'] ?> отз.)</span>
                </div>
            <?php else: ?>
                <div class="doctor-rating" style="color:#ccc">Отзывов пока нет</div>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/doctors/<?= (int)$doctor['id'] ?>"
                class="btn btn-secondary" style="margin-top:12px;display:block;text-align:center">
                    Подробнее →
            </a>
            <?php if (empty($_SESSION['user_role']) || $_SESSION['user_role'] === 'patient'): ?>
                <a href="<?= BASE_URL ?>/patient/book?doctor_id=<?= (int)$doctor['id'] ?>" class="btn-book">Записаться на приём</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>