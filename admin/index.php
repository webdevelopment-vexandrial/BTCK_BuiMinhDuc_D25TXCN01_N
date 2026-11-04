<?php
require __DIR__ . '/../includes/init.php';
require_admin();

$statuses = order_statuses();
$statusCounts = array_fill_keys(array_keys($statuses), 0);
$stmt = $pdo->query('SELECT status, COUNT(*) AS order_count FROM orders GROUP BY status');
foreach ($stmt->fetchAll() as $row) {
    if (array_key_exists($row['status'], $statusCounts)) {
        $statusCounts[$row['status']] = (int) $row['order_count'];
    }
}

$completedRevenue = (int) $pdo->query(
    "SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'completed'"
)->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT id, name, stock FROM products
     ORDER BY stock ASC, id ASC
     LIMIT 5'
);
$stmt->execute();
$lowStockProducts = $stmt->fetchAll();

$pageTitle = 'Tổng quan quản trị';
require __DIR__ . '/../includes/header.php';
?>
<h2>Tổng quan quản trị</h2>

<p class="cart-actions">
    <a href="<?= url('admin/product.php') ?>" class="btn">Quản lý sản phẩm</a>
    <a href="<?= url('admin/categories.php') ?>" class="btn">Quản lý danh mục</a>
    <a href="<?= url('admin/orders.php') ?>" class="btn">Quản lý đơn hàng</a>
</p>

<h3>Đơn hàng theo trạng thái</h3>
<div class="dashboard-cards">
    <?php foreach ($statuses as $status => $label): ?>
        <article class="dashboard-card">
            <h4><?= e($label) ?></h4>
            <p><?= $statusCounts[$status] ?></p>
        </article>
    <?php endforeach; ?>
    <article class="dashboard-card">
        <h4>Doanh thu đơn hoàn tất</h4>
        <p><?= format_price($completedRevenue) ?></p>
    </article>
</div>

<h3>5 sản phẩm có tồn kho thấp nhất</h3>
<?php if (!$lowStockProducts): ?>
    <p>Chưa có sản phẩm nào.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Mã</th>
                <th>Sản phẩm</th>
                <th>Tồn kho</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lowStockProducts as $product): ?>
                <tr>
                    <td><?= (int) $product['id'] ?></td>
                    <td><?= e($product['name']) ?></td>
                    <td><?= (int) $product['stock'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
