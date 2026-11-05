<?php
require __DIR__ . '/includes/init.php';
require_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $currentPassword = is_string($currentPassword) ? $currentPassword : '';
    $newPassword = is_string($newPassword) ? $newPassword : '';
    $confirmPassword = is_string($confirmPassword) ? $confirmPassword : '';

    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([current_user()['id']]);
    $passwordHash = $stmt->fetchColumn();

    if ($passwordHash === false || !password_verify($currentPassword, $passwordHash)) {
        $errors['current_password'] = 'Mật khẩu hiện tại không đúng.';
    }
    if (strlen($newPassword) < 6) {
        $errors['new_password'] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    }
    if ($newPassword !== $confirmPassword) {
        $errors['confirm_password'] = 'Hai mật khẩu mới không khớp nhau.';
    }
    if (!$errors && password_verify($newPassword, $passwordHash)) {
        $errors['new_password'] = 'Mật khẩu mới phải khác mật khẩu hiện tại.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), current_user()['id']]);
        set_flash('success', 'Đổi mật khẩu thành công.');
        redirect('change-password.php');
    }
}

$pageTitle = 'Đổi mật khẩu';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-page auth-login-page">
    <header class="auth-heading">
        <span class="auth-eyebrow">BẢO MẬT TÀI KHOẢN</span>
        <h2>Đổi mật khẩu</h2>
        <p>Nhập mật khẩu hiện tại để xác nhận thay đổi.</p>
    </header>

    <form action="<?= url('change-password.php') ?>" method="post" class="form auth-form">
        <?= csrf_field() ?>
        <?php if ($errors): ?>
            <p class="alert alert-error" role="alert">Vui lòng kiểm tra lại thông tin bên dưới.</p>
        <?php endif; ?>
        <p>
            <label for="current_password">Mật khẩu hiện tại</label>
            <input type="password" id="current_password" name="current_password"
                   autocomplete="current-password" required>
            <span class="error"><?= e($errors['current_password'] ?? '') ?></span>
        </p>
        <p>
            <label for="new_password">Mật khẩu mới</label>
            <input type="password" id="new_password" name="new_password"
                   autocomplete="new-password" minlength="6" required>
            <span class="error"><?= e($errors['new_password'] ?? '') ?></span>
        </p>
        <p>
            <label for="confirm_password">Nhập lại mật khẩu mới</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   autocomplete="new-password" minlength="6" required>
            <span class="error"><?= e($errors['confirm_password'] ?? '') ?></span>
        </p>
        <p class="auth-submit-row">
            <button type="submit" class="btn btn-accent auth-submit">Lưu mật khẩu mới <span aria-hidden="true">→</span></button>
        </p>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
