<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<div class="page-header">
    <div>
        <a href="<?= BASE_URL ?>/admin/dashboard" class="back-link">← Панель администратора</a>
        <h1 class="page-title">Анализы и прайс-лист</h1>
    </div>
    <a href="<?= BASE_URL ?>/admin/services" class="btn btn--ghost btn--sm">
        <?php icon('clipboard-list', 14) ?> Обычные услуги
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
        <h2 class="card__title u-mb-4">Добавить анализ</h2>
        <form method="POST" action="<?= BASE_URL ?>/admin/lab-tests/create">
            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
            <div class="form-grid-4">
                <div class="form__group u-m-0">
                    <label class="form__label form__label--required" for="lt-name">Название</label>
                    <input class="form__control" type="text" id="lt-name" name="name"
                           required placeholder="Общий анализ крови">
                </div>
                <div class="form__group u-m-0">
                    <label class="form__label form__label--required" for="lt-cat">Категория</label>
                    <input class="form__control" type="text" id="lt-cat" name="category"
                           required placeholder="Гематология">
                </div>
                <div class="form__group u-m-0">
                    <label class="form__label form__label--required" for="lt-price">Цена, ₽</label>
                    <input class="form__control" type="number" id="lt-price" name="price"
                           required min="1" step="0.01" placeholder="500">
                </div>
                <div class="form__group u-m-0">
                    <label class="form__label" for="lt-dur">Мин.</label>
                    <input class="form__control" type="number" id="lt-dur" name="duration_min"
                           required min="1" value="15">
                </div>
            </div>
            <div class="form-grid-2">
                <div class="form__group u-m-0">
                    <label class="form__label" for="lt-desc">Описание</label>
                    <input class="form__control" type="text" id="lt-desc" name="description"
                           placeholder="Краткое описание">
                </div>
                <div class="form__group u-m-0">
                    <label class="form__label" for="lt-prep">Подготовка к анализу</label>
                    <input class="form__control" type="text" id="lt-prep" name="preparation"
                           placeholder="Натощак, не курить 2 ч.">
                </div>
            </div>
            <button type="submit" class="btn btn--primary btn--sm">+ Добавить анализ</button>
        </form>
    </div>
</div>

<!-- Список анализов -->
<div class="card card--flush">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Анализ</th>
                    <th>Категория</th>
                    <th class="td-center">Мин.</th>
                    <th class="td-right">Цена</th>
                    <th class="td-actions td-w180">Действия</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tests as $t): ?>
                <tr id="lt-row-<?= (int)$t['id'] ?>">
                    <td>
                        <div class="u-fw-medium"><?= View::e($t['name']) ?></div>
                        <?php if ($t['description']): ?>
                            <div class="u-text-xs u-text-muted u-mt-2"><?= View::e($t['description']) ?></div>
                        <?php endif; ?>
                        <?php if ($t['preparation']): ?>
                            <div class="u-text-xs u-text-muted">
                                Подготовка: <?= View::e($t['preparation']) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="u-text-muted"><?= View::e($t['category']) ?></td>
                    <td class="td-center u-text-muted"><?= (int)$t['duration_min'] ?></td>
                    <td class="price-amount"><?= number_format((float)$t['price'], 0, '.', ' ') ?> ₽</td>
                    <td class="td-actions">
                        <button onclick="ltToggleEdit(<?= (int)$t['id'] ?>)"
                                class="btn btn--ghost btn--sm">
                            <?php icon('settings', 13) ?> Изменить
                        </button>
                        <form method="POST"
                              action="<?= BASE_URL ?>/admin/lab-tests/<?= (int)$t['id'] ?>/delete"
                              class="u-inline"
                              onsubmit="return confirm('Удалить анализ?')">
                            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                            <button type="submit" class="btn btn--danger btn--sm">Удалить</button>
                        </form>
                    </td>
                </tr>
                <tr id="lt-edit-<?= (int)$t['id'] ?>" class="inline-edit-row">
                    <td colspan="5">
                        <form method="POST" action="<?= BASE_URL ?>/admin/lab-tests/<?= (int)$t['id'] ?>/update">
                            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">
                            <div class="form-grid-4">
                                <div class="form__group u-m-0">
                                    <label class="form__label">Название</label>
                                    <input class="form__control" type="text" name="name"
                                           required value="<?= View::e($t['name']) ?>">
                                </div>
                                <div class="form__group u-m-0">
                                    <label class="form__label">Категория</label>
                                    <input class="form__control" type="text" name="category"
                                           required value="<?= View::e($t['category']) ?>">
                                </div>
                                <div class="form__group u-m-0">
                                    <label class="form__label">Цена, ₽</label>
                                    <input class="form__control" type="number" name="price"
                                           required min="1" step="0.01" value="<?= (float)$t['price'] ?>">
                                </div>
                                <div class="form__group u-m-0">
                                    <label class="form__label">Мин.</label>
                                    <input class="form__control" type="number" name="duration_min"
                                           required min="1" value="<?= (int)$t['duration_min'] ?>">
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div class="form__group u-m-0">
                                    <label class="form__label">Описание</label>
                                    <input class="form__control" type="text" name="description"
                                           value="<?= View::e($t['description'] ?? '') ?>">
                                </div>
                                <div class="form__group u-m-0">
                                    <label class="form__label">Подготовка</label>
                                    <input class="form__control" type="text" name="preparation"
                                           value="<?= View::e($t['preparation'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn--primary btn--sm">
                                    <?php icon('save', 14) ?> Сохранить
                                </button>
                                <button type="button" class="btn btn--ghost btn--sm"
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
                    <td colspan="5" class="u-text-center u-text-muted u-p-6">
                        Анализов нет. Добавьте первый.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function ltToggleEdit(id) {
    const row = document.getElementById('lt-edit-' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
