<?php
require __DIR__ . '/../includes/init.php';
require_admin();

$statuses = order_statuses();

// Cập nhật trạng thái một đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = filter_var($_POST['order_id'] ?? '', FILTER_VALIDATE_INT);
    $newStatus = $_POST['status'] ?? '';
    if ($orderId === false || $orderId < 1 || !is_string($newStatus) || !isset($statuses[$newStatus])) {
        set_flash('error', 'Mã đơn hàng hoặc trạng thái không hợp lệ.');
    } else {
        try {
            update_order_status($pdo, $orderId, $newStatus);
            set_flash('success', 'Đã cập nhật đơn hàng #' . $orderId . '.');
        } catch (RuntimeException $e) {
            set_flash('error', $e->getMessage());
        }
    }
    redirect('admin/orders.php');
}

// Lọc theo trạng thái: admin/orders.php?status=pending
$filter = $_GET['status'] ?? '';
if (is_string($filter) && isset($statuses[$filter])) {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE status = ? ORDER BY id DESC');
    $stmt->execute([$filter]);
} else {
    $filter = '';
    $stmt = $pdo->query('SELECT * FROM orders ORDER BY id DESC');
}
$orders = $stmt->fetchAll();

$pageTitle = 'Quản lý đơn hàng';
require __DIR__ . '/../includes/header.php';
?>

<section class="orders-page admin-orders-page">
    <header class="orders-heading">
        <p class="orders-eyebrow">TRUNG TÂM QUẢN LÝ</p>
        <h2>Quản lý đơn hàng</h2>
        <p>Lọc đơn theo trạng thái và cập nhật tiến trình xử lý.</p>
    </header>

    <nav class="category-bar orders-filter" aria-label="Lọc đơn hàng">
        <a href="<?= url('admin/index.php') ?>">Tổng quan</a>
        <a href="<?= url('admin/categories.php') ?>">Danh mục</a>
        <a href="<?= url('admin/orders.php') ?>" class="<?= $filter === '' ? 'active' : '' ?>">Tất cả</a>
        <?php foreach ($statuses as $value => $label): ?>
            <a href="<?= url('admin/orders.php?status=' . $value) ?>"
               class="<?= $filter === $value ? 'active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
        <a href="<?= url('admin/product.php') ?>">Sản phẩm</a>
    </nav>

    <?php if (!$orders): ?>
        <div class="orders-empty">
            <span class="orders-empty-icon" aria-hidden="true">◇</span>
            <h3>Không có đơn hàng phù hợp</h3>
            <p>Thử chọn trạng thái khác để xem thêm đơn hàng.</p>
        </div>
    <?php else: ?>
        <div class="orders-table-wrap">
            <table class="orders-table admin-orders-table">
                <thead>
                    <tr>
                        <th scope="col">Mã đơn</th>
                        <th scope="col">Ngày đặt</th>
                        <th scope="col">Người nhận</th>
                        <th scope="col">Tổng tiền</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col"><span class="sr-only">Chi tiết</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><span class="order-code">DH-<?= str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                            <td><?= date('d/m/Y · H:i', strtotime($order['created_at'])) ?></td>
                            <td class="admin-order-customer"><?= e($order['fullname']) ?><small><?= e($order['phone']) ?></small></td>
                            <td class="order-total"><?= format_price($order['total']) ?></td>
                            <td>
                                <form action="<?= url('admin/orders.php') ?>" method="post" class="admin-order-status-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" aria-label="Trạng thái đơn DH-<?= str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT) ?>">
                                        <?php foreach ($statuses as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $order['status'] === $value ? 'selected' : '' ?>>
                                                <?= e($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="cart-button cart-button-secondary">Lưu</button>
                                </form>
                            </td>
                            <td><a class="order-view-link" href="<?= url('order-detail.php?id=' . $order['id']) ?>">Chi tiết <span aria-hidden="true">→</span></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>