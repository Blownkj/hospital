<?php
use App\Core\View;
$pageTitle = 'Вход в систему';
require ROOT_PATH . '/views/layout/header.php';
?>

<h1 class="auth-title">Вход в систему</h1>

<?php if ($error): ?>
    <div class="alert alert--error" role="alert">
        <span class="alert__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>
            </svg>
        </span>
        <span class="alert__body"><?= View::e($error) ?></span>
    </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/login">
    <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">

    <div class="form__group">
        <label class="form__label" for="email">Email</label>
        <input class="form__control" type="email" id="email" name="email"
               required autofocus placeholder="example@mail.ru">
    </div>

    <div class="form__group">
        <label class="form__label" for="password">Пароль</label>
        <input class="form__control" type="password" id="password" name="password"
               required placeholder="Ваш пароль">
    </div>

    <button type="submit" class="btn btn--primary btn--block">Войти</button>
</form>

<p class="auth-footer">
    Нет аккаунта? <a href="<?= BASE_URL ?>/register">Зарегистрироваться</a>
</p>

<?php require ROOT_PATH . '/views/layout/footer.php'; ?>
