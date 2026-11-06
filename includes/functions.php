<?php
// Các hàm dùng chung của Cua hang Bui Minh Duc.

// ===== 1. Hiển thị =====

// Mã hóa ký tự đặc biệt trước khi in ra HTML (chống XSS)
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Định dạng tiền: 250000 -> "250.000 đ"
function format_price($amount)
{
    return number_format((int) $amount, 0, ',', '.') . ' đ';
}

// Tạo đường dẫn tuyệt đối trong website: url('cart.php') -> "/cua_hang_bui_minh_duc/cart.php"
function url($path = '')
{
    return BASE_URL . ltrim($path, '/');
}

// ===== 2. Chuyển hướng và thông báo một lần =====

function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

// $type là 'success' hoặc 'error'
function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Lấy thông báo ra và xóa luôn, nên nó chỉ hiện đúng một lần
function get_flash()
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// ===== 3. Tài khoản =====

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in()
{
    return current_user() !== null;
}

function is_admin()
{
    return is_logged_in() && current_user()['role'] === 'admin';
}

// Đặt ở đầu những trang bắt buộc đăng nhập
function require_login()
{
    if (!is_logged_in()) {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $requestPath = parse_url($requestUri, PHP_URL_PATH);
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '/';
        if (
            $_SERVER['REQUEST_METHOD'] === 'GET'
            && is_string($requestPath)
            && str_starts_with($requestPath, $basePath)
            && !str_starts_with(substr($requestPath, strlen($basePath)), '/')
            && basename($requestPath) !== 'login.php'
        ) {
            $_SESSION['redirect_after_login'] = $requestUri;
        }
        set_flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect('login.php');
    }
}

function login_lock_seconds($pdo, $email)
{
    $stmt = $pdo->prepare(
        'SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), locked_until))
         FROM login_attempts
         WHERE email = ? AND locked_until > NOW()'
    );
    $stmt->execute([$email]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function record_login_failure($pdo, $email)
{
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT IGNORE INTO login_attempts (email) VALUES (?)');
    $stmt->execute([$email]);

    $stmt = $pdo->prepare(
        'SELECT failed_attempts,
                locked_until,
                (locked_until IS NOT NULL AND locked_until > NOW()) AS is_locked,
                GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), locked_until)) AS seconds_remaining
         FROM login_attempts
         WHERE email = ?
         FOR UPDATE'
    );
    $stmt->execute([$email]);
    $attempt = $stmt->fetch();

    if ((int) $attempt['is_locked'] === 1) {
        $pdo->commit();
        return (int) $attempt['seconds_remaining'];
    }

    $failedAttempts = $attempt['locked_until'] !== null ? 0 : (int) $attempt['failed_attempts'];
    $failedAttempts++;
    $locked = $failedAttempts >= 5;

    $stmt = $pdo->prepare(
        'UPDATE login_attempts
         SET failed_attempts = ?,
             locked_until = IF(? = 1, DATE_ADD(NOW(), INTERVAL 5 MINUTE), NULL)
         WHERE email = ?'
    );
    $stmt->execute([$failedAttempts, $locked ? 1 : 0, $email]);
    $pdo->commit();

    return $locked ? 300 : 0;
}

function clear_login_failures($pdo, $email)
{
    $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE email = ?');
    $stmt->execute([$email]);
}

function update_order_status($pdo, $orderId, $status, $userId = null)
{
    if (!is_string($status) || !isset(order_statuses()[$status])) {
        throw new RuntimeException('Trạng thái đơn hàng không hợp lệ.');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT user_id, status FROM orders WHERE id = ? FOR UPDATE');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) {
            throw new RuntimeException('Đơn hàng không tồn tại.');
        }
        if ($userId !== null && (int) $order['user_id'] !== (int) $userId) {
            throw new RuntimeException('Không tìm thấy đơn hàng.');
        }
        if ($userId !== null && ($order['status'] !== 'pending' || $status !== 'cancelled')) {
            throw new RuntimeException('Chỉ có thể hủy đơn hàng đang chờ xử lý.');
        }

        if ($order['status'] === $status) {
            $pdo->commit();
            return;
        }

        $allowedTransitions = order_status_transitions()[$order['status']] ?? [];
        if (!in_array($status, $allowedTransitions, true)) {
            throw new RuntimeException('Không thể chuyển đơn hàng từ trạng thái hiện tại sang trạng thái đã chọn.');
        }

        if ($status === 'cancelled') {
            $stmt = $pdo->prepare(
                'SELECT product_id, SUM(quantity) AS quantity
                 FROM order_items
                 WHERE order_id = ?
                 GROUP BY product_id
                 ORDER BY product_id'
            );
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll();
            $restoreStock = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
            foreach ($items as $item) {
                $restoreStock->execute([(int) $item['quantity'], (int) $item['product_id']]);
            }
        }

        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

// ===== 4. Giỏ hàng =====
// Giỏ hàng nằm trong session, dạng [mã sản phẩm => số lượng]

function cart_count()
{
    return array_sum($_SESSION['cart'] ?? []);
}

// Trả về [danh sách dòng hàng, tổng tiền]; tên và giá luôn lấy từ CSDL
function get_cart_items($pdo)
{
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        return [[], 0];
    }

    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price, image, stock FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($cart));

    $items = [];
    $total = 0;
    foreach ($stmt->fetchAll() as $product) {
        $quantity = $cart[$product['id']];
        $product['quantity'] = $quantity;
        $product['line_total'] = $product['price'] * $quantity;
        $total += $product['line_total'];
        $items[] = $product;
    }
    return [$items, $total];
}

// ===== 5. Đơn hàng =====

function order_statuses()
{
    return [
        'pending'   => 'Chờ xử lý',
        'shipping'  => 'Đang giao',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy',
    ];
}

function order_status_transitions()
{
    return [
        'pending'   => ['shipping', 'cancelled'],
        'shipping'  => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];
}

function order_status_label($status)
{
    return order_statuses()[$status] ?? $status;
}

// ===== 6. Quản trị =====

// Đặt ở đầu mọi trang trong thư mục admin/
function require_admin()
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        $pageTitle = '403 - Không có quyền truy cập';
        require __DIR__ . '/forbidden.php';
        exit;
    }
}

// ===== 7. Tải ảnh lên =====

// Trả về đường dẫn ảnh đã lưu (ví dụ "upload/3f2a....jpg"); ném ngoại lệ nếu tệp không hợp lệ
function upload_image($file)
{
    if (!is_array($file) || !isset($file['error'], $file['tmp_name']) || !is_int($file['error'])) {
        throw new RuntimeException('Thông tin tệp tải lên không hợp lệ.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Tải tệp lên thất bại (mã lỗi ' . $file['error'] . ').');
    }
    if (!is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Tệp tải lên không hợp lệ.');
    }

    $size = filesize($file['tmp_name']);
    if ($size === false || $size === 0) {
        throw new RuntimeException('Tệp ảnh rỗng hoặc không đọc được.');
    }
    if ($size > 2 * 1024 * 1024) {
        throw new RuntimeException('Ảnh không được lớn hơn 2 MB.');
    }

    // Kiểm tra cả MIME và cấu trúc ảnh; không tin tên hoặc MIME do trình duyệt gửi lên.
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $info = getimagesize($file['tmp_name']);
    if ($info === false || !isset($extensions[$mime]) || $info['mime'] !== $mime) {
        throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.');
    }

    // Tự đặt tên mới, ngẫu nhiên, để không trùng và không dùng tên do người dùng gửi lên
    $name = bin2hex(random_bytes(8)) . '.' . $extensions[$info['mime']];
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../upload/' . $name)) {
        throw new RuntimeException('Không lưu được tệp. Kiểm tra quyền ghi của thư mục upload.');
    }
    return 'upload/' . $name;
}

function delete_uploaded_image_if_unused($pdo, $imagePath)
{
    if (
        !is_string($imagePath)
        || !preg_match('~\Aupload/[a-f0-9]{16}\.(?:jpg|png|webp)\z~', $imagePath)
    ) {
        return;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE image = ?');
    $stmt->execute([$imagePath]);
    if ((int) $stmt->fetchColumn() > 0) {
        return;
    }

    $uploadDirectory = realpath(__DIR__ . '/../upload');
    $imageFile = realpath(__DIR__ . '/../' . $imagePath);
    if (
        $uploadDirectory !== false
        && $imageFile !== false
        && dirname($imageFile) === $uploadDirectory
        && is_file($imageFile)
        && !unlink($imageFile)
    ) {
        throw new RuntimeException('Đã cập nhật sản phẩm nhưng không xóa được ảnh cũ.');
    }
}

// ===== 8. Chống giả mạo yêu cầu (CSRF) =====

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// In ra ô ẩn chứa mã; đặt bên trong mọi form dùng method="post"
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify()
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Yêu cầu không hợp lệ. Vui lòng quay lại, tải lại trang và thử lại.');
    }
}