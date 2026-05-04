<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Клиника', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/main.css">
</head>
<body>

<nav class="navbar">
    <a href="<?= BASE_URL ?>/" class="navbar-brand">🏥 Клиника</a>
    <ul class="navbar-nav">
        <?php
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $path = substr($uri, strlen($base));
        ?>
        <li><a href="<?= BASE_URL ?>/"
               <?= $path === '/' ? 'class="active"' : '' ?>>Главная</a></li>
        <li><a href="<?= BASE_URL ?>/about">О клинике</a></li>
        <li><a href="<?= BASE_URL ?>/faq">FAQ</a></li>
        <li><a href="<?= BASE_URL ?>/doctors"
               <?= str_starts_with($path, '/doctors') ? 'class="active"' : '' ?>>Врачи</a></li>
        <li><a href="<?= BASE_URL ?>/services"
               <?= str_starts_with($path, '/services') ? 'class="active"' : '' ?>>Услуги и цены</a></li>
        <li><a href="<?= BASE_URL ?>/articles"
               <?= str_starts_with($path, '/articles') ? 'class="active"' : '' ?>>Статьи</a></li>
        <li><a href="<?= BASE_URL ?>/contact"
               <?= str_starts_with($path, '/contact') ? 'class="active"' : '' ?>>Контакты</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li>
                <a href="<?= BASE_URL ?>/<?= $_SESSION['user_role'] ?>/dashboard" class="btn-nav">
                    Личный кабинет
                </a>
            </li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>/login">Войти</a></li>
                <li><a href="<?= BASE_URL ?>/register" class="btn-nav">Записаться</a></li>
            <?php endif; ?>
    </ul>
</nav>

<main class="main">