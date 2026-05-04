<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';

$specs  = $specs  ?? [];
$query  = $query  ?? '';
$specId = (int)($specId ?? 0);
$doctors = $doctors ?? [];
?>

<a href="<?= BASE_URL ?>/admin/dashboard" class="back-link">← Дашборд</a>

<?php include ROOT_PATH . '/views/partials/flash.php'; ?>

<div class="page-header">
    <h1 class="page-title">Врачи</h1>
    <span class="text-muted" style="font-size:14px">
        Найдено: <?= count($doctors) ?>
    </span>
    <div style="margin-left:auto">
        <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/admin/doctors/create">＋ Добавить врача</a>
    </div>
</div>

<!-- Поиск и фильтр -->
<form method="GET" action="<?= BASE_URL ?>/admin/doctors" style="margin-bottom:18px">
    <div style="display:flex;gap:12px;flex-wrap:wrap">
        <input
            type="text"
            name="q"
            class="form-control"
            placeholder="Поиск по имени, email или специализации..."
            value="<?= View::e($query) ?>"
            style="flex:1;min-width:220px"
        >
        <select name="spec" class="form-control" style="width:220px">
            <option value="0">Все специализации</option>
            <?php foreach ($specs as $spec): ?>
                <option value="<?= (int)$spec['id'] ?>"
                    <?= (int)$spec['id'] === $specId ? 'selected' : '' ?>>
                    <?= View::e($spec['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Найти</button>
        <?php if ($query || $specId): ?>
            <a href="<?= BASE_URL ?>/admin/doctors" class="btn" style="border:1px solid #dde0e8">
                Сбросить
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- Результаты -->
<?php if (empty($doctors)): ?>
    <?php
    $emptyIcon    = '🔍';
    $emptyMessage = 'Врачи не найдены';
    $emptyLinkUrl = BASE_URL . '/admin/doctors';
    $emptyLinkText = 'Сбросить фильтр';
    include ROOT_PATH . '/views/partials/empty-state.php';
    ?>
<?php else: ?>
    <div class="card" style="padding:0;overflow:hidden">
        <table class="table" style="width:100%;border-collapse:collapse">
            <thead>
            <tr style="background:#f6f8fb">
                <th style="text-align:left;padding:10px 12px;font-size:12px;color:#666">ФИО</th>
                <th style="text-align:left;padding:10px 12px;font-size:12px;color:#666">Email</th>
                <th style="text-align:left;padding:10px 12px;font-size:12px;color:#666">Специализация</th>
                <th style="text-align:left;padding:10px 12px;font-size:12px;color:#666">Статус</th>
                <th style="text-align:right;padding:10px 12px;font-size:12px;color:#666;width:260px">Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($doctors as $d): ?>
                <?php
                    $isActive = (($d['role'] ?? '') === 'doctor');
                ?>
                <tr style="border-top:1px solid #eef0f4">
                    <td style="padding:10px 12px">
                        <div style="font-weight:600"><?= View::e($d['full_name'] ?? '') ?></div>
                        <?php if (!empty($d['bio'] ?? '')): ?>
                            <div class="text-muted" style="font-size:12px;margin-top:2px">
                                <?= View::e(mb_strimwidth((string)($d['bio'] ?? ''), 0, 120, '...')) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 12px"><?= View::e($d['email'] ?? '—') ?></td>
                    <td style="padding:10px 12px"><?= View::e($d['specialization'] ?? '—') ?></td>
                    <td style="padding:10px 12px">
                        <?php if ($isActive): ?>
                            <span class="badge" style="background:#e8fff1;color:#0b7a3b;border:1px solid #baf2cf;padding:2px 8px;border-radius:999px;font-size:12px">Активен</span>
                        <?php else: ?>
                            <span class="badge" style="background:#fff1f1;color:#a30f0f;border:1px solid #ffd1d1;padding:2px 8px;border-radius:999px;font-size:12px">Отключён</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 12px;text-align:right;white-space:nowrap">
                        <a class="btn btn-sm" style="border:1px solid #dde0e8" href="<?= BASE_URL ?>/admin/doctors/<?= (int)$d['id'] ?>/edit">Редактировать</a>

                        <?php if ($isActive): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/doctors/<?= (int)$d['id'] ?>/deactivate" style="display:inline-block;margin-left:6px">
                                <input type="hidden" name="csrf_token" value="<?= View::e($csrf ?? '') ?>">
                                <button class="btn btn-sm" style="border:1px solid #ffd1d1;color:#a30f0f;background:#fff" type="submit">Деактивировать</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/doctors/<?= (int)$d['id'] ?>/activate" style="display:inline-block;margin-left:6px">
                                <input type="hidden" name="csrf_token" value="<?= View::e($csrf ?? '') ?>">
                                <button class="btn btn-primary btn-sm" type="submit">Активировать</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>