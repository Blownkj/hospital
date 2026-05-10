<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';

$specs  = $specs  ?? [];
$query  = $query  ?? '';
$specId = (int)($specId ?? 0);
$doctors = $doctors ?? [];
?>

<a href="<?= BASE_URL ?>/admin/dashboard" class="back-link">← Дашборд</a>

<?php include ROOT_PATH . '/views/partials/flash.php'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Врачи</h1>
        <span class="page-subtitle">Найдено: <?= count($doctors) ?></span>
    </div>
    <a class="btn btn--primary btn--sm" href="<?= BASE_URL ?>/admin/doctors/create">
        + Добавить врача
    </a>
</div>

<!-- Поиск и фильтр -->
<div class="card u-mb-4">
    <div class="card__body">
        <form method="GET" action="<?= BASE_URL ?>/admin/doctors" class="form-row">
            <input class="form__control u-flex-1 search-input" type="text" name="q"
                   placeholder="Поиск по имени, email или специализации..."
                   value="<?= View::e($query) ?>">
            <select class="form__control search-spec" name="spec">
                <option value="0">Все специализации</option>
                <?php foreach ($specs as $spec): ?>
                    <option value="<?= (int)$spec['id'] ?>"
                        <?= (int)$spec['id'] === $specId ? 'selected' : '' ?>>
                        <?= View::e($spec['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn--primary btn--sm">
                <?php icon('search', 14) ?> Найти
            </button>
            <?php if ($query || $specId): ?>
                <a href="<?= BASE_URL ?>/admin/doctors" class="btn btn--ghost btn--sm">Сбросить</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Результаты -->
<?php if (empty($doctors)): ?>
    <?php
    $emptyMessage = 'Врачи не найдены';
    $emptyLinkUrl = BASE_URL . '/admin/doctors';
    $emptyLinkText = 'Сбросить фильтр';
    include ROOT_PATH . '/views/partials/empty-state.php';
    ?>
<?php else: ?>
    <div class="card card--flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Email</th>
                    <th>Специализация</th>
                    <th>Статус</th>
                    <th class="td-actions td-w260">Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($doctors as $d):
                    $isActive = (bool)($d['is_active'] ?? 1);
                ?>
                    <tr>
                        <td>
                            <div class="u-fw-semibold"><?= View::e($d['full_name'] ?? '') ?></div>
                            <?php if (!empty($d['bio'] ?? '')): ?>
                                <div class="u-text-xs u-text-muted u-mt-2">
                                    <?= View::e(mb_strimwidth((string)($d['bio'] ?? ''), 0, 120, '...')) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?= View::e($d['email'] ?? '—') ?></td>
                        <td><?= View::e($d['specialization'] ?? '—') ?></td>
                        <td>
                            <?php if ($isActive): ?>
                                <span class="badge badge--success">Активен</span>
                            <?php else: ?>
                                <span class="badge badge--danger">Отключён</span>
                            <?php endif; ?>
                        </td>
                        <td class="td-actions">
                            <a class="btn btn--ghost btn--sm"
                               href="<?= BASE_URL ?>/admin/doctors/<?= (int)$d['id'] ?>/edit">
                                <?php icon('settings', 13) ?> Редактировать
                            </a>
                            <?php if ($isActive): ?>
                                <form method="POST"
                                      action="<?= BASE_URL ?>/admin/doctors/<?= (int)$d['id'] ?>/deactivate"
                                      class="u-inline-block u-ms-1">
                                    <input type="hidden" name="csrf_token" value="<?= View::e($csrf ?? '') ?>">
                                    <button class="btn btn--danger btn--sm" type="submit">Деактивировать</button>
                                </form>
                            <?php else: ?>
                                <form method="POST"
                                      action="<?= BASE_URL ?>/admin/doctors/<?= (int)$d['id'] ?>/activate"
                                      class="u-inline-block u-ms-1">
                                    <input type="hidden" name="csrf_token" value="<?= View::e($csrf ?? '') ?>">
                                    <button class="btn btn--primary btn--sm" type="submit">Активировать</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
