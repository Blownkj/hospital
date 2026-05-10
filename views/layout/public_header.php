<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Клиника', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/main.css">
</head>
<body>

<a class="u-skip-link" href="#main">Перейти к содержимому</a>

<?php
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$path = substr($uri, strlen($base));
?>

<header class="navbar" role="banner">
    <div class="navbar__container">

        <a href="<?= BASE_URL ?>/" class="navbar__brand" aria-label="Клиника — на главную">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6 6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/>
                <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4"/>
                <circle cx="18" cy="11.5" r="2.5"/>
            </svg>
            <span class="navbar__brand-name">Клиника</span>
        </a>

        <input type="checkbox" id="navbar-toggle" class="navbar__toggle-input" aria-hidden="true">
        <label for="navbar-toggle" class="navbar__burger" aria-label="Открыть меню">
            <span></span><span></span><span></span>
        </label>

        <nav class="navbar__nav" aria-label="Основная навигация">
            <ul class="navbar__list">
                <li><a href="<?= BASE_URL ?>/"
                       class="navbar__link<?= $path === '/' ? ' navbar__link--active' : '' ?>"
                       <?= $path === '/' ? 'aria-current="page"' : '' ?>>Главная</a></li>
                <li><a href="<?= BASE_URL ?>/about"
                       class="navbar__link<?= $path === '/about' ? ' navbar__link--active' : '' ?>"
                       <?= $path === '/about' ? 'aria-current="page"' : '' ?>>О клинике</a></li>
                <li><a href="<?= BASE_URL ?>/faq"
                       class="navbar__link<?= $path === '/faq' ? ' navbar__link--active' : '' ?>"
                       <?= $path === '/faq' ? 'aria-current="page"' : '' ?>>FAQ</a></li>
                <li><a href="<?= BASE_URL ?>/doctors"
                       class="navbar__link<?= str_starts_with($path, '/doctors') ? ' navbar__link--active' : '' ?>"
                       <?= str_starts_with($path, '/doctors') ? 'aria-current="page"' : '' ?>>Врачи</a></li>
                <li><a href="<?= BASE_URL ?>/services"
                       class="navbar__link<?= str_starts_with($path, '/services') ? ' navbar__link--active' : '' ?>"
                       <?= str_starts_with($path, '/services') ? 'aria-current="page"' : '' ?>>Услуги и цены</a></li>
                <li><a href="<?= BASE_URL ?>/articles"
                       class="navbar__link<?= str_starts_with($path, '/articles') ? ' navbar__link--active' : '' ?>"
                       <?= str_starts_with($path, '/articles') ? 'aria-current="page"' : '' ?>>Статьи</a></li>
                <li><a href="<?= BASE_URL ?>/contact"
                       class="navbar__link<?= str_starts_with($path, '/contact') ? ' navbar__link--active' : '' ?>"
                       <?= str_starts_with($path, '/contact') ? 'aria-current="page"' : '' ?>>Контакты</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                <li>
                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($_SESSION['user_role'] ?? '', ENT_QUOTES, 'UTF-8') ?>/dashboard"
                       class="navbar__cta">Личный кабинет</a>
                </li>
                <?php else: ?>
                <li><a href="<?= BASE_URL ?>/login" class="navbar__link">Войти</a></li>
                <li><a href="<?= BASE_URL ?>/register" class="navbar__cta">Записаться</a></li>
                <?php endif; ?>
            </ul>
        </nav>

    </div>
</header>

<main id="main">
