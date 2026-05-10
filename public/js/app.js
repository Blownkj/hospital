// ── Выбор слота времени ──────────────────────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.slot-btn');
    if (!btn) return;

    const time = btn.dataset.time;
    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.getElementById('selected-time').value = time;
    document.getElementById('confirm-time').textContent = time;
    document.getElementById('confirm-block').classList.remove('u-hidden');
});

// ── Подтверждение перед отправкой формы (data-confirm) ──────────────────
document.addEventListener('submit', function (e) {
    const form = e.target.closest('form[data-confirm]');
    if (!form) return;
    if (!window.confirm(form.dataset.confirm)) e.preventDefault();
});

// ── Перенос записи (admin/appointments) ─────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-toggle-reschedule]');
    if (!btn) return;

    const id = btn.dataset.toggleReschedule;
    const el = document.getElementById('rs-' + id);
    if (!el) return;
    el.style.display = (el.style.display === 'none' || !el.style.display) ? 'block' : 'none';
});

// ── FAQ аккордеон ────────────────────────────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.faq-question');
    if (!btn) return;

    const answer = btn.nextElementSibling;
    const icon   = btn.querySelector('.faq-icon');
    const isOpen = answer.classList.contains('open');

    document.querySelectorAll('.faq-answer').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.faq-icon').forEach(el => el.textContent = '+');
    document.querySelectorAll('.faq-question').forEach(el => el.setAttribute('aria-expanded', 'false'));

    if (!isOpen) {
        answer.classList.add('open');
        icon.textContent = '−';
        btn.setAttribute('aria-expanded', 'true');
    }
});

// ── Выбор анализа ────────────────────────────────────────────────────────
function selectLabTest(id, name, price, btn) {
    document.querySelectorAll('.lab-card').forEach(c => c.classList.remove('selected'));
    btn.classList.add('selected');

    document.getElementById('selected-lab-test-id').value    = id;
    document.getElementById('confirm-lab-name').textContent  = name;
    document.getElementById('confirm-lab-price').textContent = price;
    document.getElementById('lab-confirm-block').style.display = 'block';
}