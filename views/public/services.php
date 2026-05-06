<?php
use App\Core\View;
require ROOT_PATH . '/views/layout/public_header.php';
require ROOT_PATH . '/views/partials/icon.php';
?>

<div class="page-header">
    <h1 class="page-title">Услуги и цены</h1>
</div>

<?php foreach ($grouped as $specialization => $services): ?>
    <div class="price-section">
        <div class="price-spec-title"><?= View::e($specialization) ?></div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Услуга</th>
                        <th>Описание</th>
                        <th class="td-right">Стоимость</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td class="u-fw-medium"><?= View::e($service['name']) ?></td>
                            <td class="u-text-subtle"><?= View::e($service['description'] ?? '') ?></td>
                            <td class="price-amount"><?= number_format((float)$service['price'], 0, '.', ' ') ?> ₽</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>

<div class="payment-note">
    <?php icon('credit-card', 18) ?>
    Принимаем наличные и банковские карты. Возможна оплата по полису ДМС.
</div>

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
