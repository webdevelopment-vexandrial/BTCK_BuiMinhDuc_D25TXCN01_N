<?php
require __DIR__ . '/includes/init.php';

$pageTitle = 'Giới thiệu';
require __DIR__ . '/includes/header.php';
?>
        <article class="product-detail">
            <div class="product-image">
                <img src="images/sp01.jpg" alt="Chuột không dây M1" width="400">
            </div>

            <div class="product-info">
                <h2>Chuột không dây M1</h2>
                <p class="price">Giá: <strong>250.000 đ</strong></p>
                <p>
                    Chuột không dây kết nối 2.4GHz, thiết kế đối xứng, nút bấm êm,
                    phù hợp cho học tập và làm việc văn phòng.
                </p>

                <h3>Thông số kỹ thuật</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Thông số</th>
                            <th>Giá trị</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Kết nối</td>
                            <td>Không dây 2.4GHz, đầu thu USB</td>
                        </tr>
                        <tr>
                            <td>Độ phân giải</td>
                            <td>1600 DPI</td>
                        </tr>
                        <tr>
                            <td>Pin</td>
                            <td>1 pin AA, dùng khoảng 12 tháng</td>
                        </tr>
                        <tr>
                            <td>Bảo hành</td>
                            <td>12 tháng</td>
                        </tr>
                    </tbody>
                </table>

                <p><a href="index.php">&laquo; Quay lại danh sách sản phẩm</a></p>
            </div>
        </article>
   <?php require __DIR__ . '/includes/footer.php'; ?>