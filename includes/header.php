<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Trang chủ') ?> - <?= APP_NAME ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= url('images/favicon.svg') ?>">
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
    <script src="<?= url('js/main.js') ?>" defer></script>
</head>
<body>
    <header class="site-header">
        <h1><?= APP_NAME ?></h1>
        <nav class="main-nav">
            <a href="<?= url('index.php') ?>">Trang chủ</a>
            <a href="<?= url('about.php') ?>">Giới thiệu</a>
            <a href="<?= url('contact.php') ?>">Liên hệ</a>
            <a href="<?= url('cart.php') ?>">Giỏ hàng (<span id="cart-count"><?= cart_count() ?></span>)</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= url('orders.php') ?>">Đơn hàng</a>
                <form action="<?= url('logout.php') ?>" method="post" class="inline-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="main-nav-button">Đăng xuất (<?= e(current_user()['fullname']) ?>)</button>
                </form>
                <?php if (is_admin()): ?>
                    <a href="<?= url('admin/index.php') ?>">Tổng quan</a>
                    <a href="<?= url('admin/product.php') ?>">Sản phẩm</a>
                    <a href="<?= url('admin/categories.php') ?>">Danh mục</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= url('login.php') ?>">Đăng nhập</a>
                <a href="<?= url('register.php') ?>">Đăng ký</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <?php $flash = get_flash(); ?>
        <?php if ($flash): ?>
            <p class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
        <?php endif; ?>
