<?php
require __DIR__ . '/includes/init.php';

// Dữ liệu tạm thời, viết cứng trong mã. Lab 3.4 sẽ lấy từ cơ sở dữ liệu.
// $products = [
//     ['id' => 1, 'name' => 'Chuột không dây M1', 'price' => 250000, 'image' => 'images/sp01.jpg'],
//     ['id' => 2, 'name' => 'Bàn phím cơ K2', 'price' => 790000, 'image' => 'images/sp02.jpg'],
//     ['id' => 3, 'name' => 'Tai nghe Bluetooth T3', 'price' => 450000, 'image' => 'images/sp03.jpg'],
//     ['id' => 4, 'name' => 'USB 64GB U4', 'price' => 150000, 'image' => 'images/sp04.jpg'],
//     ['id' => 5, 'name' => 'Webcam Full HD W5', 'price' => 520000, 'image' => 'images/sp05.jpg'],
//     ['id' => 6, 'name' => 'Đế tản nhiệt laptop D6', 'price' => 320000, 'image' => 'images/sp06.jpg'],
// ];

$stmt = $pdo->query('SELECT id, name, price, image FROM products ORDER BY id');
$products = $stmt->fetchAll();

$pageTitle = 'Trang chủ';
require __DIR__ . '/includes/header.php';
?>
<section>
    <h2>Sản phẩm nổi bật</h2>

    <div class="product-list">
        <?php foreach ($products as $product): ?>
         <article class="product-card">
            <img src="<?= url($product['image']) ?>" alt="<?= e($product['name']) ?>">
            <h3><?= e($product['name']) ?></h3>
            <p class="price"><?= format_price($product['price']) ?></p>
            <a href="<?= url('product.php?id=' . $product['id']) ?>" class="btn">Xem chi tiết</a>
            <button type="button" class="btn btn-accent add-to-cart"
                    data-id="<?= $product['id'] ?>">Thêm vào giỏ</button>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>