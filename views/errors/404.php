<?php
$pageTitle = 'Страница не найдена — Клиника';
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div style="text-align:center; padding: 80px 24px 100px;">
    <div style="font-size: 120px; font-weight: 800; color: #e8eaf0; line-height: 1; user-select: none;">
        404
    </div>
    <h1 style="font-size: 26px; font-weight: 700; color: #1a1a2e; margin-top: 8px;">
        Страница не найдена
    </h1>
    <p style="color: #888; font-size: 15px; margin-top: 12px; max-width: 420px; margin-left: auto; margin-right: auto;">
        Возможно, страница была удалена, перемещена или вы перешли по неверной ссылке.
    </p>

    <div style="margin-top: 36px; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/" class="btn btn-primary">На главную</a>
        <a href="<?= BASE_URL ?>/doctors" class="btn" style="background:#f0f4ff; color:#4a90e2;">Найти врача</a>
        <a href="<?= BASE_URL ?>/contact" class="btn" style="background:#f0f4ff; color:#4a90e2;">Связаться с нами</a>
    </div>

    <div style="margin-top: 60px; display: flex; gap: 32px; justify-content: center; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/services" style="color: #888; font-size: 14px;">Услуги и цены</a>
        <a href="<?= BASE_URL ?>/about" style="color: #888; font-size: 14px;">О клинике</a>
        <a href="<?= BASE_URL ?>/faq" style="color: #888; font-size: 14px;">FAQ</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>/<?= $_SESSION['user_role'] ?>/dashboard" style="color: #888; font-size: 14px;">Личный кабинет</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login" style="color: #888; font-size: 14px;">Войти</a>
        <?php endif; ?>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
