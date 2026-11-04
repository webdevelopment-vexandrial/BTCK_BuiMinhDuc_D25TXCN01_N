<?php
// 1. Biến: luôn bắt đầu bằng dấu $ và có thể chứa chữ cái, số và dấu gạch dưới. Biến phân biệt chữ hoa chữ thường.
$name = "Chuột không dây M1";
$price = 299000;
$quantity = 2;

// 2. Chuỗi: nháy đơn giữ nguyên văn, nháy kép cho phép chèn biến và ký tự đặc biệt.
echo 'Tên: $name<br/>';
echo "Tên: $name<br/>";
echo 'Thành tiền: '.($price * $quantity).' đ <br/>';

// 3. Mảng đánh chỉ số và vòng lặp for kiểu C
$prices = [250000, 790000, 450000];
$total = 0;
for ($i = 0; $i < count($prices); $i++) {
    $total += $prices[$i];
}

echo "Tổng: $total<br>";

// 4. Mảng kết hợp: chỉ số là chuỗi, giống struct
$product = ['name' => 'USB 64GB U4', 'price' => 150000];
echo $product['name'] . ' giá ' . $product['price'] . '<br>';

// 5. foreach: duyệt mảng không cần biến chỉ số
foreach ($prices as $p) {
    echo number_format($p, 0, ',', '.') . ' đ<br>';
}

// 6. Hàm
function discount($amount, $percent){
    return $amount - ($amount * $percent / 100);
}
echo 'Sau giảm 10%: ' . discount(250000, 10) . '<br>';

// 7. Giá trị rỗng và toán tử ??
$coupon = "ABC";
echo 'Mã giảm giá: ' . ($coupon ?? 'không có') . '<br>';