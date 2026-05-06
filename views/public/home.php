<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<!-- Hero -->
<div class="hero">
    <h1 class="hero__title">Ваше здоровье — наш приоритет</h1>
    <p class="hero__lead">Опытные специалисты, современное оборудование, удобная запись онлайн</p>
    <div class="hero__actions">
        <a href="<?= BASE_URL ?>/register" class="btn btn--white btn--lg">Записаться на приём</a>
        <a href="<?= BASE_URL ?>/doctors"  class="btn btn--outline-white btn--lg">Наши врачи</a>
    </div>
</div>

<!-- Счётчики -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card__icon"><?php icon('users', 28) ?></div>
        <div class="stat-card__value"><?= number_format((int)($stats['patients'] ?? 0)) ?></div>
        <div class="stat-card__label">Пациентов</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon"><?php icon('stethoscope', 28) ?></div>
        <div class="stat-card__value"><?= number_format((int)($stats['doctors'] ?? 0)) ?></div>
        <div class="stat-card__label">Специалистов</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon"><?php icon('star', 28) ?></div>
        <div class="stat-card__value"><?= number_format((int)($stats['reviews'] ?? 0)) ?></div>
        <div class="stat-card__label">Одобренных отзывов</div>
    </div>
</div>

<!-- Врачи (превью) -->
<div class="section-title">Наши специалисты</div>
<div class="doctors-grid">
    <?php foreach (array_slice($doctors, 0, 4) as $doctor): ?>
        <?php $clickable = true; include ROOT_PATH . '/views/partials/doctor-card.php'; ?>
    <?php endforeach; ?>
</div>
<p class="u-text-center u-mb-12">
    <a href="<?= BASE_URL ?>/doctors" class="btn btn--primary btn--sm">Все специалисты</a>
</p>

<!-- Почему мы -->
<div class="section-title">Почему выбирают нас</div>
<div class="features-grid">
    <?php foreach ([
        ['stethoscope', 'Опытные специалисты',   'Врачи с опытом от 10 лет, кандидаты медицинских наук, регулярно повышающие квалификацию'],
        ['calendar',    'Удобная онлайн-запись',  'Запись к врачу в любое время суток — без звонков и очередей. Напоминание за день до визита'],
        ['microscope',  'Диагностика на месте',   'Современное оборудование: УЗИ, ЭКГ, лабораторные анализы без направления в другие клиники'],
        ['pill',        'Доказательная медицина', 'Лечение по актуальным международным протоколам — без лишних назначений и устаревших схем'],
        ['shield-check','Конфиденциальность',     'Медицинская карта доступна только вам и вашему врачу. Данные надёжно защищены'],
        ['star',        'Высокий рейтинг',        'Средняя оценка врачей нашей клиники — 4.9 из 5 по отзывам пациентов'],
    ] as [$iconName, $title, $desc]): ?>
        <div class="feature-card">
            <div class="feature-card__icon"><?php icon($iconName, 28) ?></div>
            <div class="feature-card__name"><?= View::e($title) ?></div>
            <div class="feature-card__text"><?= View::e($desc) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Специализации -->
<?php if (!empty($specializations)): ?>
<div class="section-title">Наши специализации</div>
<?php
$specIconMap = [
    'Терапевт'     => 'stethoscope',
    'Кардиолог'    => 'heart',
    'Невролог'     => 'brain',
    'Дерматолог'   => 'microscope',
    'Хирург'       => 'scissors',
    'Педиатр'      => 'user-round',
    'Эндокринолог' => 'flask-conical',
    'Офтальмолог'  => 'eye',
    'Ортопед'      => 'bone',
    'Гинеколог'    => 'flower',
];
?>
<div class="spec-grid">
    <?php foreach ($specializations as $spec): ?>
        <?php $iconName = $specIconMap[$spec['name']] ?? 'stethoscope'; ?>
        <a href="<?= BASE_URL ?>/doctors?spec=<?= (int)$spec['id'] ?>" class="spec-card">
            <div class="spec-card__icon"><?php icon($iconName, 20) ?></div>
            <div>
                <div class="spec-card__name"><?= View::e($spec['name']) ?></div>
                <?php if (!empty($spec['description'])): ?>
                    <div class="spec-card__desc"><?= View::e(mb_strimwidth($spec['description'], 0, 50, '…')) ?></div>
                <?php endif; ?>
            </div>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Статьи -->
<?php if (!empty($recentArticles)): ?>
<div class="section-title">Статьи о здоровье</div>
<?php
$catIconMap = [
    'Подготовка к обследованию' => 'microscope',
    'Нормы и показатели'        => 'activity',
    'Когда к врачу'             => 'stethoscope',
    'Первая помощь'             => 'alert-triangle',
    'Профилактика'              => 'shield-check',
    'Общее'                     => 'clipboard-list',
];
?>
<div class="articles-grid">
    <?php foreach ($recentArticles as $art): ?>
        <a href="<?= BASE_URL ?>/articles/<?= View::e($art['slug']) ?>" class="article-card">
            <div class="article-card__cat">
                <?php icon($catIconMap[$art['category']] ?? 'clipboard-list', 12) ?>
                <?= View::e($art['category']) ?>
            </div>
            <div class="article-card__title"><?= View::e($art['title']) ?></div>
            <div class="article-card__excerpt"><?= View::e(mb_strimwidth($art['excerpt'], 0, 100, '…')) ?></div>
            <div class="article-card__meta">
                <?php icon('clock', 12) ?>
                <?= (int)$art['read_time'] ?> мин
                <span class="article-card__read-more">Читать →</span>
            </div>
        </a>
    <?php endforeach; ?>
</div>
<p class="u-text-center u-mb-12">
    <a href="<?= BASE_URL ?>/articles" class="btn btn--ghost btn--sm">Все статьи</a>
</p>
<?php endif; ?>

<!-- Отзывы -->
<?php if (!empty($latestReviews)): ?>
<div class="section-title">Отзывы пациентов</div>
<div class="reviews-grid">
    <?php foreach ($latestReviews as $review): ?>
        <div class="review-card">
            <div class="review-card__header">
                <div class="review-card__avatar"><?= View::e(View::initials($review['patient_name'])) ?></div>
                <div class="review-card__info">
                    <div class="review-card__name"><?= View::e($review['patient_name']) ?></div>
                    <div class="review-card__doctor"><?= View::e($review['doctor_name']) ?> · <?= View::e($review['specialization']) ?></div>
                </div>
                <div class="review-card__stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                             class="<?= $i <= (int)$review['rating'] ? 'filled' : '' ?>" aria-hidden="true">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                    <?php endfor; ?>
                </div>
            </div>
            <?php if ($review['text']): ?>
                <p class="review-card__text">«<?= View::e(mb_strimwidth($review['text'], 0, 160, '...')) ?>»</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
