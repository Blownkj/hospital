<?php use App\Core\View; ?>
<?php if (!empty($flash ?? '')): ?>
<div class="alert alert--success" role="alert" aria-live="polite">
    <span class="alert__icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
            <path d="m9 12 2 2 4-4"/>
        </svg>
    </span>
    <span class="alert__body"><?= View::e($flash) ?></span>
</div>
<?php endif; ?>
<?php if (!empty($error ?? '')): ?>
<div class="alert alert--error" role="alert" aria-live="polite">
    <span class="alert__icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
            <path d="m15 9-6 6M9 9l6 6"/>
        </svg>
    </span>
    <span class="alert__body"><?= View::e($error) ?></span>
</div>
<?php endif; ?>
<?php if (!empty($warning ?? '')): ?>
<div class="alert alert--warning" role="alert" aria-live="polite">
    <span class="alert__icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
            <path d="M12 9v4M12 17h.01"/>
        </svg>
    </span>
    <span class="alert__body"><?= View::e($warning) ?></span>
</div>
<?php endif; ?>
<?php if (!empty($info ?? '')): ?>
<div class="alert alert--info" role="alert" aria-live="polite">
    <span class="alert__icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 16v-4M12 8h.01"/>
        </svg>
    </span>
    <span class="alert__body"><?= View::e($info) ?></span>
</div>
<?php endif; ?>
