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

<div style="max-width:760px;margin:0 auto">

    <!-- Хлебные крошки -->
    <nav style="font-size:13px;color:#aaa;margin-bottom:20px">
        <a href="<?= BASE_URL ?>/" style="color:#4a90e2">Главная</a>
        <span style="margin:0 8px">›</span>
        <a href="<?= BASE_URL ?>/articles" style="color:#4a90e2">Статьи</a>
        <span style="margin:0 8px">›</span>
        <span><?= View::e($article['title']) ?></span>
    </nav>

    <!-- Мета -->
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px">
        <span style="font-size:13px;color:#4a90e2;font-weight:500;background:#eef3fd;padding:4px 12px;border-radius:20px">
            <?= ($categoryIcons[$article['category']] ?? '📋') . ' ' . View::e($article['category']) ?>
        </span>
        <span style="font-size:13px;color:#aaa">🕐 <?= (int)$article['read_time'] ?> мин чтения</span>
        <span style="font-size:13px;color:#aaa">
            <?= date('d.m.Y', strtotime($article['published_at'])) ?>
        </span>
    </div>

    <!-- Заголовок -->
    <h1 style="font-size:28px;font-weight:800;line-height:1.3;margin-bottom:12px;color:#1a1a2e">
        <?= View::e($article['title']) ?>
    </h1>
    <p style="font-size:16px;color:#666;line-height:1.7;margin-bottom:32px;border-left:4px solid #4a90e2;padding-left:16px">
        <?= View::e($article['excerpt']) ?>
    </p>

    <!-- Тело статьи -->
    <div class="article-body" style="font-size:15px;line-height:1.8;color:#333">
        <?= $article['body'] /* already trusted HTML from seeds */ ?>
    </div>

    <!-- CTA блок -->
    <div style="margin-top:48px;background:linear-gradient(135deg,#4a90e2,#357abd);border-radius:16px;padding:36px;color:#fff">
        <h2 style="font-size:20px;font-weight:700;margin-bottom:8px">
            Остались вопросы?
        </h2>
        <p style="opacity:.85;margin-bottom:24px;font-size:15px">
            Запишитесь на консультацию к нашему специалисту — мы поможем разобраться в вашей ситуации
        </p>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <a href="<?= BASE_URL ?>/register"
               style="background:#fff;color:#4a90e2;font-weight:600;padding:12px 28px;border-radius:10px;text-decoration:none;font-size:14px">
                Записаться на приём
            </a>
            <a href="<?= BASE_URL ?>/doctors"
               style="border:2px solid rgba(255,255,255,.6);color:#fff;padding:12px 28px;border-radius:10px;text-decoration:none;font-size:14px">
                Наши врачи
            </a>
        </div>
    </div>

    <!-- Похожие статьи -->
    <?php if (!empty($related)): ?>
        <div style="margin-top:48px">
            <div class="section-title" style="font-size:18px">Читайте также</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px">
                <?php foreach ($related as $rel): ?>
                    <a href="<?= BASE_URL ?>/articles/<?= View::e($rel['slug']) ?>"
                       class="card" style="text-decoration:none;color:inherit;margin-bottom:0">
                        <div style="font-size:12px;color:#4a90e2;font-weight:500;margin-bottom:8px">
                            <?= ($categoryIcons[$rel['category']] ?? '📋') . ' ' . View::e($rel['category']) ?>
                        </div>
                        <div style="font-size:14px;font-weight:600;line-height:1.4;margin-bottom:8px">
                            <?= View::e($rel['title']) ?>
                        </div>
                        <div style="font-size:12px;color:#aaa">
                            🕐 <?= (int)$rel['read_time'] ?> мин
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Назад -->
    <div style="margin-top:32px;padding-top:24px;border-top:1px solid #e8e8f0">
        <a href="<?= BASE_URL ?>/articles" style="font-size:14px;color:#4a90e2">← Все статьи</a>
    </div>

</div>

<style>
.article-body h2 { font-size: 19px; font-weight: 700; margin: 28px 0 12px; color: #1a1a2e; }
.article-body h3 { font-size: 16px; font-weight: 600; margin: 20px 0 8px; color: #1a1a2e; }
.article-body p  { margin-bottom: 14px; }
.article-body ul, .article-body ol { padding-left: 24px; margin-bottom: 14px; }
.article-body li { margin-bottom: 6px; }
.article-body table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px; }
.article-body th, .article-body td { padding: 10px 14px; border: 1px solid #e0e0f0; }
.article-body th { background: #f0f4ff; font-weight: 600; }
.article-body strong { color: #1a1a2e; }
</style>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
