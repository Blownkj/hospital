<?php
use App\Core\View;
$pageTitle = 'Регистрация';
require ROOT_PATH . '/views/layout/header.php';
?>

<h1 class="auth-title">Регистрация пациента</h1>

<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-error"><?= View::e($errors['general']) ?></div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/register">
    <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">

    <div class="form-group">
        <label for="email">Email *</label>
        <input class="form-control" type="email" id="email" name="email"
               required value="<?= View::e($old['email'] ?? '') ?>">
        <?php if (!empty($errors['email'])): ?>
            <div class="field-error"><?= View::e($errors['email']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="full_name">Полное имя *</label>
        <input class="form-control" type="text" id="full_name" name="full_name"
               required placeholder="Иванов Иван Иванович"
               value="<?= View::e($old['full_name'] ?? '') ?>">
        <?php if (!empty($errors['full_name'])): ?>
            <div class="field-error"><?= View::e($errors['full_name']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="birth_date">Дата рождения *</label>
        <input class="form-control" type="date" id="birth_date" name="birth_date"
               required value="<?= View::e($old['birth_date'] ?? '') ?>">
        <?php if (!empty($errors['birth_date'])): ?>
            <div class="field-error"><?= View::e($errors['birth_date']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="gender">Пол *</label>
        <select class="form-control" id="gender" name="gender" required>
            <option value="">— выберите —</option>
            <option value="m"     <?= ($old['gender'] ?? '') === 'm'     ? 'selected' : '' ?>>Мужской</option>
            <option value="f"     <?= ($old['gender'] ?? '') === 'f'     ? 'selected' : '' ?>>Женский</option>
            <option value="other" <?= ($old['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Другой</option>
        </select>
        <?php if (!empty($errors['gender'])): ?>
            <div class="field-error"><?= View::e($errors['gender']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="phone">Телефон</label>
        <input class="form-control" type="tel" id="phone" name="phone"
               placeholder="+7 900 000-00-00"
               value="<?= View::e($old['phone'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="password">Пароль * (минимум 6 символов)</label>
        <input class="form-control" type="password" id="password" name="password" required>
        <?php if (!empty($errors['password'])): ?>
            <div class="field-error"><?= View::e($errors['password']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password2">Повторите пароль *</label>
        <input class="form-control" type="password" id="password2" name="password2" required>
        <?php if (!empty($errors['password2'])): ?>
            <div class="field-error"><?= View::e($errors['password2']) ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Зарегистрироваться</button>
</form>

<p class="auth-footer">
    Уже есть аккаунт? <a href="<?= BASE_URL ?>/login">Войти</a>
</p>

<?php require ROOT_PATH . '/views/layout/footer.php'; ?>