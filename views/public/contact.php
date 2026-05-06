<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
require ROOT_PATH . '/views/partials/flash.php';
?>

<div class="page-header">
    <h1 class="page-title">Контакты</h1>
</div>

<div class="contact-grid">
    <!-- Информация -->
    <div class="contact-info">
        <h2>Как нас найти</h2>

        <div class="contact-info__item">
            <div class="contact-info__icon"><?php icon('map-pin', 18) ?></div>
            <div>
                <div class="contact-info__label">Адрес</div>
                <div class="contact-info__value">г. Москва, ул. Медицинская, д. 1</div>
            </div>
        </div>

        <div class="contact-info__item">
            <div class="contact-info__icon"><?php icon('phone', 18) ?></div>
            <div>
                <div class="contact-info__label">Телефон</div>
                <div class="contact-info__value"><a href="tel:+74951234567">+7 (495) 123-45-67</a></div>
            </div>
        </div>

        <div class="contact-info__item">
            <div class="contact-info__icon"><?php icon('mail', 18) ?></div>
            <div>
                <div class="contact-info__label">Email</div>
                <div class="contact-info__value"><a href="mailto:info@hospital.local">info@hospital.local</a></div>
            </div>
        </div>

        <div class="contact-info__item">
            <div class="contact-info__icon"><?php icon('clock', 18) ?></div>
            <div>
                <div class="contact-info__label">Режим работы</div>
                <div class="contact-info__value">
                    Пн–Пт: 08:00 – 20:00<br>
                    Сб: 09:00 – 16:00<br>
                    Вс: выходной
                </div>
            </div>
        </div>
    </div>

    <!-- Форма -->
    <div class="contact-form">
        <h2>Обратная связь</h2>

        <?php if ($sent): ?>
            <div class="alert alert--success" role="alert">
                <span class="alert__icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                    </svg>
                </span>
                <span class="alert__body">Ваше сообщение отправлено! Мы свяжемся с вами в течение рабочего дня.</span>
            </div>
        <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>/contact">
                <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                <div class="form__group">
                    <label class="form__label form__label--required" for="name">Ваше имя</label>
                    <input class="form__control" type="text" id="name" name="name" required placeholder="Иван Иванов">
                </div>
                <div class="form__group">
                    <label class="form__label" for="phone_contact">Телефон</label>
                    <input class="form__control" type="tel" id="phone_contact" name="phone" placeholder="+7 900 000-00-00">
                </div>
                <div class="form__group">
                    <label class="form__label form__label--required" for="message">Сообщение</label>
                    <textarea class="form__control" id="message" name="message" required
                              placeholder="Задайте вопрос или оставьте пожелание..."></textarea>
                </div>
                <button type="submit" class="btn btn--primary btn--block">Отправить сообщение</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
