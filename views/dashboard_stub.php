<?php
use App\Core\View;
$pageTitle = 'Личный кабинет';
require ROOT_PATH . '/views/layout/header.php';
?>

<h1>Добро пожаловать!</h1>
<p class="u-text-center u-text-muted u-mt-2 u-mb-6">
    Роль: <strong><?= View::e($role) ?></strong> &nbsp;·&nbsp; <?= View::e($email) ?>
</p>
<form method="POST" action="<?= BASE_URL ?>/logout">
    <input type="hidden" name="csrf_token" value="<?= View::e(App\Core\Session::generateCsrfToken()) ?>">
    <button type="submit" class="btn btn--ghost btn--block">Выйти</button>
</form>

<?php require ROOT_PATH . '/views/layout/footer.php'; ?>