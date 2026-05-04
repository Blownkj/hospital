<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
?>

<div class="section-title">Услуги и цены</div>

<?php foreach ($grouped as $specialization => $services): ?>
    <div class="price-section">
        <div class="price-spec-title"><?= View::e($specialization) ?></div>
        <table class="price-table">
            <thead>
                <tr>
                    <th>Услуга</th>
                    <th>Описание</th>
                    <th style="text-align:right">Стоимость</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td style="font-weight:500"><?= View::e($service['name']) ?></td>
                        <td style="color:#777"><?= View::e($service['description'] ?? '') ?></td>
                        <td style="text-align:right" class="price-amount">
                            <?= number_format((float)$service['price'], 0, '.', ' ') ?> ₽
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>

<div style="background:#eef3fd;border-radius:12px;padding:18px 24px;font-size:14px;color:#555;margin-top:16px">
    💳 Принимаем наличные и банковские карты. Возможна оплата по полису ДМС.
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>