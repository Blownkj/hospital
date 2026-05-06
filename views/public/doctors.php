<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="page-header">
    <h1 class="page-title">Наши специалисты</h1>
</div>

<div class="doctors-grid">
    <?php foreach ($doctors as $doctor): ?>
        <?php $clickable = false; include ROOT_PATH . '/views/partials/doctor-card.php'; ?>
    <?php endforeach; ?>
</div>

<?php
$qs = fn(int $p) => http_build_query(array_merge($_GET, ['page' => $p]));
include ROOT_PATH . '/views/partials/pagination.php';
?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
