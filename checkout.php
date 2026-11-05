<?php
require __DIR__ . '/includes/init.php';
require_login();

[$items, $total] = get_cart_items($pdo);
if (!$items) {
    set_flash('error', 'Giỏ hàng đang trống.');
    redirect('cart.php');
}

// Điền sẵn thông tin nhận hàng từ tài khoản
$stmt = $pdo->prepare('SELECT fullname, phone, address FROM users WHERE id = ?');
$stmt->execute([current_user()['id']]);
$account = $stmt->fetch();

$errors = [];
$fullname = $account['fullname'];
$phone = $account['phone'];
$address = $account['address'];
$note = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $note = trim($_POST['note'] ?? '');

    if (mb_strlen($fullname) < 2 || mb_strlen($fullname) > 100) {
        $errors['fullname'] = 'Họ tên phải có từ 2 đến 100 ký tự.';
    }
    if (!preg_match('/^0[0-9]{9}$/', $phone)) {
        $errors['phone'] = 'Số điện thoại gồm 10 chữ số, bắt đầu bằng số 0.';
    }
    if (mb_strlen($address) < 10 || mb_strlen($address) > 255) {
        $errors['address'] = 'Địa chỉ phải có từ 10 đến 255 ký tự.';
    }
    if (mb_strlen($note) > 500) {
        $errors['note'] = 'Ghi chú không quá 500 ký tự.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // 1. Khóa từng dòng sản phẩm, đọc lại giá và tồn kho mới nhất
            $lock = $pdo->prepare('SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE');
            $lines = [];
            $orderTotal = 0;
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $lock->execute([$productId]);
                $product = $lock->fetch();
                if (!$product) {
                    throw new RuntimeException('Một sản phẩm trong giỏ không còn được bán.');
                }
                if ($product['stock'] < $quantity) {
                    throw new RuntimeException(
                        'Sản phẩm "' . $product['name'] . '" chỉ còn ' . $product['stock'] . ' cái.'
                    );
                }
                $product['quantity'] = $quantity;
                $lines[] = $product;
                $orderTotal += $product['price'] * $quantity;
            }

            // 2. Ghi đơn hàng
            $stmt = $pdo->prepare(
                'INSERT INTO orders (user_id, fullname, phone, address, note, total)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([current_user()['id'], $fullname, $phone, $address, $note, $orderTotal]);
            $orderId = (int) $pdo->lastInsertId();

            // 3. Ghi từng dòng hàng và trừ tồn kho
            $insertItem = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, price, quantity)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $reduceStock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
            foreach ($lines as $line) {
                $insertItem->execute([$orderId, $line['id'], $line['name'], $line['price'], $line['quantity']]);
                $reduceStock->execute([$line['quantity'], $line['id']]);
            }

            // 4. Mọi thứ thành công: xác nhận giao dịch
            $pdo->commit();

            unset($_SESSION['cart']);
            set_flash('success', 'Đặt hàng thành công. Mã đơn hàng của bạn là #' . $orderId . '.');
            redirect('order-detail.php?id=' . $orderId);
        } catch (RuntimeException $e) {
            // Lỗi nghiệp vụ (hết hàng...): hủy mọi thay đổi và báo cho người dùng
            $pdo->rollBack();
            $errors['order'] = $e->getMessage();
        } catch (PDOException $e) {
            // Lỗi CSDL: hủy mọi thay đổi, rồi để lỗi tiếp tục nổi lên
            $pdo->rollBack();
            throw $e;
        }
    }
}

$pageTitle = 'Đặt hàng';
require __DIR__ . '/includes/header.php';
?>
<section class="checkout-page">
    <a class="product-close checkout-close" href="<?= url('cart.php') ?>" aria-label="Đóng và quay lại giỏ hàng" title="Quay lại giỏ hàng">×</a>

    <header class="checkout-heading">
        <p class="checkout-eyebrow">HOÀN TẤT ĐƠN HÀNG</p>
        <h2>Thanh toán</h2>
        <p>Kiểm tra sản phẩm và nhập thông tin nhận hàng của bạn.</p>
    </header>

    <?php if (isset($errors['order'])): ?>
        <p class="alert alert-error checkout-error"><?= e($errors['order']) ?></p>
    <?php endif; ?>

    <div class="checkout-layout">
        <section class="checkout-summary">
            <div class="checkout-section-heading">
                <span class="checkout-step">01</span>
                <div>
                    <h3>Đơn hàng của bạn</h3>
                    <p><?= count($items) ?> sản phẩm</p>
                </div>
            </div>

            <div class="checkout-items">
                <?php foreach ($items as $item): ?>
                    <article class="checkout-item">
                        <div class="checkout-item-image">
                            <img src="<?= url($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                        </div>
                        <div class="checkout-item-info">
                            <h4><?= e($item['name']) ?></h4>
                            <p>Số lượng: <?= $item['quantity'] ?></p>
                        </div>
                        <strong><?= format_price($item['line_total']) ?></strong>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="checkout-total">
                <span>Tổng thanh toán</span>
                <strong><?= format_price($total) ?></strong>
            </div>
            <a class="checkout-back-link" href="<?= url('cart.php') ?>">← Chỉnh sửa giỏ hàng</a>
        </section>

        <section class="checkout-form-panel">
            <div class="checkout-section-heading">
                <span class="checkout-step">02</span>
                <div>
                    <h3>Thông tin nhận hàng</h3>
                    <p>Thông tin của bạn được dùng để giao đơn hàng.</p>
                </div>
            </div>

            <form action="<?= url('checkout.php') ?>" method="post" class="checkout-form">
                <?= csrf_field() ?>
                <div class="checkout-field">
                    <label for="fullname">Họ và tên người nhận</label>
                    <input type="text" id="fullname" name="fullname" value="<?= e($fullname) ?>"
                           autocomplete="name" maxlength="100" required>
                    <?php if (isset($errors['fullname'])): ?>
                        <span class="error"><?= e($errors['fullname']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="checkout-field">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" value="<?= e($phone) ?>"
                           autocomplete="tel" inputmode="numeric" maxlength="10" required>
                    <?php if (isset($errors['phone'])): ?>
                        <span class="error"><?= e($errors['phone']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="checkout-field">
                    <label for="address">Địa chỉ nhận hàng</label>
                    <textarea id="address" name="address" rows="3" autocomplete="street-address"
                              maxlength="255" required><?= e($address) ?></textarea>
                    <?php if (isset($errors['address'])): ?>
                        <span class="error"><?= e($errors['address']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="checkout-field">
                    <label for="note">Ghi chú <span>(không bắt buộc)</span></label>
                    <textarea id="note" name="note" rows="2" maxlength="500"
                              placeholder="Ví dụ: giao hàng sau 17 giờ"><?= e($note) ?></textarea>
                    <?php if (isset($errors['note'])): ?>
                        <span class="error"><?= e($errors['note']) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="checkout-submit">
                    Xác nhận đặt hàng <span aria-hidden="true">→</span>
                </button>
                <p class="checkout-secure-note">Vui lòng kiểm tra kỹ số điện thoại và địa chỉ trước khi xác nhận.</p>
            </form>
        </section>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
