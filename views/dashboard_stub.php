<?php
use App\Core\View;
$pageTitle = 'Личный кабинет';
require ROOT_PATH . '/views/layout/header.php';
?>

<h1>Добро пожаловать!</h1>
<p style="text-align:center;color:#666;margin:12px 0 24px">
    Роль: <strong><?= View::e($role) ?></strong> &nbsp;·&nbsp; <?= View::e($email) ?>
</p>
<a href="<?= BASE_URL ?>/logout" class="btn" style="display:block;text-align:center;text-decoration:none">Выйти</a>

<?php require ROOT_PATH . '/views/layout/footer.php'; ?>