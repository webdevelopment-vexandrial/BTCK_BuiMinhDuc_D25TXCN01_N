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

$todayOrders = (int) $pdo->query(
    'SELECT COUNT(*) FROM orders WHERE created_at >= CURRENT_DATE()'
)->fetchColumn();
$productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$customerCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$stockAlertThreshold = 5;

$stmt = $pdo->prepare(
    'SELECT id, name, stock FROM products
     WHERE stock <= ?
     ORDER BY stock ASC, id ASC
     LIMIT 5'
);
$stmt->execute([$stockAlertThreshold]);
$lowStockProducts = $stmt->fetchAll();

$recentOrders = $pdo->query(
    'SELECT id, fullname, total, status, created_at
     FROM orders
     ORDER BY id DESC
     LIMIT 5'
)->fetchAll();

$pageTitle = 'Tổng quan quản trị';
require __DIR__ . '/../includes/header.php';
?>
<section class="admin-page admin-dashboard-page">
<h2>Tổng quan quản trị</h2>
<p class="admin-dashboard-intro">Theo dõi hoạt động cửa hàng và xử lý các mục cần chú ý.</p>

<p class="cart-actions">
    <a href="<?= url('admin/product.php') ?>" class="btn">Quản lý sản phẩm</a>
    <a href="<?= url('admin/categories.php') ?>" class="btn">Quản lý danh mục</a>
    <a href="<?= url('admin/orders.php') ?>" class="btn">Quản lý đơn hàng</a>
</p>

<h3>Chỉ số cửa hàng</h3>
<div class="dashboard-cards">
    <article class="dashboard-card">
        <h4>Đơn hàng hôm nay</h4>
        <p><?= $todayOrders ?></p>
    </article>
    <article class="dashboard-card">
        <h4>Tổng khách hàng</h4>
        <p><?= $customerCount ?></p>
    </article>
    <article class="dashboard-card">
        <h4>Tổng sản phẩm</h4>
        <p><?= $productCount ?></p>
    </article>
    <article class="dashboard-card">
        <h4>Doanh thu đơn hoàn tất</h4>
        <p><?= format_price($completedRevenue) ?></p>
    </article>
</div>

<h3>Đơn hàng theo trạng thái</h3>
<div class="dashboard-cards">
    <?php foreach ($statuses as $status => $label): ?>
        <a class="dashboard-card dashboard-status-card" href="<?= url('admin/orders.php?status=' . $status) ?>">
            <h4><?= e($label) ?></h4>
            <p><?= $statusCounts[$status] ?></p>
        </a>
    <?php endforeach; ?>
</div>

<h3>5 sản phẩm sắp hết hàng <small>(còn không quá <?= $stockAlertThreshold ?>)</small></h3>
<?php if (!$lowStockProducts): ?>
    <p class="admin-dashboard-empty">Không có sản phẩm nào dưới ngưỡng cảnh báo tồn kho.</p>
<?php else: ?>
    <div class="admin-table-wrap">
    <table class="admin-table">
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
    </div>
<?php endif; ?>

<h3>5 đơn hàng gần nhất</h3>
<?php if (!$recentOrders): ?>
    <p class="admin-dashboard-empty">Chưa có đơn hàng nào.</p>
<?php else: ?>
    <div class="admin-table-wrap">
    <table class="admin-table admin-recent-orders">
        <thead>
            <tr>
                <th scope="col">Mã đơn</th>
                <th scope="col">Người nhận</th>
                <th scope="col">Ngày đặt</th>
                <th scope="col">Tổng tiền</th>
                <th scope="col">Trạng thái</th>
                <th scope="col"><span class="sr-only">Chi tiết</span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td><span class="order-code">DH-<?= str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                    <td><?= e($order['fullname']) ?></td>
                    <td><?= date('d/m/Y · H:i', strtotime($order['created_at'])) ?></td>
                    <td><?= format_price($order['total']) ?></td>
                    <td><span class="order-status status-<?= e($order['status']) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                    <td><a class="order-view-link" href="<?= url('order-detail.php?id=' . $order['id']) ?>">Chi tiết <span aria-hidden="true">→</span></a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
