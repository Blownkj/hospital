<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<?php if ($flash): ?>
    <div class="alert alert--success" role="alert">
        <span class="alert__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
            </svg>
        </span>
        <span class="alert__body"><?= View::e($flash) ?></span>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert--error" role="alert">
        <span class="alert__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
            </svg>
        </span>
        <span class="alert__body"><?= View::e($error) ?></span>
    </div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            Добро пожаловать,
            <?= View::e($patient['first_name']) ?>!
        </h1>
        <p class="u-text-muted u-text-sm"><?= View::e($patient['email']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/patient/book" class="btn btn--primary">
        <?php icon('calendar-plus', 16) ?> Записаться к врачу
    </a>
</div>

<?php if ($patient['chronic_diseases']): ?>
    <div class="alert alert--warning u-mb-6" role="alert">
        <span class="alert__icon"><?php icon('clipboard-list', 18) ?></span>
        <span class="alert__body">
            <strong>Хронические заболевания (видны вашему врачу):</strong><br>
            <?= View::e($patient['chronic_diseases']) ?>
        </span>
    </div>
<?php endif; ?>

<!-- Предстоящие записи -->
<div class="card u-mb-6">
    <div class="card__body">
        <h2 class="card__title u-mb-4">
            <?php icon('calendar', 18) ?> Предстоящие записи
        </h2>

        <?php if (empty($upcoming)): ?>
            <p class="u-text-muted u-p-4 u-text-center">
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
                            <?php icon('calendar', 14) ?>
                            <?= date('d.m.Y', strtotime($appt['scheduled_at'])) ?>
                            <?php icon('clock', 14) ?>
                            <?= date('H:i', strtotime($appt['scheduled_at'])) ?>
                        </div>
                    </div>
                    <?php if ($appt['status'] === 'confirmed'): ?>
                        <span class="badge badge--success">
                            <span class="badge__dot" aria-hidden="true"></span>
                            Подтверждена
                        </span>
                    <?php else: ?>
                        <span class="badge badge--warning">
                            <span class="badge__dot" aria-hidden="true"></span>
                            Ожидает
                        </span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <p class="u-mt-3">
                <a href="<?= BASE_URL ?>/patient/appointments" class="u-text-sm">Все мои записи →</a>
            </p>
        <?php endif; ?>
    </div>
</div>

<!-- Быстрые действия -->
<div class="dash-grid">
    <a href="<?= BASE_URL ?>/patient/book" class="dash-widget">
        <div class="dash-widget__icon"><?php icon('calendar', 28) ?></div>
        <div class="dash-widget__label">Записаться к врачу</div>
        <div class="dash-widget__sub">Выбрать специалиста и время</div>
    </a>
    <a href="<?= BASE_URL ?>/patient/book/analysis" class="dash-widget">
        <div class="dash-widget__icon"><?php icon('flask-conical', 28) ?></div>
        <div class="dash-widget__label">Сдать анализы</div>
        <div class="dash-widget__sub">ОАК, ТТГ, биохимия и др.</div>
    </a>
    <a href="<?= BASE_URL ?>/patient/appointments" class="dash-widget">
        <div class="dash-widget__icon"><?php icon('clipboard-list', 28) ?></div>
        <div class="dash-widget__label">Мои записи</div>
        <div class="dash-widget__sub">История и статусы</div>
    </a>
    <a href="<?= BASE_URL ?>/patient/medical-record" class="dash-widget">
        <div class="dash-widget__icon"><?php icon('file-text', 28) ?></div>
        <div class="dash-widget__label">Медицинская карта</div>
        <div class="dash-widget__sub">История визитов и результатов</div>
    </a>
    <a href="<?= BASE_URL ?>/patient/profile" class="dash-widget">
        <div class="dash-widget__icon"><?php icon('user-round', 28) ?></div>
        <div class="dash-widget__label">Мой профиль</div>
        <div class="dash-widget__sub">Редактировать данные</div>
    </a>
    <a href="<?= BASE_URL ?>/patient/reviews" class="dash-widget">
        <div class="dash-widget__icon"><?php icon('star', 28) ?></div>
        <div class="dash-widget__label">Отзывы</div>
        <div class="dash-widget__sub">Оценить врача</div>
    </a>
    <form method="POST" action="<?= BASE_URL ?>/logout" class="dash-widget-form">
        <input type="hidden" name="csrf_token" value="<?= View::e(App\Core\Session::generateCsrfToken()) ?>">
        <button type="submit" class="dash-widget danger">
            <div class="dash-widget__icon"><?php icon('log-out', 28) ?></div>
            <div class="dash-widget__label">Выйти</div>
            <div class="dash-widget__sub">Завершить сессию</div>
        </button>
    </form>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
