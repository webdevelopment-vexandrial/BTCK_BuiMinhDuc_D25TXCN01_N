<?php
require __DIR__ . '/includes/init.php';
require_login();

$stmt = $pdo->prepare('SELECT fullname, email, phone, address FROM users WHERE id = ?');
$stmt->execute([current_user()['id']]);
$profile = $stmt->fetch();
if (!$profile) {
    http_response_code(404);
    exit('Không tìm thấy tài khoản.');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = isset($_POST['fullname']) && is_string($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $phone = isset($_POST['phone']) && is_string($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) && is_string($_POST['address']) ? trim($_POST['address']) : '';

    if (mb_strlen($fullname) < 2 || mb_strlen($fullname) > 100) {
        $errors['fullname'] = 'Họ tên phải có từ 2 đến 100 ký tự.';
    }
    if (!preg_match('/^0[0-9]{9}$/', $phone)) {
        $errors['phone'] = 'Số điện thoại gồm 10 chữ số, bắt đầu bằng số 0.';
    }
    if (mb_strlen($address) > 255) {
        $errors['address'] = 'Địa chỉ không được quá 255 ký tự.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE users SET fullname = ?, phone = ?, address = ? WHERE id = ?');
        $stmt->execute([$fullname, $phone, $address, current_user()['id']]);
        $_SESSION['user']['fullname'] = $fullname;
        set_flash('success', 'Đã cập nhật thông tin tài khoản.');
        redirect('profile.php');
    }

    $profile['fullname'] = $fullname;
    $profile['phone'] = $phone;
    $profile['address'] = $address;
}

$pageTitle = 'Tài khoản của tôi';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-page profile-page">
    <header class="auth-heading">
        <span class="auth-eyebrow">THÔNG TIN CÁ NHÂN</span>
        <h2>Tài khoản của tôi</h2>
        <p>Cập nhật thông tin nhận hàng và liên hệ của bạn.</p>
    </header>

    <form action="<?= url('profile.php') ?>" method="post" class="form auth-form">
        <?= csrf_field() ?>
        <?php if ($errors): ?>
            <p class="alert alert-error" role="alert">Vui lòng kiểm tra lại thông tin.</p>
        <?php endif; ?>
        <p>
            <label for="fullname">Họ và tên</label>
            <input type="text" id="fullname" name="fullname" value="<?= e($profile['fullname']) ?>"
                   autocomplete="name" minlength="2" maxlength="100" required>
            <span class="error"><?= e($errors['fullname'] ?? '') ?></span>
        </p>
        <p>
            <label for="email">Email</label>
            <input type="email" id="email" value="<?= e($profile['email']) ?>" autocomplete="email" disabled>
        </p>
        <p>
            <label for="phone">Số điện thoại</label>
            <input type="tel" id="phone" name="phone" value="<?= e($profile['phone']) ?>"
                   autocomplete="tel" pattern="0[0-9]{9}" required>
            <span class="error"><?= e($errors['phone'] ?? '') ?></span>
        </p>
        <p>
            <label for="address">Địa chỉ nhận hàng</label>
            <textarea id="address" name="address" rows="3" maxlength="255"
                      autocomplete="street-address"><?= e($profile['address']) ?></textarea>
            <span class="error"><?= e($errors['address'] ?? '') ?></span>
        </p>
        <p class="auth-submit-row">
            <button type="submit" class="btn btn-accent auth-submit">Lưu thông tin <span aria-hidden="true">→</span></button>
        </p>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
