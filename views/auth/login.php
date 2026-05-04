<?php
use App\Core\View;
$pageTitle = 'Вход в систему';
require ROOT_PATH . '/views/layout/header.php';
?>

<h1 class="auth-title">Вход в систему</h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= View::e($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/login">
    <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">

    <div class="form-group">
        <label for="email">Email</label>
        <input class="form-control" type="email" id="email" name="email"
               required autofocus placeholder="example@mail.ru">
    </div>

    <div class="form-group">
        <label for="password">Пароль</label>
        <input class="form-control" type="password" id="password" name="password"
               required placeholder="Ваш пароль">
    </div>

    <button type="submit" class="btn btn-primary btn-block">Войти</button>
</form>

<p class="auth-footer">
    Нет аккаунта? <a href="<?= BASE_URL ?>/register">Зарегистрироваться</a>
</p>

<?php require ROOT_PATH . '/views/layout/footer.php'; ?>