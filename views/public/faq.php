<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="page-header">
    <h1 class="page-title">Частые вопросы</h1>
</div>

<div class="card u-mb-4">
    <div class="card__body">
        <div class="faq-list">
            <?php foreach ($questions as $i => $item): ?>
            <div class="faq-item" id="faq-<?= $i ?>">
                <button class="faq-question" aria-expanded="false" aria-controls="faq-answer-<?= $i ?>">
                    <span><?= View::e($item['q']) ?></span>
                    <span class="faq-icon" id="faq-icon-<?= $i ?>">+</span>
                </button>
                <div class="faq-answer" id="faq-answer-<?= $i ?>" role="region">
                    <p><?= View::e($item['a']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card__body u-text-center">
        <p class="u-text-subtle">Не нашли ответ на свой вопрос?</p>
        <a href="<?= BASE_URL ?>/contact" class="btn btn--secondary u-mt-3">Написать нам</a>
    </div>
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
