<?php
require __DIR__ . '/includes/init.php';

// Đã đăng nhập rồi thì không cần vào trang này nữa
if (is_logged_in()) {
    redirect(is_admin() ? 'admin/index.php' : 'index.php');
}

$error = '';
$email = '';
$lockSeconds = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailInput = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = is_string($emailInput) ? mb_strtolower(trim($emailInput)) : '';
    $password = is_string($password) ? $password : '';

    if ($email !== '' && strlen($email) <= 150 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $lockSeconds = login_lock_seconds($pdo, $email);
    }

    if ($lockSeconds > 0) {
        $error = 'Đăng nhập bị khóa do nhập sai mật khẩu 5 lần liên tiếp. Vui lòng thử lại sau 5 phút.';
    } elseif ($email === '' || strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email hoặc mật khẩu không đúng.';
    } else {
        $stmt = $pdo->prepare('SELECT id, fullname, email, password_hash, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            clear_login_failures($pdo, $email);
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'       => $user['id'],
                'fullname' => $user['fullname'],
                'email'    => $user['email'],
                'role'     => $user['role'],
            ];
            $welcome = $user['role'] === 'admin'
                ? 'Xin chào, ' . $user['fullname'] . '! Chào mừng bạn đến trang quản trị.'
                : 'Xin chào ' . $user['fullname'] . '!';
            set_flash('success', $welcome);
            if ($user['role'] === 'admin') {
                unset($_SESSION['redirect_after_login']);
                redirect('admin/index.php');
            }

            $destination = $_SESSION['redirect_after_login'] ?? '';
            unset($_SESSION['redirect_after_login']);
            if (
                is_string($destination)
                && str_starts_with($destination, BASE_URL)
                && !str_starts_with($destination, '//')
                && basename((string) parse_url($destination, PHP_URL_PATH)) !== 'login.php'
            ) {
                header('Location: ' . $destination);
                exit;
            }
            redirect('index.php');
        }

        $lockSeconds = $email !== '' ? record_login_failure($pdo, $email) : 0;
        $error = $lockSeconds > 0
            ? 'Đăng nhập bị khóa do nhập sai mật khẩu 5 lần liên tiếp. Vui lòng thử lại sau 5 phút.'
            : 'Email hoặc mật khẩu không đúng.';
    }
}

$pageTitle = 'Đăng nhập';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-page auth-login-page">
    <header class="auth-heading">
        <span class="auth-eyebrow">CHÀO MỪNG TRỞ LẠI</span>
        <h2>Đăng nhập</h2>
        <p>Đăng nhập để theo dõi đơn hàng và tiếp tục mua sắm.</p>
    </header>

    <form action="<?= url('login.php') ?>" method="post" class="form auth-form" <?= $lockSeconds > 0 ? 'data-lock-seconds="' . $lockSeconds . '"' : '' ?>>
        <?= csrf_field() ?>
        <?php if ($error): ?>
            <p class="alert alert-error" role="alert"><?= e($error) ?></p>
        <?php endif; ?>
        <p>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required <?= $lockSeconds > 0 ? 'disabled' : '' ?>>
        </p>
        <p>
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required <?= $lockSeconds > 0 ? 'disabled' : '' ?>>
        </p>
        <p class="auth-submit-row">
            <button type="submit" class="btn btn-accent auth-submit" <?= $lockSeconds > 0 ? 'disabled' : '' ?>>Đăng nhập <span aria-hidden="true">→</span></button>
        </p>
        <p class="auth-switch">Chưa có tài khoản? <a href="<?= url('register.php') ?>">Đăng ký ngay</a></p>
    </form>
</section>
<?php if ($lockSeconds > 0): ?>
    <script>
        window.setTimeout(function () {
            window.location.reload();
        }, <?= $lockSeconds * 1000 ?>);
    </script>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
