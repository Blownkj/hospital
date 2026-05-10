document.addEventListener('DOMContentLoaded', function () {
    var lineCanvas = document.getElementById('chartLine');
    var barCanvas  = document.getElementById('chartBar');

    if (lineCanvas) {
        var dayLabels  = JSON.parse(lineCanvas.dataset.labels);
        var dayCounts  = JSON.parse(lineCanvas.dataset.counts);
        var fmtLabels  = dayLabels.map(function (d) {
            var parts = d.split('-'); return parts[2] + '.' + parts[1];
        });
        new Chart(lineCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: fmtLabels,
                datasets: [{
                    label: 'Записей', data: dayCounts,
                    borderColor: '#14b8a6', backgroundColor: 'rgba(20,184,166,0.08)',
                    borderWidth: 2, tension: 0.3, fill: true, pointRadius: 3,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
            }
        });
    }

    if (barCanvas) {
        var doctorLabels = JSON.parse(barCanvas.dataset.labels);
        var doctorCounts = JSON.parse(barCanvas.dataset.counts);
        new Chart(barCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: doctorLabels.map(function (n) { return n.split(' ').slice(0, 2).join(' '); }),
                datasets: [{
                    label: 'Приёмов', data: doctorCounts,
                    backgroundColor: ['#14b8a6', '#0d9488', '#0f766e', '#f59e0b', '#6366f1'],
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } }, y: { grid: { display: false } } }
            }
        });
    }
});
