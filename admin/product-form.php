<?php
require __DIR__ . '/../includes/init.php';
require_admin();

// Có id trên URL là sửa, không có là thêm mới
$id = (int) ($_GET['id'] ?? 0);
$product = [
    'category_id' => 0,
    'name'        => '',
    'price'       => '',
    'stock'       => '',
    'description' => '',
    'image'       => 'images/sp01.jpg',
];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        set_flash('error', 'Sản phẩm không tồn tại.');
        redirect('admin/product.php');
    }
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY id')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product['category_id'] = (int) ($_POST['category_id'] ?? 0);
    $product['name']        = trim($_POST['name'] ?? '');
    $product['price']       = (int) ($_POST['price'] ?? 0);
    $product['stock']       = (int) ($_POST['stock'] ?? -1);
    $product['description'] = trim($_POST['description'] ?? '');

    if (!in_array($product['category_id'], array_map('intval', array_column($categories, 'id')), true)) {
        $errors['category_id'] = 'Hãy chọn danh mục.';
    }

    if (mb_strlen($product['name']) < 2 || mb_strlen($product['name']) > 200) {
        $errors['name'] = 'Tên sản phẩm phải có từ 2 đến 200 ký tự.';
    }

    if ($product['price'] < 1000) {
        $errors['price'] = 'Giá phải từ 1.000 đ trở lên.';
    }

    if ($product['stock'] < 0) {
        $errors['stock'] = 'Tồn kho phải là số từ 0 trở lên.';
    }

    // Ảnh: chỉ xử lý khi người dùng có chọn tệp; không chọn thì giữ ảnh cũ
    if (!$errors && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $product['image'] = upload_image($_FILES['image']);
        } catch (RuntimeException $e) {
            $errors['image'] = $e->getMessage();
        }
    }

    if (!$errors) {
        $values = [
            $product['category_id'], $product['name'], $product['price'],
            $product['stock'], $product['description'], $product['image'],
        ];

        if ($id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE products
                 SET category_id = ?, name = ?, price = ?, stock = ?, description = ?, image = ?
                 WHERE id = ?'
            );
            $values[] = $id;
            $stmt->execute($values);
            set_flash('success', 'Đã cập nhật sản phẩm.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO products (category_id, name, price, stock, description, image)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute($values);
            set_flash('success', 'Đã thêm sản phẩm.');
        }

        redirect('admin/product.php');
    }
}

$pageTitle = $id > 0 ? 'Sửa sản phẩm' : 'Thêm sản phẩm';
$action = 'admin/product-form.php' . ($id > 0 ? '?id=' . $id : '');
require __DIR__ . '/../includes/header.php';
?>

<h2><?= $pageTitle ?></h2>

<form action="<?= url($action) ?>" method="post" enctype="multipart/form-data" class="form">
    <?= csrf_field() ?>
    <p>
        <label for="name">Tên sản phẩm</label><br>
        <input type="text" id="name" name="name" value="<?= e($product['name']) ?>" required>
        <span class="error"><?= $errors['name'] ?? '' ?></span>
    </p>

    <p>
        <label for="category_id">Danh mục</label><br>
        <select id="category_id" name="category_id" required>
            <option value="">-- Chọn --</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"
                    <?= $category['id'] === $product['category_id'] ? 'selected' : '' ?>>
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="error"><?= $errors['category_id'] ?? '' ?></span>
    </p>

    <p>
        <label for="price">Giá (đ)</label><br>
        <input type="number" id="price" name="price" value="<?= e($product['price']) ?>" min="1000" step="1000" required>
        <span class="error"><?= $errors['price'] ?? '' ?></span>
    </p>

    <p>
        <label for="stock">Tồn kho</label><br>
        <input type="number" id="stock" name="stock" value="<?= e($product['stock']) ?>" min="0" required>
        <span class="error"><?= $errors['stock'] ?? '' ?></span>
    </p>

    <p>
        <label for="description">Mô tả</label><br>
        <textarea id="description" name="description" rows="5"><?= e($product['description']) ?></textarea>
    </p>

    <p>
        <label for="image">Ảnh sản phẩm (JPG, PNG, WebP; tối đa 2 MB)</label><br>
        <img src="<?= url($product['image']) ?>" alt="" class="thumb">
        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
        <span class="error"><?= $errors['image'] ?? '' ?></span>
    </p>

    <p>
        <button type="submit" class="btn btn-accent">Lưu</button>
        <a href="<?= url('admin/product.php') ?>">Hủy</a>
    </p>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>