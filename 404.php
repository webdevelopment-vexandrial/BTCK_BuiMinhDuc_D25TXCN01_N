<?php
require_once __DIR__ . '/config/config.php';
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
require_once __DIR__ . '/includes/functions.php';

http_response_code(404);
$pageTitle = 'Không tìm thấy trang';
require __DIR__ . '/includes/header.php';
?>
<h2>Không tìm thấy trang</h2>
<p>Địa chỉ bạn vừa mở không tồn tại. <a href="<?= url('index.php') ?>">Về trang chủ</a></p>
<?php require __DIR__ . '/includes/footer.php'; ?>