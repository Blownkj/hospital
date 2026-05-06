<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';

$catIconMap = [
    'Подготовка к обследованию' => 'microscope',
    'Нормы и показатели'        => 'activity',
    'Когда к врачу'             => 'stethoscope',
    'Первая помощь'             => 'alert-triangle',
    'Профилактика'              => 'shield-check',
    'Общее'                     => 'clipboard-list',
];
?>

<div class="article-wrap">

    <!-- Хлебные крошки -->
    <nav class="breadcrumb" aria-label="Навигация">
        <span class="breadcrumb__item">
            <a class="breadcrumb__link" href="<?= BASE_URL ?>/">Главная</a>
        </span>
        <span class="breadcrumb__sep" aria-hidden="true">›</span>
        <span class="breadcrumb__item">
            <a class="breadcrumb__link" href="<?= BASE_URL ?>/articles">Статьи</a>
        </span>
        <span class="breadcrumb__sep" aria-hidden="true">›</span>
        <span><?= View::e($article['title']) ?></span>
    </nav>

    <!-- Мета -->
    <div class="article-meta">
        <span class="article-meta__cat">
            <?php icon($catIconMap[$article['category']] ?? 'clipboard-list', 12) ?>
            <?= View::e($article['category']) ?>
        </span>
        <span class="article-meta__item">
            <?php icon('clock', 14) ?>
            <?= (int)$article['read_time'] ?> мин чтения
        </span>
        <span class="article-meta__item">
            <?= date('d.m.Y', strtotime($article['published_at'])) ?>
        </span>
    </div>

    <!-- Заголовок -->
    <h1 class="article-title">
        <?= View::e($article['title']) ?>
    </h1>
    <p class="article-lead"><?= View::e($article['excerpt']) ?></p>

    <!-- Тело статьи -->
    <div class="article-body">
        <?php
        // TODO P0.6: install ezyang/htmlpurifier and sanitize here.
        // Body is currently admin/seed-only HTML, no user-controlled input path exists.
        echo $article['body'];
        ?>
    </div>

    <!-- CTA -->
    <div class="cta-banner">
        <p class="cta-banner__title">Остались вопросы?</p>
        <p class="cta-banner__lead">Запишитесь на консультацию к нашему специалисту — мы поможем разобраться в вашей ситуации</p>
        <div class="cta-banner__actions">
            <a href="<?= BASE_URL ?>/register" class="btn btn--white btn--lg">Записаться на приём</a>
            <a href="<?= BASE_URL ?>/doctors"  class="btn btn--outline-white btn--lg">Наши врачи</a>
        </div>
    </div>

    <!-- Похожие статьи -->
    <?php if (!empty($related)): ?>
        <div class="section-title u-text-xl u-mt-12">Читайте также</div>
        <div class="articles-grid">
            <?php foreach ($related as $rel): ?>
                <a href="<?= BASE_URL ?>/articles/<?= View::e($rel['slug']) ?>" class="article-card">
                    <div class="article-card__cat">
                        <?php icon($catIconMap[$rel['category']] ?? 'clipboard-list', 12) ?>
                        <?= View::e($rel['category']) ?>
                    </div>
                    <div class="article-card__title"><?= View::e($rel['title']) ?></div>
                    <div class="article-card__meta">
                        <?php icon('clock', 12) ?>
                        <?= (int)$rel['read_time'] ?> мин
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Назад -->
    <div class="article-back">
        <a href="<?= BASE_URL ?>/articles" class="back-link">← Все статьи</a>
    </div>

</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
