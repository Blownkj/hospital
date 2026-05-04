<?php
$pageTitle = 'Ошибка сервера — Клиника';
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div style="text-align:center; padding: 80px 24px 100px;">
    <div style="font-size: 120px; font-weight: 800; color: #fee2e2; line-height: 1; user-select: none;">
        500
    </div>
    <h1 style="font-size: 26px; font-weight: 700; color: #1a1a2e; margin-top: 8px;">
        Внутренняя ошибка сервера
    </h1>
    <p style="color: #888; font-size: 15px; margin-top: 12px; max-width: 440px; margin-left: auto; margin-right: auto;">
        Что-то пошло не так на нашей стороне. Мы уже работаем над исправлением.
        Пожалуйста, попробуйте немного позже.
    </p>

    <div style="margin-top: 36px; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/" class="btn btn-primary">На главную</a>
        <a href="javascript:history.back()" class="btn" style="background:#f0f4ff; color:#4a90e2;">Вернуться назад</a>
    </div>

    <div style="margin-top: 48px; display: inline-block; background: #fff; border: 1px solid #e8e8f0; border-radius: 14px; padding: 20px 32px; text-align: left;">
        <p style="font-size: 13px; color: #555; margin: 0;">
            Если проблема повторяется, свяжитесь с нами:
        </p>
        <a href="<?= BASE_URL ?>/contact" style="font-size: 14px; color: #4a90e2; font-weight: 500; margin-top: 6px; display: inline-block;">
            Написать в поддержку
        </a>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
