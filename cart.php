<?php
require __DIR__ . '/includes/init.php';

[$items, $total] = get_cart_items($pdo);

$pageTitle = 'Giỏ hàng';
require __DIR__ . '/includes/header.php';
?>
<section class="cart-page">
    <a class="product-close cart-close" href="<?= url('index.php') ?>" aria-label="Đóng giỏ hàng và quay lại cửa hàng" title="Quay lại cửa hàng">×</a>

    <div class="cart-heading">
        <p class="cart-eyebrow">KIỂM TRA LẠI LỰA CHỌN CỦA BẠN</p>
        <h2>Giỏ hàng</h2>
        <p>Điều chỉnh số lượng trước khi tiến hành đặt hàng.</p>
    </div>

    <?php if (!$items): ?>
        <div class="cart-empty">
            <span class="cart-empty-icon" aria-hidden="true">◇</span>
            <h3>Giỏ hàng đang trống</h3>
            <p>Hãy khám phá cửa hàng và chọn sản phẩm bạn yêu thích.</p>
            <a class="cart-button cart-button-primary" href="<?= url('index.php') ?>">Khám phá sản phẩm <span aria-hidden="true">→</span></a>
        </div>
    <?php else: ?>
        <div class="cart-table-wrap">
            <form action="<?= url('cart-update.php') ?>" method="post">
                <?= csrf_field() ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th scope="col">Sản phẩm</th>
                            <th scope="col">Đơn giá</th>
                            <th scope="col">Số lượng</th>
                            <th scope="col">Thành tiền</th>
                            <th scope="col"><span class="sr-only">Thao tác</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr data-price="<?= $item['price'] ?>">
                                <td class="cart-product-name">
                                    <span class="cart-product-mark" aria-hidden="true">✦</span>
                                    <?= e($item['name']) ?>
                                </td>
                                <td class="cart-unit-price"><?= format_price($item['price']) ?></td>
                                <td>
                                    <input type="number" class="qty" name="qty[<?= $item['id'] ?>]"
                                           aria-label="Số lượng <?= e($item['name']) ?>"
                                           value="<?= $item['quantity'] ?>" min="1" max="99">
                                </td>
                                <td class="line-total"><?= format_price($item['line_total']) ?></td>
                                <td>
                                    <button type="submit" form="remove-form" name="remove"
                                            value="<?= $item['id'] ?>" class="cart-remove-button"
                                            data-confirm="Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?">Xóa</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" scope="row">Tổng thanh toán</th>
                            <th id="cart-total" colspan="2"><?= format_price($total) ?></th>
                        </tr>
                    </tfoot>
                </table>

                <div class="cart-actions">
                    <button type="submit" name="update" value="1" class="cart-button cart-button-secondary">Cập nhật giỏ hàng</button>
                    <button type="submit" name="clear" value="1" class="cart-button cart-button-danger"
                            data-confirm="Bạn có chắc muốn xóa toàn bộ giỏ hàng?">Xóa tất cả</button>
                    <a href="<?= url('checkout.php') ?>" class="cart-button cart-button-primary">Tiến hành đặt hàng <span aria-hidden="true">→</span></a>
                </div>
            </form>
        </div>

        <form id="remove-form" action="<?= url('cart-update.php') ?>" method="post">
            <?= csrf_field() ?>
        </form>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
