<?php
require __DIR__ . '/includes/init.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();

// Chỉ chủ đơn hàng hoặc quản trị viên được xem.
// Đơn không tồn tại và đơn của người khác đều trả về 404 như nhau.
if (!$order || ($order['user_id'] !== current_user()['id'] && !is_admin())) {
    http_response_code(404);
    $pageTitle = 'Không tìm thấy đơn hàng';
    require __DIR__ . '/includes/header.php';
    echo '<h2>Không tìm thấy đơn hàng</h2>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = $pdo->prepare(
    'SELECT product_name, price, quantity FROM order_items WHERE order_id = ? ORDER BY id'
);
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$pageTitle = 'Đơn hàng #' . $order['id'];
require __DIR__ . '/includes/header.php';
?>
<?php $ordersPath = is_admin() ? 'admin/orders.php' : 'orders.php'; ?>
<section class="order-detail-page">
    <a class="product-close orders-close" href="<?= url($ordersPath) ?>" aria-label="Đóng và quay lại danh sách đơn hàng" title="Quay lại đơn hàng">×</a>

    <header class="orders-heading">
        <p class="orders-eyebrow">CHI TIẾT GIAO DỊCH</p>
        <h2>Đơn hàng <span class="order-code">DH-<?= str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT) ?></span></h2>
        <p>Đặt lúc <?= date('d/m/Y · H:i', strtotime($order['created_at'])) ?></p>
    </header>

    <div class="order-summary-grid">
        <section class="order-info-card">
            <h3>Thông tin nhận hàng</h3>
            <dl class="order-info-list">
                <div>
                    <dt>Người nhận</dt>
                    <dd><?= e($order['fullname']) ?></dd>
                </div>
                <div>
                    <dt>Số điện thoại</dt>
                    <dd><?= e($order['phone']) ?></dd>
                </div>
                <div>
                    <dt>Địa chỉ</dt>
                    <dd><?= e($order['address']) ?></dd>
                </div>
                <div>
                    <dt>Ghi chú</dt>
                    <dd><?= $order['note'] !== '' ? nl2br(e($order['note'])) : 'Không có ghi chú' ?></dd>
                </div>
            </dl>
        </section>

        <section class="order-info-card order-status-card">
            <h3>Trạng thái đơn hàng</h3>
            <span class="order-status status-<?= e($order['status']) ?>"><?= e(order_status_label($order['status'])) ?></span>
            <p>Trạng thái sẽ được cập nhật khi đơn hàng được xử lý.</p>
        </section>
    </div>

    <section class="order-items-section">
        <h3>Sản phẩm trong đơn</h3>
        <div class="orders-table-wrap">
            <table class="orders-table order-items-table">
                <thead>
                    <tr>
                        <th scope="col">Sản phẩm</th>
                        <th scope="col">Đơn giá</th>
                        <th scope="col">Số lượng</th>
                        <th scope="col">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="order-item-name"><?= e($item['product_name']) ?></td>
                            <td><?= format_price($item['price']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td class="order-total"><?= format_price($item['price'] * $item['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" scope="row">Tổng thanh toán</th>
                        <th class="order-total"><?= format_price($order['total']) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

    <a class="order-back-link" href="<?= url($ordersPath) ?>">← Danh sách đơn hàng</a>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
