<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-success">✅ <?= View::e($flash) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= View::e($error) ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            Добро пожаловать,
            <?= View::e(explode(' ', $patient['full_name'])[1] ?? $patient['full_name']) ?>!
        </h1>
        <p class="text-muted"><?= View::e($patient['email']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/patient/book" class="btn btn-primary">+ Записаться к врачу</a>
</div>

<?php if ($patient['chronic_diseases']): ?>
    <div class="alert alert-warning">
        <strong>📋 Хронические заболевания (видны вашему врачу):</strong><br>
        <?= View::e($patient['chronic_diseases']) ?>
    </div>
<?php endif; ?>

<!-- Предстоящие записи -->
<div class="card">
    <div class="card-title">📅 Предстоящие записи</div>

    <?php if (empty($upcoming)): ?>
        <p class="text-muted text-center" style="padding:20px 0">
            Нет предстоящих записей.
            <a href="<?= BASE_URL ?>/patient/book">Записаться к врачу →</a>
        </p>
    <?php else: ?>
        <?php foreach ($upcoming as $appt): ?>
            <div class="appt-row">
                <div class="appt-info">
                    <div class="appt-doctor">
                        <?= View::e($appt['doctor_name']) ?>
                        <span>— <?= View::e($appt['specialization']) ?></span>
                    </div>
                    <div class="appt-time">
                        📅 <?= date('d.m.Y', strtotime($appt['scheduled_at'])) ?>
                        &nbsp; 🕐 <?= date('H:i', strtotime($appt['scheduled_at'])) ?>
                    </div>
                </div>
                <span class="badge badge-<?= $appt['status'] === 'confirmed' ? 'confirmed' : 'pending' ?>">
                    <?= $appt['status'] === 'confirmed' ? '✓ Подтверждена' : '⏳ Ожидает' ?>
                </span>
            </div>
        <?php endforeach; ?>
        <p class="mt-2">
            <a href="<?= BASE_URL ?>/patient/appointments" style="font-size:13px">Все мои записи →</a>
        </p>
    <?php endif; ?>
</div>

<!-- Быстрые действия -->
<div class="dash-grid">
    <a href="<?= BASE_URL ?>/patient/book" class="dash-widget">
        <div class="dash-widget-icon">🗓️</div>
        <div class="dash-widget-label">Записаться к врачу</div>
        <div class="dash-widget-sub">Выбрать специалиста и время</div>
    </a>
    <a href="<?= BASE_URL ?>/patient/book/analysis" class="dash-widget">
        <div class="dash-widget-icon">🧪</div>
        <div class="dash-widget-label">Сдать анализы</div>
        <div class="dash-widget-sub">ОАК, ТТГ, биохимия и др.</div>
    </a>
    <a href="<?= BASE_URL ?>/patient/appointments" class="dash-widget">
        <div class="dash-widget-icon">📋</div>
        <div class="dash-widget-label">Мои записи</div>
        <div class="dash-widget-sub">История и статусы</div>
    </a>
    <a href="<?= BASE_URL ?>/patient/medical-record" class="dash-widget">
        <div class="dash-widget-icon">📄</div>
        <div class="dash-widget-label">Медицинская карта</div>
        <div class="dash-widget-sub">История визитов и результатов</div>
    </a>
    <a href="<?= BASE_URL ?>/patient/profile" class="dash-widget">
        <div class="dash-widget-icon">👤</div>
        <div class="dash-widget-label">Мой профиль</div>
        <div class="dash-widget-sub">Редактировать данные</div>
    </a>
    <a href="<?= BASE_URL ?>/patient/reviews" class="dash-widget">
        <div class="dash-widget-icon">⭐</div>
        <div class="dash-widget-label">Отзывы</div>
        <div class="dash-widget-sub">Оценить врача</div>
    </a>
    <a href="<?= BASE_URL ?>/logout" class="dash-widget danger">
        <div class="dash-widget-icon">🚪</div>
        <div class="dash-widget-label">Выйти</div>
        <div class="dash-widget-sub">Завершить сессию</div>
    </a>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>