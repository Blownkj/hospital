<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="section-title">Контакты</div>

<div class="contact-grid">
    <!-- Информация -->
    <div class="contact-info">
        <h2>Как нас найти</h2>
        <p>📍 <strong>Адрес:</strong><br>г. Москва, ул. Медицинская, д. 1</p>
        <p>📞 <strong>Телефон:</strong><br><a href="tel:+74951234567" style="color:#4a90e2">+7 (495) 123-45-67</a></p>
        <p>✉️ <strong>Email:</strong><br><a href="mailto:info@hospital.local" style="color:#4a90e2">info@hospital.local</a></p>
        <p>🕐 <strong>Режим работы:</strong><br>
            Пн–Пт: 08:00 – 20:00<br>
            Сб: 09:00 – 16:00<br>
            Вс: выходной
        </p>
    </div>

    <!-- Форма обратной связи -->
    <div class="contact-form">
        <h2>Обратная связь</h2>

        <?php if ($sent): ?>
            <div class="success-box">
                ✅ Ваше сообщение отправлено! Мы свяжемся с вами в течение рабочего дня.
            </div>
        <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>/contact">
                <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                <div class="form-group">
                    <label for="name">Ваше имя</label>
                    <input type="text" id="name" name="name" required placeholder="Иван Иванов">
                </div>
                <div class="form-group">
                    <label for="phone_contact">Телефон</label>
                    <input type="tel" id="phone_contact" name="phone" placeholder="+7 900 000-00-00">
                </div>
                <div class="form-group">
                    <label for="message">Сообщение</label>
                    <textarea id="message" name="message" required
                              placeholder="Задайте вопрос или оставьте пожелание..."></textarea>
                </div>
                <button type="submit" class="btn-submit">Отправить сообщение</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>