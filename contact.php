<?php
require __DIR__ . '/includes/init.php';

$errors = [];
$fullname = '';
$email = '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = isset($_POST['fullname']) && is_string($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) && is_string($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) && is_string($_POST['message']) ? trim($_POST['message']) : '';

    if (mb_strlen($fullname) < 2 || mb_strlen($fullname) > 100) {
        $errors['fullname'] = 'Họ tên phải có từ 2 đến 100 ký tự.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors['email'] = 'Email không hợp lệ.';
    }
    if (mb_strlen($subject) < 2 || mb_strlen($subject) > 200) {
        $errors['subject'] = 'Tiêu đề phải có từ 2 đến 200 ký tự.';
    }
    if (mb_strlen($message) < 10 || mb_strlen($message) > 5000) {
        $errors['message'] = 'Nội dung phải có từ 10 đến 5.000 ký tự.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO contacts (fullname, email, subject_line, message)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$fullname, $email, $subject, $message]);
        set_flash('success', 'Đã gửi lời nhắn. Cảm ơn bạn đã liên hệ!');
        redirect('contact.php');
    }
}

$pageTitle = 'Liên hệ';
require __DIR__ . '/includes/header.php';
?>
<section class="contact-page">
    <header class="contact-heading">
        <span class="about-eyebrow">CHÚNG TÔI LUÔN Ở ĐÂY</span>
        <h2>Kết nối với cửa hàng</h2>
        <p>Để lại lời nhắn hoặc liên hệ trực tiếp, chúng tôi sẽ hỗ trợ bạn sớm nhất.</p>
    </header>

    <div class="contact-layout">
        <aside class="contact-info">
            <span class="contact-info-label">THÔNG TIN CỬA HÀNG</span>
            <h3>Rất vui được hỗ trợ bạn.</h3>
            <a href="mailto:hotro@minhduc.example">
                <span class="contact-icon" aria-hidden="true">@</span>
                <span><small>Email hỗ trợ</small>hotro@minhduc.example</span>
            </a>
            <a href="tel:0900000000">
                <span class="contact-icon" aria-hidden="true">☎</span>
                <span><small>Điện thoại</small>0900 000 000</span>
            </a>
            <div class="contact-address">
                <span class="contact-icon" aria-hidden="true">⌖</span>
                <span><small>Địa chỉ</small>123 Đường Số 1, TP. Biên Hòa, Đồng Nai</span>
            </div>
            <p class="contact-hours">Mỗi ngày · 8:00 – 21:00</p>
            <div class="contact-info-table-wrap">
                <table class="contact-info-table">
                    <tbody>
                        <tr><th scope="row">Kênh phản hồi</th><td>Email và điện thoại</td></tr>
                        <tr><th scope="row">Thời gian</th><td>Trong giờ mở cửa</td></tr>
                        <tr><th scope="row">Hỗ trợ</th><td>Tư vấn sản phẩm và đơn hàng</td></tr>
                    </tbody>
                </table>
            </div>
        </aside>

        <form action="<?= url('contact.php') ?>" method="post" class="form contact-form">
            <?= csrf_field() ?>
            <h3>Gửi lời nhắn</h3>
            <p>
                <label for="fullname">Họ và tên</label>
                <input type="text" id="fullname" name="fullname" value="<?= e($fullname) ?>"
                       autocomplete="name" placeholder="Tên của bạn" minlength="2" maxlength="100" required>
                <span class="error"><?= e($errors['fullname'] ?? '') ?></span>
            </p>
            <p>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>"
                       autocomplete="email" placeholder="ban@example.com" maxlength="150" required>
                <span class="error"><?= e($errors['email'] ?? '') ?></span>
            </p>
            <p>
                <label for="subject">Tiêu đề</label>
                <input type="text" id="subject" name="subject" value="<?= e($subject) ?>"
                       placeholder="Bạn cần chúng tôi hỗ trợ gì?" minlength="2" maxlength="200" required>
                <span class="error"><?= e($errors['subject'] ?? '') ?></span>
            </p>
            <p>
                <label for="message">Nội dung</label>
                <textarea id="message" name="message" rows="5" minlength="10" maxlength="5000"
                          placeholder="Viết lời nhắn của bạn..." required><?= e($message) ?></textarea>
                <span class="error"><?= e($errors['message'] ?? '') ?></span>
            </p>
            <p class="contact-submit-row">
                <button type="submit" class="btn contact-submit">Gửi lời nhắn <span aria-hidden="true">→</span></button>
            </p>
        </form>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
