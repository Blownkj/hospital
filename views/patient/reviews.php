<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<a href="<?= BASE_URL ?>/patient/dashboard" class="back-link">← Личный кабинет</a>

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
    <h1 class="page-title">Мои отзывы</h1>
</div>

<!-- Форма нового отзыва -->
<?php if (!empty($canReview)): ?>
<div class="card u-mb-5">
    <div class="card__body">
        <h2 class="card__title u-mb-5">
            <?php icon('message-circle', 18) ?> Оставить отзыв
        </h2>
        <form method="POST" action="<?= BASE_URL ?>/patient/reviews/submit">
            <input type="hidden" name="csrf_token" value="<?= View::e($csrf) ?>">

            <div class="form__group">
                <label class="form__label form__label--required" for="appointment_id">Приём</label>
                <select class="form__control" id="appointment_id" name="appointment_id" required>
                    <option value="">— Выберите приём —</option>
                    <?php foreach ($canReview as $a): ?>
                    <option value="<?= (int)$a['appointment_id'] ?>">
                        <?= View::e($a['full_name']) ?> — <?= View::e($a['specialization']) ?>
                        (<?= date('d.m.Y', strtotime($a['scheduled_at'])) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form__group">
                <label class="form__label form__label--required">Оценка</label>
                <div class="star-rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required>
                        <label for="star<?= $i ?>" aria-label="<?= $i ?> звёзд">★</label>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="form__group">
                <label class="form__label form__label--required" for="review_text">Отзыв</label>
                <textarea class="form__control" id="review_text" name="text" rows="4"
                          placeholder="Расскажите о вашем визите..." required minlength="10"></textarea>
            </div>

            <button type="submit" class="btn btn--primary btn--block">Отправить на модерацию</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- История отзывов -->
<div class="card">
    <div class="card__body">
        <h2 class="card__title u-mb-5">
            <?php icon('clipboard-list', 18) ?> История отзывов
        </h2>

        <?php if (empty($myReviews)): ?>
            <p class="u-text-muted u-text-center u-p-6">
                Вы ещё не оставляли отзывов.
            </p>
        <?php else: ?>
            <?php foreach ($myReviews as $r): ?>
            <div class="my-review-item">
                <div class="my-review-header">
                    <div>
                        <div class="my-review-doctor"><?= View::e($r['doctor_name']) ?></div>
                        <div class="my-review-spec"><?= View::e($r['specialization']) ?></div>
                    </div>
                    <div class="my-review-meta">
                        <span class="my-review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                     class="<?= $i <= (int)$r['rating'] ? 'filled' : '' ?>" aria-hidden="true">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                            <?php endfor; ?>
                        </span>
                        <?php if ($r['is_approved']): ?>
                            <span class="badge badge--success">Опубликован</span>
                        <?php else: ?>
                            <span class="badge badge--warning">На модерации</span>
                        <?php endif; ?>
                    </div>
                </div>
                <p class="my-review-text"><?= View::e($r['text']) ?></p>
                <div class="my-review-date"><?= date('d.m.Y', strtotime($r['created_at'])) ?></div>
                <?php if (!empty($r['admin_reply'])): ?>
                <div class="my-review-reply">
                    <div class="my-review-reply__label">Ответ клиники</div>
                    <p class="my-review-reply__text"><?= View::e($r['admin_reply']) ?></p>
                    <div class="my-review-reply__date"><?= date('d.m.Y', strtotime($r['admin_reply_at'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
