<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';

$isEdit   = $doctor !== null;
$formAction = $isEdit
    ? BASE_URL . '/admin/doctors/' . (int)$doctor['id'] . '/edit'
    : BASE_URL . '/admin/doctors/create';
?>

<a href="<?= BASE_URL ?>/admin/doctors" class="back-link">← Врачи</a>
<h1 class="page-title u-mb-6"><?= View::e($pageTitle) ?></h1>

<?php include ROOT_PATH . '/views/partials/flash.php'; ?>

<div class="card" style="max-width:560px">
    <div class="card__body">
        <form method="POST" action="<?= $formAction ?>">
            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">

            <?php if (!$isEdit): ?>
            <div class="form__group">
                <label class="form__label" for="email">Email <span class="u-text-danger">*</span></label>
                <input class="form__control" type="email" id="email" name="email"
                       value="<?= View::e($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="form__group">
                <label class="form__label" for="password">Пароль <span class="u-text-danger">*</span></label>
                <input class="form__control" type="password" id="password" name="password"
                       minlength="8" required>
                <span class="form__hint">Не менее 8 символов</span>
            </div>
            <?php endif; ?>

            <div class="form__group">
                <label class="form__label" for="last_name">Фамилия <span class="u-text-danger">*</span></label>
                <input class="form__control" type="text" id="last_name" name="last_name"
                       value="<?= View::e($_POST['last_name'] ?? ($doctor['last_name'] ?? '')) ?>" required>
            </div>

            <div class="form__group">
                <label class="form__label" for="first_name">Имя <span class="u-text-danger">*</span></label>
                <input class="form__control" type="text" id="first_name" name="first_name"
                       value="<?= View::e($_POST['first_name'] ?? ($doctor['first_name'] ?? '')) ?>" required>
            </div>

            <div class="form__group">
                <label class="form__label" for="middle_name">Отчество</label>
                <input class="form__control" type="text" id="middle_name" name="middle_name"
                       value="<?= View::e($_POST['middle_name'] ?? ($doctor['middle_name'] ?? '')) ?>">
            </div>

            <div class="form__group">
                <label class="form__label" for="specialization_id">Специализация <span class="u-text-danger">*</span></label>
                <select class="form__control" id="specialization_id" name="specialization_id" required>
                    <option value="">— Выберите —</option>
                    <?php foreach ($specializations as $s):
                        $selected = (int)($doctor['specialization_id'] ?? 0) === (int)$s['id']
                            || (int)($_POST['specialization_id'] ?? 0) === (int)$s['id'];
                    ?>
                        <option value="<?= (int)$s['id'] ?>" <?= $selected ? 'selected' : '' ?>>
                            <?= View::e($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form__group">
                <label class="form__label" for="bio">О враче</label>
                <textarea class="form__control" id="bio" name="bio" rows="4"><?= View::e($_POST['bio'] ?? ($doctor['bio'] ?? '')) ?></textarea>
            </div>

            <div class="u-flex u-gap-3 u-mt-4">
                <button type="submit" class="btn btn--primary">
                    <?= $isEdit ? 'Сохранить изменения' : 'Добавить врача' ?>
                </button>
                <a href="<?= BASE_URL ?>/admin/doctors" class="btn btn--ghost">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
