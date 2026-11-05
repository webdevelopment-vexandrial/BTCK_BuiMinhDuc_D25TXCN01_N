<?php
require __DIR__ . '/includes/init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = filter_var($_POST['cancel_order'] ?? '', FILTER_VALIDATE_INT);
    if ($orderId === false || $orderId < 1) {
        set_flash('error', 'Mã đơn hàng không hợp lệ.');
    } else {
        try {
            update_order_status($pdo, $orderId, 'cancelled', current_user()['id']);
            set_flash('success', 'Đã hủy đơn hàng và cập nhật lại tồn kho.');
        } catch (RuntimeException $e) {
            set_flash('error', $e->getMessage());
        }
    }
    redirect('orders.php');
}

// Chỉ lấy đơn của người đang đăng nhập
$stmt = $pdo->prepare(
    'SELECT id, total, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC'
);
$stmt->execute([current_user()['id']]);
$orders = $stmt->fetchAll();

$pageTitle = 'Đơn hàng của tôi';
require __DIR__ . '/includes/header.php';
?>
<section class="orders-page">
    <a class="product-close orders-close" href="<?= url('index.php') ?>" aria-label="Đóng và quay lại cửa hàng" title="Quay lại cửa hàng">×</a>

    <header class="orders-heading">
        <p class="orders-eyebrow">THEO DÕI ĐƠN ĐẶT HÀNG</p>
        <h2>Đơn hàng của tôi</h2>
        <p>Xem lại các đơn hàng và cập nhật trạng thái giao hàng.</p>
    </header>

    <?php if (!$orders): ?>
        <div class="orders-empty">
            <span class="orders-empty-icon" aria-hidden="true">◇</span>
            <h3>Bạn chưa có đơn hàng nào</h3>
            <p>Đơn hàng sẽ xuất hiện ở đây sau khi bạn hoàn tất đặt hàng.</p>
            <a class="cart-button cart-button-primary" href="<?= url('index.php') ?>">Xem sản phẩm <span aria-hidden="true">→</span></a>
        </div>
    <?php else: ?>
        <div class="orders-table-wrap">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th scope="col">Mã đơn</th>
                        <th scope="col">Ngày đặt</th>
                        <th scope="col">Tổng tiền</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><span class="order-code">DH-<?= str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                            <td><?= date('d/m/Y · H:i', strtotime($order['created_at'])) ?></td>
                            <td class="order-total"><?= format_price($order['total']) ?></td>
                            <td><span class="order-status status-<?= e($order['status']) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                            <td>
                                <a class="order-view-link" href="<?= url('order-detail.php?id=' . $order['id']) ?>">Chi tiết <span aria-hidden="true">→</span></a>
                                <?php if ($order['status'] === 'pending'): ?>
                                    <form action="<?= url('orders.php') ?>" method="post" class="order-cancel-form">
                                        <?= csrf_field() ?>
                                        <button type="submit" name="cancel_order" value="<?= (int) $order['id'] ?>"
                                                class="cart-button cart-button-danger"
                                                data-confirm="Bạn có chắc muốn hủy đơn hàng này không?">
                                            Hủy đơn
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
