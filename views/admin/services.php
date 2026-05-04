<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="page-header">
    <div>
        <a href="<?= BASE_URL ?>/admin/dashboard" class="back-link">← Панель администратора</a>
        <h1 class="page-title">Услуги и прайс-лист</h1>
    </div>
    <a href="<?= BASE_URL ?>/admin/lab-tests" class="btn" style="border:1px solid #dde0e8">
        Анализы
    </a>
</div>

<?php if ($flash): ?>
    <div class="alert alert-success">✅ <?= View::e($flash) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= View::e($error) ?></div>
<?php endif; ?>

<!-- Форма добавления -->
<div class="card">
    <div class="card-title">Добавить услугу</div>
    <form method="POST" action="<?= BASE_URL ?>/admin/services/create">
        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;margin-bottom:12px">
            <div class="form-group" style="margin:0">
                <label>Название *</label>
                <input class="form-control" type="text" name="name"
                       required placeholder="Первичный приём терапевта">
            </div>
            <div class="form-group" style="margin:0">
                <label>Цена, ₽ *</label>
                <input class="form-control" type="number" name="price"
                       required min="1" step="0.01" placeholder="1500">
            </div>
            <div class="form-group" style="margin:0">
                <label>Специализация</label>
                <select class="form-control" name="specialization_id">
                    <option value="0">— Общая —</option>
                    <?php foreach ($specs as $spec): ?>
                        <option value="<?= (int)$spec['id'] ?>">
                            <?= View::e($spec['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Описание</label>
            <input class="form-control" type="text" name="description"
                   placeholder="Краткое описание услуги">
        </div>
        <button type="submit" class="btn btn-primary">Добавить услугу</button>
    </form>
</div>

<!-- Список услуг -->
<div class="card" style="padding:0">
    <table style="width:100%;border-collapse:collapse;font-size:14px">
        <thead>
            <tr style="background:var(--color-background-secondary,#f7f8fa)">
                <th style="padding:12px 16px;text-align:left;font-weight:500;
                           border-bottom:1px solid #e8e8f0">Услуга</th>
                <th style="padding:12px 16px;text-align:left;font-weight:500;
                           border-bottom:1px solid #e8e8f0">Специализация</th>
                <th style="padding:12px 16px;text-align:right;font-weight:500;
                           border-bottom:1px solid #e8e8f0">Цена</th>
                <th style="padding:12px 16px;text-align:right;font-weight:500;
                           border-bottom:1px solid #e8e8f0">Действия</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($services as $svc): ?>
            <tr id="row-<?= (int)$svc['id'] ?>">
                <!-- Обычный вид -->
                <td style="padding:12px 16px;border-bottom:1px solid #f0f0f5">
                    <div style="font-weight:500"><?= View::e($svc['name']) ?></div>
                    <?php if ($svc['description']): ?>
                        <div style="font-size:12px;color:#888;margin-top:2px">
                            <?= View::e($svc['description']) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td style="padding:12px 16px;border-bottom:1px solid #f0f0f5;color:#888">
                    <?= View::e($svc['specialization_name'] ?? '—') ?>
                </td>
                <td style="padding:12px 16px;border-bottom:1px solid #f0f0f5;
                           text-align:right;font-weight:600">
                    <?= number_format((float)$svc['price'], 0, '.', ' ') ?> ₽
                </td>
                <td style="padding:12px 16px;border-bottom:1px solid #f0f0f5;
                           text-align:right;white-space:nowrap">
                    <button onclick="toggleEdit(<?= (int)$svc['id'] ?>)"
                            class="btn btn-sm"
                            style="border:1px solid #dde0e8;margin-right:4px">
                        ✏️ Изменить
                    </button>
                    <form method="POST"
                          action="<?= BASE_URL ?>/admin/services/<?= (int)$svc['id'] ?>/delete"
                          style="display:inline"
                          onsubmit="return confirm('Удалить услугу?')">
                        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                    </form>
                </td>
            </tr>
            <!-- Строка редактирования (скрыта по умолчанию) -->
            <tr id="edit-<?= (int)$svc['id'] ?>" style="display:none;background:#fafbff">
                <td colspan="4" style="padding:16px;border-bottom:1px solid #e8e8f0">
                    <form method="POST"
                          action="<?= BASE_URL ?>/admin/services/<?= (int)$svc['id'] ?>/update">
                        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;margin-bottom:12px">
                            <div class="form-group" style="margin:0">
                                <label>Название</label>
                                <input class="form-control" type="text" name="name"
                                       required value="<?= View::e($svc['name']) ?>">
                            </div>
                            <div class="form-group" style="margin:0">
                                <label>Цена, ₽</label>
                                <input class="form-control" type="number" name="price"
                                       required min="1" step="0.01"
                                       value="<?= (float)$svc['price'] ?>">
                            </div>
                            <div class="form-group" style="margin:0">
                                <label>Специализация</label>
                                <select class="form-control" name="specialization_id">
                                    <option value="0">— Общая —</option>
                                    <?php foreach ($specs as $spec): ?>
                                        <option value="<?= (int)$spec['id'] ?>"
                                            <?= (int)$spec['id'] === (int)$svc['specialization_id']
                                                ? 'selected' : '' ?>>
                                            <?= View::e($spec['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Описание</label>
                            <input class="form-control" type="text" name="description"
                                   value="<?= View::e($svc['description'] ?? '') ?>">
                        </div>
                        <div style="display:flex;gap:8px">
                            <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
                            <button type="button" class="btn btn-sm"
                                    style="border:1px solid #dde0e8"
                                    onclick="toggleEdit(<?= (int)$svc['id'] ?>)">
                                Отмена
                            </button>
                        </div>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function toggleEdit(id) {
    const editRow = document.getElementById('edit-' + id);
    editRow.style.display = editRow.style.display === 'none' ? 'table-row' : 'none';
}
</script>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>