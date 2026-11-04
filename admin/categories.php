<?php
require __DIR__ . '/../includes/init.php';
require_admin();

$errors = [];
$formCategory = ['id' => 0, 'name' => ''];
$editing = false;

if (isset($_GET['edit'])) {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($editId === false || $editId < 1) {
        http_response_code(400);
        exit('Mã danh mục không hợp lệ.');
    }

    $stmt = $pdo->prepare('SELECT id, name FROM categories WHERE id = ?');
    $stmt->execute([$editId]);
    $category = $stmt->fetch();
    if (!$category) {
        set_flash('error', 'Danh mục không tồn tại.');
        redirect('admin/categories.php');
    }
    $formCategory = $category;
    $editing = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $categoryId = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);

    if ($action === 'delete') {
        if ($categoryId === false || $categoryId < 1) {
            http_response_code(400);
            exit('Mã danh mục không hợp lệ.');
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $stmt->execute([$categoryId]);
        if ((int) $stmt->fetchColumn() > 0) {
            set_flash('error', 'Không thể xóa danh mục đang có sản phẩm.');
            redirect('admin/categories.php');
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$categoryId]);
            $deleted = $stmt->rowCount() > 0;
            set_flash($deleted ? 'success' : 'error', $deleted ? 'Đã xóa danh mục.' : 'Danh mục không tồn tại.');
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            set_flash('error', 'Không thể xóa danh mục đang có sản phẩm.');
        }
        redirect('admin/categories.php');
    }

    if ($action !== 'save') {
        http_response_code(400);
        exit('Thao tác danh mục không hợp lệ.');
    }

    $name = isset($_POST['name']) && is_string($_POST['name']) ? trim($_POST['name']) : '';
    if ($categoryId === false || $categoryId < 0) {
        $errors['category'] = 'Mã danh mục không hợp lệ.';
    }
    $formCategory = ['id' => $categoryId === false ? 0 : $categoryId, 'name' => $name];
    $editing = $formCategory['id'] > 0;

    if (mb_strlen($name) < 1 || mb_strlen($name) > 100) {
        $errors['name'] = 'Tên danh mục phải có từ 1 đến 100 ký tự.';
    }

    if ($editing) {
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
        $stmt->execute([$formCategory['id']]);
        if (!$stmt->fetch()) {
            $errors['category'] = 'Danh mục cần sửa không còn tồn tại.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE name = ? AND id <> ?');
        $stmt->execute([$name, $formCategory['id']]);
        if ($stmt->fetch()) {
            $errors['name'] = 'Tên danh mục đã tồn tại.';
        }
    }

    if (!$errors) {
        if ($editing) {
            $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
            $stmt->execute([$name, $formCategory['id']]);
            set_flash('success', 'Đã cập nhật danh mục.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$name]);
            set_flash('success', 'Đã thêm danh mục.');
        }
        redirect('admin/categories.php');
    }
}

$categories = $pdo->query(
    'SELECT c.id, c.name, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id, c.name
     ORDER BY c.id'
)->fetchAll();

$pageTitle = 'Quản lý danh mục';
require __DIR__ . '/../includes/header.php';
?>
<h2>Quản lý danh mục</h2>

<p class="cart-actions">
    <a href="<?= url('admin/index.php') ?>" class="btn">Tổng quan quản trị</a>
    <a href="<?= url('admin/product.php') ?>" class="btn">Quản lý sản phẩm</a>
</p>

<h3><?= $editing ? 'Sửa danh mục' : 'Thêm danh mục' ?></h3>
<?php if (isset($errors['category'])): ?>
    <p class="alert alert-error"><?= e($errors['category']) ?></p>
<?php endif; ?>
<form action="<?= url('admin/categories.php') ?>" method="post" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="category_id" value="<?= (int) $formCategory['id'] ?>">
    <p>
        <label for="name">Tên danh mục</label><br>
        <input type="text" id="name" name="name" value="<?= e($formCategory['name']) ?>"
               maxlength="100" required>
        <span class="error"><?= e($errors['name'] ?? '') ?></span>
    </p>
    <p>
        <button type="submit" class="btn btn-accent"><?= $editing ? 'Lưu thay đổi' : 'Thêm danh mục' ?></button>
        <?php if ($editing): ?>
            <a href="<?= url('admin/categories.php') ?>">Hủy sửa</a>
        <?php endif; ?>
    </p>
</form>

<h3>Danh sách danh mục</h3>
<?php if (!$categories): ?>
    <p>Chưa có danh mục nào.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Mã</th>
                <th>Tên danh mục</th>
                <th>Số sản phẩm</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= (int) $category['id'] ?></td>
                    <td><?= e($category['name']) ?></td>
                    <td><?= (int) $category['product_count'] ?></td>
                    <td>
                        <a href="<?= url('admin/categories.php?edit=' . $category['id']) ?>" class="btn">Sửa</a>
                        <form action="<?= url('admin/categories.php') ?>" method="post" class="inline-form"
                              onsubmit="return confirm('Xóa danh mục này?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>">
                            <button type="submit" class="btn btn-danger"
                                <?= (int) $category['product_count'] > 0 ? 'disabled title="Danh mục đang có sản phẩm"' : '' ?>>
                                Xóa
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
