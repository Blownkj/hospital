<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="page-header">
    <h1 class="page-title">О клинике</h1>
</div>

<!-- Миссия -->
<div class="card">
    <div class="card-title">🏥 Наша миссия</div>
    <p>МедЦентр — современная многопрофильная клиника, основанная в 2010 году.
       Мы предоставляем качественную медицинскую помощь с использованием
       передовых технологий диагностики и лечения.</p>
    <p style="margin-top:12px">Наша цель — сделать качественную медицину доступной
       и удобной для каждого пациента.</p>
</div>

<!-- Преимущества -->
<div class="card">
    <div class="card-title">⭐ Наши преимущества</div>
    <div class="about-features">
        <div class="about-feature">
            <div class="about-feature-icon">👨‍⚕️</div>
            <div class="about-feature-title">Опытные специалисты</div>
            <div class="about-feature-text">Врачи высшей категории с опытом от 10 лет</div>
        </div>
        <div class="about-feature">
            <div class="about-feature-icon">🔬</div>
            <div class="about-feature-title">Современное оборудование</div>
            <div class="about-feature-text">Диагностика на оборудовании последнего поколения</div>
        </div>
        <div class="about-feature">
            <div class="about-feature-icon">📱</div>
            <div class="about-feature-title">Онлайн-запись</div>
            <div class="about-feature-text">Запись к врачу в любое время без очередей</div>
        </div>
        <div class="about-feature">
            <div class="about-feature-icon">🗂</div>
            <div class="about-feature-title">Электронная карта</div>
            <div class="about-feature-text">Вся история лечения в одном месте</div>
        </div>
    </div>
</div>

<!-- Лицензии и адрес -->
<div class="card">
    <div class="card-title">📋 Лицензии и реквизиты</div>
    <table class="about-table">
        <tr>
            <td>Лицензия</td>
            <td>№ ЛО-77-01-000000 от 01.01.2010</td>
        </tr>
        <tr>
            <td>Режим работы</td>
            <td>Пн–Пт: 8:00–20:00, Сб: 9:00–16:00</td>
        </tr>
        <tr>
            <td>Адрес</td>
            <td>г. Москва, ул. Примерная, д. 1</td>
        </tr>
        <tr>
            <td>Телефон</td>
            <td>+7 (495) 000-00-00</td>
        </tr>
        <tr>
            <td>Email</td>
            <td>info@medcenter.ru</td>
        </tr>
    </table>
</div>

<!-- CTA -->
<?php if (empty($_SESSION['user_role']) || $_SESSION['user_role'] === 'patient'): ?>
<div class="card" style="text-align:center;padding:32px">
    <div style="font-size:32px;margin-bottom:12px">🏥</div>
    <h2 style="margin-bottom:8px">Готовы записаться?</h2>
    <p class="text-muted" style="margin-bottom:20px">Выберите удобное время онлайн за 2 минуты</p>
    <a href="<?= BASE_URL ?>/patient/book" class="btn btn-primary">Записаться к врачу</a>
</div>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>