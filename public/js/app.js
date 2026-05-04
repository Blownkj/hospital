// ── Выбор слота времени ──────────────────────────────────────────────────
function selectSlot(time, btn) {
    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.getElementById('selected-time').value = time;
    document.getElementById('confirm-time').textContent = time;
    document.getElementById('confirm-block').style.display = 'block';
}

// ── Выбор анализа ────────────────────────────────────────────────────────
function selectLabTest(id, name, price, btn) {
    document.querySelectorAll('.lab-card').forEach(c => c.classList.remove('selected'));
    btn.classList.add('selected');

    document.getElementById('selected-lab-test-id').value    = id;
    document.getElementById('confirm-lab-name').textContent  = name;
    document.getElementById('confirm-lab-price').textContent = price;
    document.getElementById('lab-confirm-block').style.display = 'block';
}