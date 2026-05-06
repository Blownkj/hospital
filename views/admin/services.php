<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<div class="page-header">
    <div>
        <a href="<?= BASE_URL ?>/admin/dashboard" class="back-link">← Панель администратора</a>
        <h1 class="page-title">Услуги и прайс-лист</h1>
    </div>
    <a href="<?= BASE_URL ?>/admin/lab-tests" class="btn btn--ghost btn--sm">
        <?php icon('flask-conical', 14) ?> Анализы
    </a>
</div>

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

<!-- Форма добавления -->
<div class="card u-mb-4">
    <div class="card__body">
        <h2 class="card__title u-mb-4">Добавить услугу</h2>
        <form method="POST" action="<?= BASE_URL ?>/admin/services/create">
            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
            <div class="form-grid-3">
                <div class="form__group u-m-0">
                    <label class="form__label form__label--required" for="svc-name">Название</label>
                    <input class="form__control" type="text" id="svc-name" name="name"
                           required placeholder="Первичный приём терапевта">
                </div>
                <div class="form__group u-m-0">
                    <label class="form__label form__label--required" for="svc-price">Цена, ₽</label>
                    <input class="form__control" type="number" id="svc-price" name="price"
                           required min="1" step="0.01" placeholder="1500">
                </div>
                <div class="form__group u-m-0">
                    <label class="form__label" for="svc-spec">Специализация</label>
                    <select class="form__control" id="svc-spec" name="specialization_id">
                        <option value="0">— Общая —</option>
                        <?php foreach ($specs as $spec): ?>
                            <option value="<?= (int)$spec['id'] ?>"><?= View::e($spec['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form__group">
                <label class="form__label" for="svc-desc">Описание</label>
                <input class="form__control" type="text" id="svc-desc" name="description"
                       placeholder="Краткое описание услуги">
            </div>
            <button type="submit" class="btn btn--primary btn--sm">+ Добавить услугу</button>
        </form>
    </div>
</div>

<!-- Список услуг -->
<div class="card card--flush">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Услуга</th>
                    <th>Специализация</th>
                    <th class="td-right">Цена</th>
                    <th class="td-actions td-w180">Действия</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($services as $svc): ?>
                <tr id="row-<?= (int)$svc['id'] ?>">
                    <td>
                        <div class="u-fw-medium"><?= View::e($svc['name']) ?></div>
                        <?php if ($svc['description']): ?>
                            <div class="u-text-xs u-text-muted u-mt-2">
                                <?= View::e($svc['description']) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="u-text-muted"><?= View::e($svc['specialization_name'] ?? '—') ?></td>
                    <td class="price-amount"><?= number_format((float)$svc['price'], 0, '.', ' ') ?> ₽</td>
                    <td class="td-actions">
                        <button onclick="toggleEdit(<?= (int)$svc['id'] ?>)"
                                class="btn btn--ghost btn--sm">
                            <?php icon('settings', 13) ?> Изменить
                        </button>
                        <form method="POST"
                              action="<?= BASE_URL ?>/admin/services/<?= (int)$svc['id'] ?>/delete"
                              class="u-inline"
                              onsubmit="return confirm('Удалить услугу?')">
                            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                            <button type="submit" class="btn btn--danger btn--sm">Удалить</button>
                        </form>
                    </td>
                </tr>
                <tr id="edit-<?= (int)$svc['id'] ?>" class="inline-edit-row">
                    <td colspan="4">
                        <form method="POST" action="<?= BASE_URL ?>/admin/services/<?= (int)$svc['id'] ?>/update">
                            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                            <div class="form-grid-3">
                                <div class="form__group u-m-0">
                                    <label class="form__label">Название</label>
                                    <input class="form__control" type="text" name="name"
                                           required value="<?= View::e($svc['name']) ?>">
                                </div>
                                <div class="form__group u-m-0">
                                    <label class="form__label">Цена, ₽</label>
                                    <input class="form__control" type="number" name="price"
                                           required min="1" step="0.01"
                                           value="<?= (float)$svc['price'] ?>">
                                </div>
                                <div class="form__group u-m-0">
                                    <label class="form__label">Специализация</label>
                                    <select class="form__control" name="specialization_id">
                                        <option value="0">— Общая —</option>
                                        <?php foreach ($specs as $spec): ?>
                                            <option value="<?= (int)$spec['id'] ?>"
                                                <?= (int)$spec['id'] === (int)$svc['specialization_id'] ? 'selected' : '' ?>>
                                                <?= View::e($spec['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form__group">
                                <label class="form__label">Описание</label>
                                <input class="form__control" type="text" name="description"
                                       value="<?= View::e($svc['description'] ?? '') ?>">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn--primary btn--sm">
                                    <?php icon('save', 14) ?> Сохранить
                                </button>
                                <button type="button" class="btn btn--ghost btn--sm"
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
</div>

<script>
function toggleEdit(id) {
    const editRow = document.getElementById('edit-' + id);
    editRow.style.display = editRow.style.display === 'none' ? 'table-row' : 'none';
}
</script>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
