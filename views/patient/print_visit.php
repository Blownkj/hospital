<?php
use App\Core\View;
$typeLabels = [
    'drug'      => 'Препарат',
    'procedure' => 'Процедура',
    'referral'  => 'Направление',
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Назначения — <?= View::e($visit['patient_name']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Georgia', serif;
            font-size: 14px;
            color: #111;
            background: #fff;
            padding: 40px;
            max-width: 780px;
            margin: 0 auto;
        }

        /* ── Шапка ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #1a3a5c;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .clinic-name {
            font-size: 20px;
            font-weight: 700;
            color: #1a3a5c;
        }
        .clinic-sub {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
        .doc-title {
            font-size: 13px;
            color: #666;
            text-align: right;
        }

        /* ── Участники ── */
        .parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        .party-box {
            border: 1px solid #dde0e8;
            border-radius: 8px;
            padding: 14px 16px;
        }
        .party-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #999;
            margin-bottom: 8px;
        }
        .party-name {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .party-sub {
            font-size: 13px;
            color: #555;
        }

        /* ── Протокол ── */
        .protocol {
            margin-bottom: 24px;
        }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #1a3a5c;
            border-bottom: 1px solid #dde0e8;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .protocol-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
        }
        .protocol-block label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #999;
            display: block;
            margin-bottom: 4px;
        }
        .protocol-block p {
            font-size: 14px;
            line-height: 1.6;
        }

        /* ── Назначения ── */
        .prescriptions {
            margin-bottom: 32px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th {
            background: #f0f4fa;
            text-align: left;
            padding: 8px 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #555;
            border-bottom: 2px solid #dde0e8;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f0f5;
            vertical-align: top;
        }
        tr:last-child td { border-bottom: none; }
        .type-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            background: #e8f0fe;
            color: #1d4ed8;
        }

        /* ── Подпись ── */
        .signature {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .sig-line {
            border-top: 1px solid #555;
            width: 200px;
            padding-top: 6px;
            font-size: 12px;
            color: #666;
        }

        /* ── Кнопка печати (не печатается) ── */
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1a3a5c;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
        }

        @media print {
            .print-btn { display: none; }
            body { padding: 20px; }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">Распечатать</button>

<!-- Шапка -->
<div class="header">
    <div>
        <div class="clinic-name">Частная клиника «МедЦентр»</div>
        <div class="clinic-sub">Лицензия № ЛО-77-01-000000 · г. Москва, ул. Примерная, 1</div>
    </div>
    <div class="doc-title">
        <div style="font-weight:700;font-size:15px">Лист назначений</div>
        <div>№ визита: <?= (int)$visit['id'] ?></div>
        <div>Дата: <?= date('d.m.Y', strtotime($visit['scheduled_at'])) ?></div>
    </div>
</div>

<!-- Участники -->
<div class="parties">
    <div class="party-box">
        <div class="party-label">Пациент</div>
        <div class="party-name"><?= View::e($visit['patient_name']) ?></div>
        <div class="party-sub">
            Дата рождения: <?= date('d.m.Y', strtotime($visit['patient_birth_date'])) ?>
        </div>
        <?php if ($visit['patient_phone']): ?>
        <div class="party-sub">Тел.: <?= View::e($visit['patient_phone']) ?></div>
        <?php endif; ?>
    </div>
    <div class="party-box">
        <div class="party-label">Врач</div>
        <div class="party-name"><?= View::e($visit['doctor_name']) ?></div>
        <div class="party-sub"><?= View::e($visit['specialization']) ?></div>
        <div class="party-sub">
            Приём: <?= date('d.m.Y H:i', strtotime($visit['started_at'])) ?>
            <?php if ($visit['ended_at']): ?>
                — <?= date('H:i', strtotime($visit['ended_at'])) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Протокол -->
<?php if ($visit['complaints'] || $visit['examination'] || $visit['diagnosis']): ?>
<div class="protocol">
    <div class="section-title">Протокол приёма</div>
    <div class="protocol-grid">
        <div class="protocol-block">
            <label>Жалобы</label>
            <p><?= $visit['complaints'] ? View::e($visit['complaints']) : '—' ?></p>
        </div>
        <div class="protocol-block">
            <label>Осмотр</label>
            <p><?= $visit['examination'] ? View::e($visit['examination']) : '—' ?></p>
        </div>
        <div class="protocol-block">
            <label>Диагноз</label>
            <p><strong><?= $visit['diagnosis'] ? View::e($visit['diagnosis']) : '—' ?></strong></p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Назначения -->
<div class="prescriptions">
    <div class="section-title">Назначения</div>
    <?php if (empty($prescriptions)): ?>
        <p style="color:#888;font-size:13px">Назначений нет.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th style="width:120px">Тип</th>
                <th>Название</th>
                <th style="width:160px">Доза / срок</th>
                <th>Примечания</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prescriptions as $pr): ?>
            <tr>
                <td><span class="type-badge"><?= View::e($typeLabels[$pr['type']] ?? $pr['type']) ?></span></td>
                <td><strong><?= View::e($pr['name']) ?></strong></td>
                <td><?= View::e($pr['dosage'] ?: '—') ?></td>
                <td><?= View::e($pr['notes'] ?: '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Подпись -->
<div class="signature">
    <div>
        <div class="sig-line">Подпись врача</div>
    </div>
    <div style="text-align:right;font-size:12px;color:#888">
        Документ сформирован <?= date('d.m.Y H:i') ?>
    </div>
</div>

</body>
</html>
