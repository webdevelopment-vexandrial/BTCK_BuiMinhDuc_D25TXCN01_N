<?php
require __DIR__ . '/includes/init.php';

$pageTitle = 'Giới thiệu';
require __DIR__ . '/includes/header.php';
?>
        <h2>Giỏ hàng</h2>

        <table class="cart-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr data-price="250000">
                    <td>Chuột không dây M1</td>
                    <td>250.000 đ</td>
                    <td><input type="number" class="qty" min="1" value="2"></td>
                    <td class="line-total"></td>
                </tr>
                <tr data-price="150000">
                    <td>USB 64GB U4</td>
                    <td>150.000 đ</td>
                    <td><input type="number" class="qty" min="1" value="3"></td>
                    <td class="line-total"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Tổng cộng</th>
                    <th id="cart-total"></th>
                </tr>
            </tfoot>
        </table>

        <p><button type="button" class="btn btn-accent">Đặt hàng</button></p>
 <?php require __DIR__ . '/includes/footer.php'; ?>