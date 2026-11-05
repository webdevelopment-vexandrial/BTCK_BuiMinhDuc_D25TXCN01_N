<?php
require __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Chỉ chấp nhận yêu cầu POST.');
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    set_flash('error', 'Mã sản phẩm không hợp lệ.');
    redirect('admin/product.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        set_flash('success', 'Đã xóa sản phẩm.');
    } else {
        set_flash('error', 'Sản phẩm không tồn tại.');
    }
} catch (PDOException $e) {
    if ($e->getCode() !== '23000') {
        throw $e;
    }
    set_flash('error', 'Không thể xóa: sản phẩm đã có trong đơn hàng. Hãy đặt tồn kho về 0 để ngừng bán.');
}

redirect('admin/product.php');