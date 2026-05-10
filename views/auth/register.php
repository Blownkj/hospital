<?php
use App\Core\View;
$pageTitle = 'Регистрация';
require ROOT_PATH . '/views/layout/header.php';
?>

<h1 class="auth-title">Регистрация пациента</h1>

<?php if (!empty($errors['general'])): ?>
    <div class="alert alert--error" role="alert">
        <span class="alert__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>
            </svg>
        </span>
        <span class="alert__body"><?= View::e($errors['general']) ?></span>
    </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/register">
    <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">

    <div class="form__group">
        <label class="form__label form__label--required" for="email">Email</label>
        <input class="form__control" type="email" id="email" name="email"
               required value="<?= View::e($old['email'] ?? '') ?>">
        <?php if (!empty($errors['email'])): ?>
            <div class="form__error"><?= View::e($errors['email']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form__group">
        <label class="form__label form__label--required" for="last_name">Фамилия</label>
        <input class="form__control" type="text" id="last_name" name="last_name"
               required placeholder="Иванов"
               value="<?= View::e($old['last_name'] ?? '') ?>">
        <?php if (!empty($errors['last_name'])): ?>
            <div class="form__error"><?= View::e($errors['last_name']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form__group">
        <label class="form__label form__label--required" for="first_name">Имя</label>
        <input class="form__control" type="text" id="first_name" name="first_name"
               required placeholder="Иван"
               value="<?= View::e($old['first_name'] ?? '') ?>">
        <?php if (!empty($errors['first_name'])): ?>
            <div class="form__error"><?= View::e($errors['first_name']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form__group">
        <label class="form__label" for="middle_name">Отчество</label>
        <input class="form__control" type="text" id="middle_name" name="middle_name"
               placeholder="Иванович"
               value="<?= View::e($old['middle_name'] ?? '') ?>">
    </div>

    <div class="form__group">
        <label class="form__label form__label--required" for="birth_date">Дата рождения</label>
        <input class="form__control" type="date" id="birth_date" name="birth_date"
               required value="<?= View::e($old['birth_date'] ?? '') ?>">
        <?php if (!empty($errors['birth_date'])): ?>
            <div class="form__error"><?= View::e($errors['birth_date']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form__group">
        <label class="form__label form__label--required" for="gender">Пол</label>
        <select class="form__control" id="gender" name="gender" required>
            <option value="">— выберите —</option>
            <option value="m"     <?= ($old['gender'] ?? '') === 'm'     ? 'selected' : '' ?>>Мужской</option>
            <option value="f"     <?= ($old['gender'] ?? '') === 'f'     ? 'selected' : '' ?>>Женский</option>
            <option value="other" <?= ($old['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Другой</option>
        </select>
        <?php if (!empty($errors['gender'])): ?>
            <div class="form__error"><?= View::e($errors['gender']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form__group">
        <label class="form__label" for="phone">Телефон</label>
        <input class="form__control" type="tel" id="phone" name="phone"
               placeholder="+7 900 000-00-00"
               value="<?= View::e($old['phone'] ?? '') ?>">
    </div>

    <div class="form__group">
        <label class="form__label form__label--required" for="password">Пароль</label>
        <input class="form__control" type="password" id="password" name="password" required>
        <div class="form__hint">Минимум 6 символов</div>
        <?php if (!empty($errors['password'])): ?>
            <div class="form__error"><?= View::e($errors['password']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form__group">
        <label class="form__label form__label--required" for="password2">Повторите пароль</label>
        <input class="form__control" type="password" id="password2" name="password2" required>
        <?php if (!empty($errors['password2'])): ?>
            <div class="form__error"><?= View::e($errors['password2']) ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn--primary btn--block">Зарегистрироваться</button>
</form>

<p class="auth-footer">
    Уже есть аккаунт? <a href="<?= BASE_URL ?>/login">Войти</a>
</p>

<?php require ROOT_PATH . '/views/layout/footer.php'; ?>
