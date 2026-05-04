<?php use App\Core\View; ?>
<?php if (!empty($flash ?? '')): ?>
    <div class="alert alert-success">✅ <?= View::e($flash) ?></div>
<?php endif; ?>
<?php if (!empty($error ?? '')): ?>
    <div class="alert alert-error">⚠️ <?= View::e($error) ?></div>
<?php endif; ?>
