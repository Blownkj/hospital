<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="hero">
    <h1>Ваше здоровье — наш приоритет</h1>
    <p>Опытные специалисты, современное оборудование, удобная запись онлайн</p>
    <div class="hero-btns">
        <a href="<?= BASE_URL ?>/register" class="btn-white">Записаться на приём</a>
        <a href="<?= BASE_URL ?>/doctors"  class="btn-outline">Наши врачи</a>
    </div>
</div>

<!-- Счётчики из БД -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:48px">
    <div class="feature-card">
        <div class="feature-icon">👥</div>
        <div style="font-size:28px;font-weight:700;color:#4a90e2;margin-bottom:4px">
            <?= number_format((int)($stats['patients'] ?? 0)) ?>
        </div>
        <div style="font-size:13px;color:#888">Пациентов</div>
    </div>
    <div class="feature-card">
        <div class="feature-icon">👨‍⚕️</div>
        <div style="font-size:28px;font-weight:700;color:#4a90e2;margin-bottom:4px">
            <?= number_format((int)($stats['doctors'] ?? 0)) ?>
        </div>
        <div style="font-size:13px;color:#888">Специалистов</div>
    </div>
    <div class="feature-card">
        <div class="feature-icon">⭐</div>
        <div style="font-size:28px;font-weight:700;color:#4a90e2;margin-bottom:4px">
            <?= number_format((int)($stats['reviews'] ?? 0)) ?>
        </div>
        <div style="font-size:13px;color:#888">Одобренных отзывов</div>
    </div>
</div>

<!-- Врачи (превью — первые 4) -->
<div class="section-title">Наши специалисты</div>
<div class="doctors-grid">
    <?php foreach (array_slice($doctors, 0, 4) as $doctor): ?>
        <?php $clickable = true; include ROOT_PATH . '/views/partials/doctor-card.php'; ?>
    <?php endforeach; ?>
</div>
<p style="text-align:center;margin-bottom:48px">
    <a href="<?= BASE_URL ?>/doctors" class="btn btn-primary btn-sm">Все специалисты →</a>
</p>

<!-- Почему мы -->
<div class="section-title">Почему выбирают нас</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:48px">
    <?php foreach ([
        ['🩺', 'Опытные специалисты',   'Врачи с опытом от 10 лет, кандидаты медицинских наук, регулярно повышающие квалификацию'],
        ['📅', 'Удобная онлайн-запись', 'Запись к врачу в любое время суток — без звонков и очередей. Напоминание за день до визита'],
        ['🔬', 'Диагностика на месте',  'Современное оборудование: УЗИ, ЭКГ, лабораторные анализы без направления в другие клиники'],
        ['💊', 'Доказательная медицина','Лечение по актуальным международным протоколам — без лишних назначений и устаревших схем'],
        ['🔒', 'Конфиденциальность',    'Медицинская карта доступна только вам и вашему врачу. Данные надёжно защищены'],
        ['⭐', 'Высокий рейтинг',       'Средняя оценка врачей нашей клиники — 4.9 из 5 по отзывам пациентов'],
    ] as [$icon, $title, $desc]): ?>
        <div class="card" style="margin-bottom:0;text-align:center;padding:28px 20px">
            <div style="font-size:36px;margin-bottom:12px"><?= $icon ?></div>
            <div style="font-size:15px;font-weight:600;margin-bottom:8px;color:#1a1a2e"><?= View::e($title) ?></div>
            <div style="font-size:13px;color:#777;line-height:1.6"><?= View::e($desc) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Наши специализации -->
<?php if (!empty($specializations)): ?>
<div class="section-title">Наши специализации</div>
<?php
$specIcons = [
    'Терапевт'    => '🩺',
    'Кардиолог'   => '❤️',
    'Невролог'    => '🧠',
    'Дерматолог'  => '🔬',
    'Хирург'      => '🔪',
    'Педиатр'     => '👶',
    'Эндокринолог'=> '⚗️',
    'Офтальмолог' => '👁️',
    'Ортопед'     => '🦴',
    'Гинеколог'   => '🌸',
];
?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:48px">
    <?php foreach ($specializations as $spec): ?>
        <a href="<?= BASE_URL ?>/doctors?spec=<?= (int)$spec['id'] ?>"
           style="text-decoration:none;color:inherit"
           class="card">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:0">
                <div style="font-size:28px;width:44px;height:44px;background:#eef3fd;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <?= $specIcons[$spec['name']] ?? '🏥' ?>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:600;color:#1a1a2e"><?= View::e($spec['name']) ?></div>
                    <?php if (!empty($spec['description'])): ?>
                        <div style="font-size:12px;color:#888;margin-top:2px;line-height:1.4">
                            <?= View::e(mb_strimwidth($spec['description'], 0, 50, '…')) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Последние статьи -->
<?php if (!empty($recentArticles)): ?>
<div class="section-title">Статьи о здоровье</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-bottom:16px">
    <?php
    $catIcons = [
        'Подготовка к обследованию' => '🔬',
        'Нормы и показатели'        => '📊',
        'Когда к врачу'             => '🩺',
        'Первая помощь'             => '🚑',
        'Профилактика'              => '🛡️',
        'Общее'                     => '📋',
    ];
    ?>
    <?php foreach ($recentArticles as $art): ?>
        <a href="<?= BASE_URL ?>/articles/<?= View::e($art['slug']) ?>"
           class="card" style="text-decoration:none;color:inherit;margin-bottom:0;display:flex;flex-direction:column">
            <div style="font-size:12px;color:#4a90e2;font-weight:500;margin-bottom:8px">
                <?= ($catIcons[$art['category']] ?? '📋') . ' ' . View::e($art['category']) ?>
            </div>
            <div style="font-size:15px;font-weight:600;margin-bottom:8px;line-height:1.4">
                <?= View::e($art['title']) ?>
            </div>
            <div style="font-size:13px;color:#777;flex:1;line-height:1.6">
                <?= View::e(mb_strimwidth($art['excerpt'], 0, 100, '…')) ?>
            </div>
            <div style="font-size:12px;color:#aaa;margin-top:12px">
                🕐 <?= (int)$art['read_time'] ?> мин · <span style="color:#4a90e2">Читать →</span>
            </div>
        </a>
    <?php endforeach; ?>
</div>
<p style="text-align:center;margin-bottom:48px">
    <a href="<?= BASE_URL ?>/articles" class="btn btn-sm" style="background:#f0f4ff;color:#4a90e2">Все статьи →</a>
</p>
<?php endif; ?>

<!-- Последние отзывы -->
<?php if (!empty($latestReviews)): ?>
<div class="section-title">Отзывы пациентов</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-bottom:48px">
    <?php foreach ($latestReviews as $review): ?>
        <div class="card" style="margin-bottom:0">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <div class="doctor-avatar"
                     style="width:40px;height:40px;font-size:14px;margin-bottom:0;flex-shrink:0">
                    <?= View::e(View::initials($review['patient_name'])) ?>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:500">
                        <?= View::e($review['patient_name']) ?>
                    </div>
                    <div style="font-size:12px;color:#aaa">
                        <?= View::e($review['doctor_name']) ?>
                        · <?= View::e($review['specialization']) ?>
                    </div>
                </div>
                <span class="stars" style="margin-left:auto">
                    <?= View::stars($review['rating']) ?>
                </span>
            </div>
            <?php if ($review['text']): ?>
                <p style="font-size:13px;color:#555;line-height:1.65">
                    «<?= View::e(mb_strimwidth($review['text'], 0, 160, '...')) ?>»
                </p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>