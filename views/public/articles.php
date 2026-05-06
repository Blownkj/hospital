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

<div class="page-header">
    <h1 class="page-title">Статьи о здоровье</h1>
    <span class="page-subtitle"><?= count($articles) ?> материалов</span>
</div>

<!-- Фильтр по категориям -->
<div class="filter-chips">
    <a href="<?= BASE_URL ?>/articles"
       class="filter-chip <?= $filter === '' ? 'filter-chip--active' : '' ?>">
        Все
    </a>
    <?php foreach ($categories as $cat): ?>
        <a href="<?= BASE_URL ?>/articles?category=<?= urlencode($cat) ?>"
           class="filter-chip <?= $filter === $cat ? 'filter-chip--active' : '' ?>">
            <?php icon($catIconMap[$cat] ?? 'clipboard-list', 14) ?>
            <?= View::e($cat) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Сетка статей -->
<div class="articles-grid">
    <?php foreach ($articles as $article): ?>
        <a href="<?= BASE_URL ?>/articles/<?= View::e($article['slug']) ?>" class="article-card">
            <div class="article-card__cat">
                <?php icon($catIconMap[$article['category']] ?? 'clipboard-list', 12) ?>
                <?= View::e($article['category']) ?>
            </div>
            <h2 class="article-card__title"><?= View::e($article['title']) ?></h2>
            <p class="article-card__excerpt"><?= View::e($article['excerpt']) ?></p>
            <div class="article-card__meta">
                <?php icon('clock', 12) ?>
                <?= (int)$article['read_time'] ?> мин чтения
                <span class="article-card__read-more">Читать →</span>
            </div>
        </a>
    <?php endforeach; ?>

    <?php if (empty($articles)): ?>
        <div class="grid-full">
            <?php
            $emptyMessage = 'Статей в этой категории нет';
            include ROOT_PATH . '/views/partials/empty-state.php';
            ?>
        </div>
    <?php endif; ?>
</div>

<!-- CTA -->
<div class="cta-banner">
    <p class="cta-banner__title">Есть вопросы? Запишитесь на консультацию</p>
    <p class="cta-banner__lead">Наши специалисты ответят на все вопросы и подберут подходящее лечение</p>
    <div class="cta-banner__actions">
        <a href="<?= BASE_URL ?>/register" class="btn btn--white btn--lg">Записаться на приём</a>
        <a href="<?= BASE_URL ?>/doctors"  class="btn btn--outline-white btn--lg">Наши врачи</a>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
