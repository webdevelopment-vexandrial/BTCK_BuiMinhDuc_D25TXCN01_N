<?php
require __DIR__ . '/../includes/init.php';
require_admin();

$statuses = order_statuses();
$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$filter = $_GET['status'] ?? '';
$filter = is_string($filter) && isset($statuses[$filter]) ? $filter : '';
$page = isset($_GET['page']) && is_scalar($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT) : 1;
$page = $page !== false && $page > 0 ? $page : 1;
$perPage = 20;

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
    $returnQuery = array_filter([
        'status' => isset($_POST['return_status']) && is_string($_POST['return_status'])
            && isset($statuses[$_POST['return_status']]) ? $_POST['return_status'] : '',
        'q' => isset($_POST['return_q']) && is_string($_POST['return_q']) ? trim($_POST['return_q']) : '',
        'page' => isset($_POST['return_page']) && filter_var($_POST['return_page'], FILTER_VALIDATE_INT) > 1
            ? (int) $_POST['return_page'] : '',
    ]);
    redirect('admin/orders.php' . ($returnQuery ? '?' . http_build_query($returnQuery) : ''));
}

$conditions = [];
$params = [];
if ($filter !== '') {
    $conditions[] = 'status = ?';
    $params[] = $filter;
}
if ($search !== '') {
    $conditions[] = '(CAST(id AS CHAR) LIKE ? OR id = ? OR fullname LIKE ? OR phone LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $orderIdSearch = preg_replace('/^DH-/i', '', $search);
    $orderIdSearch = ctype_digit($orderIdSearch) ? (int) $orderIdSearch : 0;
    array_push($params, $searchTerm, $orderIdSearch, $searchTerm, $searchTerm);
}
$whereSql = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

$stmt = $pdo->prepare('SELECT COUNT(*) FROM orders' . $whereSql);
$stmt->execute($params);
$totalOrders = (int) $stmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalOrders / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare('SELECT * FROM orders' . $whereSql . ' ORDER BY id DESC LIMIT ? OFFSET ?');
foreach ($params as $index => $value) {
    $stmt->bindValue($index + 1, $value);
}
$stmt->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();
$queryParams = array_filter(['status' => $filter, 'q' => $search]);
$pageUrl = static function ($targetPage) use ($queryParams) {
    return url('admin/orders.php?' . http_build_query($queryParams + ['page' => $targetPage]));
};

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
        <a href="<?= url('admin/orders.php' . ($search !== '' ? '?' . http_build_query(['q' => $search]) : '')) ?>"
           class="<?= $filter === '' ? 'active' : '' ?>">Tất cả</a>
        <?php foreach ($statuses as $value => $label): ?>
            <a href="<?= url('admin/orders.php?' . http_build_query(array_filter(['status' => $value, 'q' => $search]))) ?>"
               class="<?= $filter === $value ? 'active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
        <a href="<?= url('admin/product.php') ?>">Sản phẩm</a>
    </nav>

    <form action="<?= url('admin/orders.php') ?>" method="get" class="admin-order-search">
        <?php if ($filter !== ''): ?>
            <input type="hidden" name="status" value="<?= e($filter) ?>">
        <?php endif; ?>
        <label for="order-search">Tìm mã đơn, người nhận hoặc số điện thoại</label>
        <div>
            <input type="search" id="order-search" name="q" value="<?= e($search) ?>"
                   placeholder="Ví dụ: DH-0012 hoặc 09..." maxlength="100">
            <button type="submit" class="cart-button cart-button-primary">Tìm đơn hàng</button>
        </div>
    </form>

    <p class="admin-order-result-count">Tìm thấy <?= $totalOrders ?> đơn hàng.</p>

    <?php if (!$orders): ?>
        <div class="orders-empty">
            <span class="orders-empty-icon" aria-hidden="true">◇</span>
            <h3>Không có đơn hàng phù hợp</h3>
            <p>Thử đổi từ khóa tìm kiếm hoặc chọn trạng thái khác.</p>
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
                                    <input type="hidden" name="return_status" value="<?= e($filter) ?>">
                                    <input type="hidden" name="return_q" value="<?= e($search) ?>">
                                    <input type="hidden" name="return_page" value="<?= $page ?>">
                                    <select name="status" aria-label="Trạng thái đơn DH-<?= str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT) ?>">
                                        <?php
                                            $nextStatuses = array_merge(
                                                [$order['status']],
                                                order_status_transitions()[$order['status']] ?? []
                                            );
                                        ?>
                                        <?php foreach ($nextStatuses as $value): ?>
                                            <?php $label = $statuses[$value] ?? $value; ?>
                                            <option value="<?= $value ?>" <?= $order['status'] === $value ? 'selected' : '' ?>>
                                                <?= e($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="cart-button cart-button-secondary"
                                            <?= empty(order_status_transitions()[$order['status']]) ? 'disabled' : '' ?>>Lưu</button>
                                </form>
                            </td>
                            <td><a class="order-view-link" href="<?= url('order-detail.php?id=' . $order['id']) ?>">Chi tiết <span aria-hidden="true">→</span></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <nav class="admin-order-pagination" aria-label="Phân trang đơn hàng">
                <?php if ($page > 1): ?>
                    <a href="<?= e($pageUrl($page - 1)) ?>">← Trước</a>
                <?php endif; ?>
                <span>Trang <?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= e($pageUrl($page + 1)) ?>">Sau →</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>