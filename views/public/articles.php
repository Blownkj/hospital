<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';

$categoryIcons = [
    'Подготовка к обследованию' => '🔬',
    'Нормы и показатели'        => '📊',
    'Когда к врачу'             => '🩺',
    'Первая помощь'             => '🚑',
    'Профилактика'              => '🛡️',
    'Общее'                     => '📋',
];
?>

<div class="page-header">
    <h1 class="page-title">Статьи о здоровье</h1>
    <span style="color:#888;font-size:14px"><?= count($articles) ?> материалов</span>
</div>

<!-- Фильтр по категориям -->
<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:32px">
    <a href="<?= BASE_URL ?>/articles"
       class="btn btn-sm"
       style="<?= $filter === '' ? 'background:#4a90e2;color:#fff' : 'background:#f0f4ff;color:#4a90e2' ?>">
        Все
    </a>
    <?php foreach ($categories as $cat): ?>
        <a href="<?= BASE_URL ?>/articles?category=<?= urlencode($cat) ?>"
           class="btn btn-sm"
           style="<?= $filter === $cat ? 'background:#4a90e2;color:#fff' : 'background:#f0f4ff;color:#4a90e2' ?>">
            <?= ($categoryIcons[$cat] ?? '📋') . ' ' . View::e($cat) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Сетка статей -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;margin-bottom:48px">
    <?php foreach ($articles as $article): ?>
        <a href="<?= BASE_URL ?>/articles/<?= View::e($article['slug']) ?>"
           style="text-decoration:none;color:inherit;display:flex;flex-direction:column"
           class="card">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                <span style="font-size:20px"><?= $categoryIcons[$article['category']] ?? '📋' ?></span>
                <span style="font-size:12px;color:#4a90e2;font-weight:500;background:#eef3fd;padding:3px 10px;border-radius:20px">
                    <?= View::e($article['category']) ?>
                </span>
            </div>
            <h2 style="font-size:16px;font-weight:600;margin-bottom:8px;line-height:1.4">
                <?= View::e($article['title']) ?>
            </h2>
            <p style="font-size:13px;color:#666;line-height:1.6;flex:1">
                <?= View::e($article['excerpt']) ?>
            </p>
            <div style="display:flex;align-items:center;gap:6px;margin-top:14px;font-size:12px;color:#aaa">
                <span>🕐 <?= (int)$article['read_time'] ?> мин чтения</span>
                <span style="margin-left:auto;color:#4a90e2;font-weight:500">Читать →</span>
            </div>
        </a>
    <?php endforeach; ?>

    <?php if (empty($articles)): ?>
        <div style="grid-column:1/-1;text-align:center;padding:60px 0;color:#aaa">
            Статей в этой категории нет.
        </div>
    <?php endif; ?>
</div>

<!-- CTA -->
<div style="background:linear-gradient(135deg,#4a90e2,#357abd);border-radius:16px;padding:40px;text-align:center;color:#fff;margin-bottom:0">
    <h2 style="font-size:22px;font-weight:700;margin-bottom:10px">Есть вопросы? Запишитесь на консультацию</h2>
    <p style="opacity:.85;margin-bottom:24px">Наши специалисты ответят на все вопросы и подберут подходящее лечение</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="<?= BASE_URL ?>/register" style="background:#fff;color:#4a90e2;font-weight:600;padding:12px 28px;border-radius:10px;text-decoration:none">
            Записаться на приём
        </a>
        <a href="<?= BASE_URL ?>/doctors" style="border:2px solid rgba(255,255,255,.6);color:#fff;padding:12px 28px;border-radius:10px;text-decoration:none">
            Наши врачи
        </a>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
