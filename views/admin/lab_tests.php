<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="page-header">
    <div>
        <a href="<?= BASE_URL ?>/admin/dashboard" class="back-link">← Панель администратора</a>
        <h1 class="page-title">Анализы и прайс-лист</h1>
    </div>
    <a href="<?= BASE_URL ?>/admin/services" class="btn" style="border:1px solid #dde0e8">
        Обычные услуги
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
    <div class="card-title">Добавить анализ</div>
    <form method="POST" action="<?= BASE_URL ?>/admin/lab-tests/create">
        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 80px;gap:12px;margin-bottom:12px">
            <div class="form-group" style="margin:0">
                <label>Название *</label>
                <input class="form-control" type="text" name="name"
                       required placeholder="Общий анализ крови">
            </div>
            <div class="form-group" style="margin:0">
                <label>Категория *</label>
                <input class="form-control" type="text" name="category"
                       required placeholder="Гематология">
            </div>
            <div class="form-group" style="margin:0">
                <label>Цена, ₽ *</label>
                <input class="form-control" type="number" name="price"
                       required min="1" step="0.01" placeholder="500">
            </div>
            <div class="form-group" style="margin:0">
                <label>Мин.</label>
                <input class="form-control" type="number" name="duration_min"
                       required min="1" value="15">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
            <div class="form-group" style="margin:0">
                <label>Описание</label>
                <input class="form-control" type="text" name="description"
                       placeholder="Краткое описание">
            </div>
            <div class="form-group" style="margin:0">
                <label>Подготовка к анализу</label>
                <input class="form-control" type="text" name="preparation"
                       placeholder="Натощак, не курить 2 ч.">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Добавить анализ</button>
    </form>
</div>

<!-- Список анализов -->
<div class="card" style="padding:0">
    <table style="width:100%;border-collapse:collapse;font-size:14px">
        <thead>
            <tr style="background:var(--color-background-secondary,#f7f8fa)">
                <th style="padding:12px 16px;text-align:left;font-weight:500;border-bottom:1px solid #e8e8f0">Анализ</th>
                <th style="padding:12px 16px;text-align:left;font-weight:500;border-bottom:1px solid #e8e8f0">Категория</th>
                <th style="padding:12px 16px;text-align:center;font-weight:500;border-bottom:1px solid #e8e8f0">Мин.</th>
                <th style="padding:12px 16px;text-align:right;font-weight:500;border-bottom:1px solid #e8e8f0">Цена</th>
                <th style="padding:12px 16px;text-align:right;font-weight:500;border-bottom:1px solid #e8e8f0">Действия</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tests as $t): ?>
            <tr id="lt-row-<?= (int)$t['id'] ?>">
                <td style="padding:12px 16px;border-bottom:1px solid #f0f0f5">
                    <div style="font-weight:500"><?= View::e($t['name']) ?></div>
                    <?php if ($t['description']): ?>
                        <div style="font-size:12px;color:#888;margin-top:2px"><?= View::e($t['description']) ?></div>
                    <?php endif; ?>
                    <?php if ($t['preparation']): ?>
                        <div style="font-size:11px;color:#aaa;margin-top:1px">
                            Подготовка: <?= View::e($t['preparation']) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td style="padding:12px 16px;border-bottom:1px solid #f0f0f5;color:#888">
                    <?= View::e($t['category']) ?>
                </td>
                <td style="padding:12px 16px;border-bottom:1px solid #f0f0f5;text-align:center;color:#888">
                    <?= (int)$t['duration_min'] ?>
                </td>
                <td style="padding:12px 16px;border-bottom:1px solid #f0f0f5;text-align:right;font-weight:600">
                    <?= number_format((float)$t['price'], 0, '.', ' ') ?> ₽
                </td>
                <td style="padding:12px 16px;border-bottom:1px solid #f0f0f5;text-align:right;white-space:nowrap">
                    <button onclick="ltToggleEdit(<?= (int)$t['id'] ?>)"
                            class="btn btn-sm"
                            style="border:1px solid #dde0e8;margin-right:4px">
                        ✏️ Изменить
                    </button>
                    <form method="POST"
                          action="<?= BASE_URL ?>/admin/lab-tests/<?= (int)$t['id'] ?>/delete"
                          style="display:inline"
                          onsubmit="return confirm('Удалить анализ?')">
                        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                    </form>
                </td>
            </tr>
            <!-- Строка редактирования -->
            <tr id="lt-edit-<?= (int)$t['id'] ?>" style="display:none;background:#fafbff">
                <td colspan="5" style="padding:16px;border-bottom:1px solid #e8e8f0">
                    <form method="POST"
                          action="<?= BASE_URL ?>/admin/lab-tests/<?= (int)$t['id'] ?>/update">
                        <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 80px;gap:12px;margin-bottom:12px">
                            <div class="form-group" style="margin:0">
                                <label>Название</label>
                                <input class="form-control" type="text" name="name"
                                       required value="<?= View::e($t['name']) ?>">
                            </div>
                            <div class="form-group" style="margin:0">
                                <label>Категория</label>
                                <input class="form-control" type="text" name="category"
                                       required value="<?= View::e($t['category']) ?>">
                            </div>
                            <div class="form-group" style="margin:0">
                                <label>Цена, ₽</label>
                                <input class="form-control" type="number" name="price"
                                       required min="1" step="0.01" value="<?= (float)$t['price'] ?>">
                            </div>
                            <div class="form-group" style="margin:0">
                                <label>Мин.</label>
                                <input class="form-control" type="number" name="duration_min"
                                       required min="1" value="<?= (int)$t['duration_min'] ?>">
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                            <div class="form-group" style="margin:0">
                                <label>Описание</label>
                                <input class="form-control" type="text" name="description"
                                       value="<?= View::e($t['description'] ?? '') ?>">
                            </div>
                            <div class="form-group" style="margin:0">
                                <label>Подготовка к анализу</label>
                                <input class="form-control" type="text" name="preparation"
                                       value="<?= View::e($t['preparation'] ?? '') ?>">
                            </div>
                        </div>
                        <div style="display:flex;gap:8px">
                            <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
                            <button type="button" class="btn btn-sm"
                                    style="border:1px solid #dde0e8"
                                    onclick="ltToggleEdit(<?= (int)$t['id'] ?>)">
                                Отмена
                            </button>
                        </div>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($tests)): ?>
            <tr>
                <td colspan="5" style="padding:24px;text-align:center;color:#aaa">
                    Анализов нет. Добавьте первый.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function ltToggleEdit(id) {
    const row = document.getElementById('lt-edit-' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
