<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<div class="page-header">
    <h1 class="page-title">О клинике</h1>
</div>

<!-- Миссия -->
<div class="card u-mb-4">
    <div class="card__body">
        <h2 class="card__title">Наша миссия</h2>
        <p>МедЦентр — современная многопрофильная клиника, основанная в 2010 году.
           Мы предоставляем качественную медицинскую помощь с использованием
           передовых технологий диагностики и лечения.</p>
        <p class="u-mt-3">Наша цель — сделать качественную медицину доступной
           и удобной для каждого пациента.</p>
    </div>
</div>

<!-- Преимущества -->
<div class="card u-mb-4">
    <div class="card__body">
        <h2 class="card__title">Наши преимущества</h2>
        <div class="about-features">
            <div class="about-feature">
                <div class="about-feature__icon"><?php icon('stethoscope', 28) ?></div>
                <div class="about-feature__title">Опытные специалисты</div>
                <div class="about-feature__text">Врачи высшей категории с опытом от 10 лет</div>
            </div>
            <div class="about-feature">
                <div class="about-feature__icon"><?php icon('microscope', 28) ?></div>
                <div class="about-feature__title">Современное оборудование</div>
                <div class="about-feature__text">Диагностика на оборудовании последнего поколения</div>
            </div>
            <div class="about-feature">
                <div class="about-feature__icon"><?php icon('smartphone', 28) ?></div>
                <div class="about-feature__title">Онлайн-запись</div>
                <div class="about-feature__text">Запись к врачу в любое время без очередей</div>
            </div>
            <div class="about-feature">
                <div class="about-feature__icon"><?php icon('clipboard-list', 28) ?></div>
                <div class="about-feature__title">Электронная карта</div>
                <div class="about-feature__text">Вся история лечения в одном месте</div>
            </div>
        </div>
    </div>
</div>

<!-- Лицензии и реквизиты -->
<div class="card u-mb-4">
    <div class="card__body">
        <h2 class="card__title u-mb-4">Лицензии и реквизиты</h2>
        <div class="table-wrap">
            <table class="table">
                <tbody>
                    <tr><td class="u-fw-medium u-nowrap">Лицензия</td>     <td>№ ЛО-77-01-000000 от 01.01.2010</td></tr>
                    <tr><td class="u-fw-medium u-nowrap">Режим работы</td> <td>Пн–Пт: 8:00–20:00, Сб: 9:00–16:00</td></tr>
                    <tr><td class="u-fw-medium u-nowrap">Адрес</td>        <td>г. Москва, ул. Примерная, д. 1</td></tr>
                    <tr><td class="u-fw-medium u-nowrap">Телефон</td>      <td>+7 (495) 000-00-00</td></tr>
                    <tr><td class="u-fw-medium u-nowrap">Email</td>        <td>info@medcenter.ru</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- CTA -->
<?php if (empty($_SESSION['user_role']) || $_SESSION['user_role'] === 'patient'): ?>
<div class="cta-banner">
    <p class="cta-banner__title">Готовы записаться?</p>
    <p class="cta-banner__lead">Выберите удобное время онлайн за 2 минуты</p>
    <div class="cta-banner__actions">
        <a href="<?= BASE_URL ?>/patient/book" class="btn btn--white btn--lg">Записаться к врачу</a>
    </div>
</div>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
