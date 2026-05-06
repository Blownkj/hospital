</main>

<footer class="site-footer" role="contentinfo">
    <div class="site-footer__container">
        <div class="site-footer__grid">

            <div class="site-footer__col site-footer__col--brand">
                <a href="<?= BASE_URL ?>/" class="site-footer__brand" aria-label="Клиника — на главную">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6 6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/>
                        <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4"/>
                        <circle cx="18" cy="11.5" r="2.5"/>
                    </svg>
                    <span>Клиника</span>
                </a>
                <p class="site-footer__tagline">Современная медицинская помощь с заботой о каждом пациенте.</p>
                <address class="site-footer__contacts">
                    <a href="tel:+70000000000" class="site-footer__contact-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.56 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/>
                        </svg>
                        +7 (000) 000-00-00
                    </a>
                    <a href="mailto:info@clinic.ru" class="site-footer__contact-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="20" height="16" x="2" y="4" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                        info@clinic.ru
                    </a>
                    <span class="site-footer__contact-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        г. Москва, ул. Примерная, 1
                    </span>
                </address>
            </div>

            <div class="site-footer__col">
                <h3 class="site-footer__heading">Навигация</h3>
                <ul class="site-footer__links">
                    <li><a href="<?= BASE_URL ?>/">Главная</a></li>
                    <li><a href="<?= BASE_URL ?>/about">О клинике</a></li>
                    <li><a href="<?= BASE_URL ?>/doctors">Врачи</a></li>
                    <li><a href="<?= BASE_URL ?>/services">Услуги и цены</a></li>
                    <li><a href="<?= BASE_URL ?>/articles">Статьи</a></li>
                    <li><a href="<?= BASE_URL ?>/contact">Контакты</a></li>
                    <li><a href="<?= BASE_URL ?>/faq">FAQ</a></li>
                </ul>
            </div>

            <div class="site-footer__col">
                <h3 class="site-footer__heading">Пациентам</h3>
                <ul class="site-footer__links">
                    <li><a href="<?= BASE_URL ?>/register">Запись на приём</a></li>
                    <li><a href="<?= BASE_URL ?>/login">Личный кабинет</a></li>
                    <li><a href="<?= BASE_URL ?>/services">Прайс-лист</a></li>
                    <li><a href="<?= BASE_URL ?>/faq">Частые вопросы</a></li>
                    <li><a href="<?= BASE_URL ?>/contact">Обратная связь</a></li>
                </ul>
            </div>

            <div class="site-footer__col">
                <h3 class="site-footer__heading">Режим работы</h3>
                <ul class="site-footer__hours">
                    <li><span>Пн – Пт</span><span>8:00 – 20:00</span></li>
                    <li><span>Суббота</span><span>9:00 – 17:00</span></li>
                    <li><span>Воскресенье</span><span>выходной</span></li>
                </ul>
                <a href="<?= BASE_URL ?>/register" class="site-footer__cta">Записаться онлайн</a>
            </div>

        </div>

        <div class="site-footer__bottom">
            <p>&copy; <?= date('Y') ?> Клиника. Все права защищены.</p>
            <p>Информация на сайте носит ознакомительный характер и не заменяет консультацию врача.</p>
        </div>
    </div>
</footer>

<script src="<?= BASE_URL ?>/js/app.js"></script>
</body>
</html>
