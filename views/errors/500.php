<?php
$pageTitle = 'Ошибка сервера — Клиника';
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="error-page">
    <div class="error-page__code error-page__code--500">500</div>
    <h1 class="error-page__title">Внутренняя ошибка сервера</h1>
    <p class="error-page__text">
        Что-то пошло не так на нашей стороне. Мы уже работаем над исправлением.
        Пожалуйста, попробуйте немного позже.
    </p>

    <div class="error-page__actions">
        <a href="<?= BASE_URL ?>/" class="btn btn--primary">На главную</a>
        <a href="javascript:history.back()" class="btn btn--secondary">Вернуться назад</a>
    </div>

    <div class="error-page__contact">
        <p class="error-page__contact-text">Если проблема повторяется, свяжитесь с нами:</p>
        <a href="<?= BASE_URL ?>/contact" class="error-page__contact-link">Написать в поддержку</a>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
