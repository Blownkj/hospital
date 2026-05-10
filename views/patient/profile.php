<?php
use App\Core\View;
use App\Core\Session;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<a href="<?= BASE_URL ?>/patient/dashboard" class="back-link">← Личный кабинет</a>

<?php if ($flash): ?>
    <div class="alert alert--success" role="alert">
        <span class="alert__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
            </svg>
        </span>
        <span class="alert__body"><?= View::e($flash) ?></span>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert--error" role="alert">
        <span class="alert__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
            </svg>
        </span>
        <span class="alert__body"><?= View::e($error) ?></span>
    </div>
<?php endif; ?>

<div class="page-header">
    <h1 class="page-title">Мой профиль</h1>
</div>

<div class="card u-mb-4">
    <div class="card__body">
        <form method="POST" action="<?= BASE_URL ?>/patient/profile">
            <input type="hidden" name="csrf_token" value="<?= View::e(Session::generateCsrfToken()) ?>">

            <div class="form__group">
                <label class="form__label form__label--required" for="last_name">Фамилия</label>
                <input class="form__control" type="text" id="last_name" name="last_name"
                       value="<?= View::e($patient['last_name'] ?? '') ?>" required>
            </div>

            <div class="form__group">
                <label class="form__label form__label--required" for="first_name">Имя</label>
                <input class="form__control" type="text" id="first_name" name="first_name"
                       value="<?= View::e($patient['first_name'] ?? '') ?>" required>
            </div>

            <div class="form__group">
                <label class="form__label" for="middle_name">Отчество</label>
                <input class="form__control" type="text" id="middle_name" name="middle_name"
                       value="<?= View::e($patient['middle_name'] ?? '') ?>">
            </div>

            <div class="form__group">
                <label class="form__label" for="birth_date">Дата рождения</label>
                <input class="form__control" type="date" id="birth_date" name="birth_date"
                       value="<?= View::e($patient['birth_date'] ?? '') ?>">
            </div>

            <div class="form__group">
                <label class="form__label" for="phone">Телефон</label>
                <input class="form__control" type="tel" id="phone" name="phone"
                       value="<?= View::e($patient['phone'] ?? '') ?>"
                       placeholder="+7 (999) 000-00-00">
            </div>

            <div class="form__group">
                <label class="form__label" for="address">Адрес</label>
                <input class="form__control" type="text" id="address" name="address"
                       value="<?= View::e($patient['address'] ?? '') ?>"
                       placeholder="Город, улица, дом">
            </div>

            <div class="form__group">
                <label class="form__label" for="chronic_diseases">Хронические заболевания</label>
                <textarea class="form__control" id="chronic_diseases" name="chronic_diseases" rows="3"
                          placeholder="Укажите если есть..."><?= View::e($patient['chronic_diseases'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn--primary btn--block">
                Сохранить изменения
            </button>
        </form>
    </div>
</div>

<!-- Смена пароля -->
<div class="card">
    <div class="card__body">
        <h2 class="card__title u-mb-5">
            <?php icon('lock', 18) ?> Смена пароля
        </h2>
        <form method="POST" action="<?= BASE_URL ?>/patient/profile/password">
            <input type="hidden" name="csrf_token" value="<?= View::e(Session::generateCsrfToken()) ?>">

            <div class="form__group">
                <label class="form__label form__label--required" for="current_password">Текущий пароль</label>
                <input class="form__control" type="password" id="current_password" name="current_password"
                       required placeholder="Введите текущий пароль">
            </div>
            <div class="form__group">
                <label class="form__label form__label--required" for="new_password">Новый пароль</label>
                <input class="form__control" type="password" id="new_password" name="new_password"
                       required placeholder="Минимум 8 символов" minlength="8">
            </div>
            <div class="form__group">
                <label class="form__label form__label--required" for="confirm_password">Подтвердите новый пароль</label>
                <input class="form__control" type="password" id="confirm_password" name="confirm_password"
                       required placeholder="Повторите новый пароль">
            </div>

            <button type="submit" class="btn btn--secondary btn--block">
                Изменить пароль
            </button>
        </form>
    </div>
</div>
