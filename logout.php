<?php
require __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// Chỉ xóa thông tin đăng nhập; giỏ hàng trong session vẫn được giữ
unset($_SESSION['user']);
session_regenerate_id(true);

set_flash('success', 'Bạn đã đăng xuất.');
redirect('index.php');
