<?php
use App\Core\View;
use App\Core\Session;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<a href="<?= BASE_URL ?>/patient/dashboard" class="back-link">← Личный кабинет</a>

<?php if ($flash): ?>
    <div class="alert alert-success">✅ <?= View::e($flash) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= View::e($error) ?></div>
<?php endif; ?>

<div class="page-header">
    <h1 class="page-title">Мой профиль</h1>
</div>

<div class="card">
    <form method="POST" action="<?= BASE_URL ?>/patient/profile">
        <input type="hidden" name="csrf_token" value="<?= View::e(Session::generateCsrfToken()) ?>">

        <div class="form-group">
            <label>Полное имя *</label>
            <input type="text" name="full_name"
                   value="<?= View::e($patient['full_name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Дата рождения</label>
            <input type="date" name="birth_date"
                   value="<?= View::e($patient['birth_date'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Телефон</label>
            <input type="tel" name="phone"
                   value="<?= View::e($patient['phone'] ?? '') ?>"
                   placeholder="+7 (999) 000-00-00">
        </div>

        <div class="form-group">
            <label>Адрес</label>
            <input type="text" name="address"
                   value="<?= View::e($patient['address'] ?? '') ?>"
                   placeholder="Город, улица, дом">
        </div>

        <div class="form-group">
            <label>Хронические заболевания</label>
            <textarea name="chronic_diseases" rows="3"
                      placeholder="Укажите если есть..."><?= View::e($patient['chronic_diseases'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%">
            Сохранить изменения
        </button>
    </form>
</div>

<!-- Смена пароля — отдельная форма -->
<div class="card" style="margin-top:16px">
    <div class="card-title">🔒 Смена пароля</div>
    <form method="POST" action="<?= BASE_URL ?>/patient/profile/password">
        <input type="hidden" name="csrf_token" value="<?= View::e(Session::generateCsrfToken()) ?>">

        <div class="form-group">
            <label>Текущий пароль</label>
            <input type="password" name="current_password" required
                   placeholder="Введите текущий пароль">
        </div>
        <div class="form-group">
            <label>Новый пароль</label>
            <input type="password" name="new_password" required
                   placeholder="Минимум 8 символов" minlength="8">
        </div>
        <div class="form-group">
            <label>Подтвердите новый пароль</label>
            <input type="password" name="confirm_password" required
                   placeholder="Повторите новый пароль">
        </div>

        <button type="submit" class="btn btn-secondary" style="width:100%">
            Изменить пароль
        </button>
    </form>
</div>