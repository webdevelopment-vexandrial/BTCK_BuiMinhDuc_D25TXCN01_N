<?php
// Phần PHP: chạy trên máy chủ, người dùng không thấy đoạn code nyaf

$shop = "Cua hang Bui Minh Duc";
$hour = (int)date("G"); // Lấy giờ hiện tại (0-23)
$greeting = $hour < 12 ? "Chào buổi sáng" : ($hour < 18 ? "Chào buổi chiều" : "Chào buổi tối");

?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>PHP đầu tiên</title>
</head>
    <body>
        <h1><?= $greeting ?> từ <?= $shop ?></h1>
        <p>Trang này được tạo lúc <?= date('H:i:s') ?>.</p>
    </body>
</html>
