<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="section-title">Наши специалисты</div>

<div class="doctors-grid">
    <?php foreach ($doctors as $doctor): ?>
        <?php $clickable = false; include ROOT_PATH . '/views/partials/doctor-card.php'; ?>
    <?php endforeach; ?>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>