<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="page-header">
    <h1 class="page-title">Частые вопросы</h1>
</div>

<div class="card">
    <div class="faq-list">
        <?php foreach ($questions as $i => $item): ?>
        <div class="faq-item" id="faq-<?= $i ?>">
            <button class="faq-question" onclick="toggleFaq(<?= $i ?>)">
                <span><?= View::e($item['q']) ?></span>
                <span class="faq-icon" id="faq-icon-<?= $i ?>">+</span>
            </button>
            <div class="faq-answer" id="faq-answer-<?= $i ?>">
                <p><?= View::e($item['a']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- CTA -->
<div class="card" style="text-align:center;padding:24px">
    <p class="text-muted">Не нашли ответ на свой вопрос?</p>
    <a href="<?= BASE_URL ?>/contact" class="btn btn-secondary" style="margin-top:12px;display:inline-block">
        Написать нам
    </a>
</div>

<script>
function toggleFaq(i) {
    const answer = document.getElementById('faq-answer-' + i);
    const icon   = document.getElementById('faq-icon-' + i);
    const isOpen = answer.classList.contains('open');
    // Закрыть все
    document.querySelectorAll('.faq-answer').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.faq-icon').forEach(el => el.textContent = '+');
    // Открыть текущий если был закрыт
    if (!isOpen) {
        answer.classList.add('open');
        icon.textContent = '−';
    }
}
</script>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>