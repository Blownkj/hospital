<?php
$pageTitle = 'Страница не найдена — Клиника';
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="error-page">
    <div class="error-page__code error-page__code--404">404</div>
    <h1 class="error-page__title">Страница не найдена</h1>
    <p class="error-page__text">
        Возможно, страница была удалена, перемещена или вы перешли по неверной ссылке.
    </p>

    <div class="error-page__actions">
        <a href="<?= BASE_URL ?>/" class="btn btn--primary">На главную</a>
        <a href="<?= BASE_URL ?>/doctors" class="btn btn--secondary">Найти врача</a>
        <a href="<?= BASE_URL ?>/contact" class="btn btn--secondary">Связаться с нами</a>
    </div>

    <div class="error-page__links">
        <a href="<?= BASE_URL ?>/services" class="error-page__link">Услуги и цены</a>
        <a href="<?= BASE_URL ?>/about"    class="error-page__link">О клинике</a>
        <a href="<?= BASE_URL ?>/faq"      class="error-page__link">FAQ</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>/<?= $_SESSION['user_role'] ?>/dashboard" class="error-page__link">Личный кабинет</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login" class="error-page__link">Войти</a>
        <?php endif; ?>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
