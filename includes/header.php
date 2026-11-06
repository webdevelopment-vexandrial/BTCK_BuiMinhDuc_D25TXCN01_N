<!DOCTYPE html>
<html lang="vi" class="scroll-smooth dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($pageTitle ?? 'Trang chủ') ?> - <?= APP_NAME ?></title>

  <link rel="icon" type="image/svg+xml" href="<?= url('images/favicon.svg') ?>" />
  <!-- Tailwind CDN (sử dụng config trong script bên dưới) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: '#0ea5e9',   // cyan
            accent: '#f97316',    // orange
            bgDark: '#111827',
            cardBg: '#1f2937',
          },
          fontFamily: { sans: ['"Helvetica Neue"', 'Helvetica', 'Arial', 'sans-serif'] },
        },
      },
    };
  </script>

  <link rel="stylesheet" href="<?= url('css/style.css') ?>" />
  <script src="<?= url('js/main.js') ?>" defer></script>
  <script src="<?= url('js/gallary.js') ?>" defer></script>
</head>
<body class="bg-bgDark text-white">
  <?php
    $currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
  ?>

  <!-- Sticky transparent header -->
  <header id="site-header"
          class="fixed inset-x-0 top-0 z-50 flex items-center justify-between px-6 py-4 transition-all duration-300 backdrop-blur-md">
    <a class="site-brand" href="<?= url(is_admin() ? 'admin/index.php' : 'index.php') ?>">
      <img src="<?= url('images/logo-avt.jpg') ?>" alt="Logo cửa hàng">
      <h1 class="text-2xl font-bold text-white"><?= e(APP_NAME) ?></h1>
    </a>
    <nav class="flex items-center gap-4 text-sm">
      <?php if (is_admin()): ?>
          <a href="<?= url('admin/index.php') ?>" class="hover:text-primary <?= $currentPage === 'index.php' && str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') ? 'active' : '' ?>" <?= $currentPage === 'index.php' && str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') ? 'aria-current="page"' : '' ?>>Tổng quan</a>
          <a href="<?= url('admin/product.php') ?>" class="hover:text-primary <?= $currentPage === 'product.php' || $currentPage === 'product-form.php' ? 'active' : '' ?>" <?= $currentPage === 'product.php' || $currentPage === 'product-form.php' ? 'aria-current="page"' : '' ?>>Sản phẩm</a>
          <a href="<?= url('admin/categories.php') ?>" class="hover:text-primary <?= $currentPage === 'categories.php' ? 'active' : '' ?>" <?= $currentPage === 'categories.php' ? 'aria-current="page"' : '' ?>>Danh mục</a>
          <a href="<?= url('admin/orders.php') ?>" class="hover:text-primary <?= $currentPage === 'orders.php' || $currentPage === 'order-detail.php' ? 'active' : '' ?>" <?= $currentPage === 'orders.php' || $currentPage === 'order-detail.php' ? 'aria-current="page"' : '' ?>>Đơn hàng</a>
          <a href="<?= url('change-password.php') ?>" class="hover:text-primary <?= $currentPage === 'change-password.php' ? 'active' : '' ?>" <?= $currentPage === 'change-password.php' ? 'aria-current="page"' : '' ?>>Đổi mật khẩu</a>
        <form action="<?= url('logout.php') ?>" method="post" class="inline-form">
          <?= csrf_field() ?>
          <button type="submit"
                  class="text-sm text-white hover:text-primary transition-colors">
            Đăng xuất (<?= e(current_user()['fullname']) ?>)
          </button>
        </form>
      <?php elseif (is_logged_in()): ?>
        <a href="<?= url('index.php') ?>" class="hover:text-primary transition-colors <?= $currentPage === 'index.php' || $currentPage === 'product.php' ? 'active' : '' ?>" <?= $currentPage === 'index.php' || $currentPage === 'product.php' ? 'aria-current="page"' : '' ?>>Trang chủ</a>
        <a href="<?= url('about.php') ?>" class="hover:text-primary transition-colors <?= $currentPage === 'about.php' ? 'active' : '' ?>" <?= $currentPage === 'about.php' ? 'aria-current="page"' : '' ?>>Giới thiệu</a>
        <a href="<?= url('contact.php') ?>" class="hover:text-primary transition-colors <?= $currentPage === 'contact.php' ? 'active' : '' ?>" <?= $currentPage === 'contact.php' ? 'aria-current="page"' : '' ?>>Liên hệ</a>
        <a href="<?= url('cart.php') ?>" class="relative hover:text-primary transition-colors <?= $currentPage === 'cart.php' || $currentPage === 'checkout.php' ? 'active' : '' ?>" <?= $currentPage === 'cart.php' || $currentPage === 'checkout.php' ? 'aria-current="page"' : '' ?>>
          Giỏ hàng (<span id="cart-count"><?= cart_count() ?></span>)
        </a>
        <a href="<?= url('orders.php') ?>" class="hover:text-primary <?= $currentPage === 'orders.php' || ($currentPage === 'order-detail.php' && !is_admin()) ? 'active' : '' ?>" <?= $currentPage === 'orders.php' || ($currentPage === 'order-detail.php' && !is_admin()) ? 'aria-current="page"' : '' ?>>Đơn hàng</a>
        <a href="<?= url('profile.php') ?>" class="hover:text-primary <?= $currentPage === 'profile.php' ? 'active' : '' ?>" <?= $currentPage === 'profile.php' ? 'aria-current="page"' : '' ?>>Tài khoản</a>
        <a href="<?= url('change-password.php') ?>" class="hover:text-primary <?= $currentPage === 'change-password.php' ? 'active' : '' ?>" <?= $currentPage === 'change-password.php' ? 'aria-current="page"' : '' ?>>Đổi mật khẩu</a>
        <form action="<?= url('logout.php') ?>" method="post" class="inline-form">
          <?= csrf_field() ?>
          <button type="submit" class="text-sm text-white hover:text-primary transition-colors">
            Đăng xuất (<?= e(current_user()['fullname']) ?>)
          </button>
        </form>
      <?php else: ?>
        <a href="<?= url('index.php') ?>" class="hover:text-primary transition-colors <?= $currentPage === 'index.php' || $currentPage === 'product.php' ? 'active' : '' ?>" <?= $currentPage === 'index.php' || $currentPage === 'product.php' ? 'aria-current="page"' : '' ?>>Trang chủ</a>
        <a href="<?= url('about.php') ?>" class="hover:text-primary transition-colors <?= $currentPage === 'about.php' ? 'active' : '' ?>" <?= $currentPage === 'about.php' ? 'aria-current="page"' : '' ?>>Giới thiệu</a>
        <a href="<?= url('contact.php') ?>" class="hover:text-primary transition-colors <?= $currentPage === 'contact.php' ? 'active' : '' ?>" <?= $currentPage === 'contact.php' ? 'aria-current="page"' : '' ?>>Liên hệ</a>
        <a href="<?= url('cart.php') ?>" class="relative hover:text-primary transition-colors <?= $currentPage === 'cart.php' || $currentPage === 'checkout.php' ? 'active' : '' ?>" <?= $currentPage === 'cart.php' || $currentPage === 'checkout.php' ? 'aria-current="page"' : '' ?>>
          Giỏ hàng (<span id="cart-count"><?= cart_count() ?></span>)
        </a>
        <a href="<?= url('login.php') ?>" class="hover:text-primary <?= $currentPage === 'login.php' ? 'active' : '' ?>" <?= $currentPage === 'login.php' ? 'aria-current="page"' : '' ?>>Đăng nhập</a>
        <a href="<?= url('register.php') ?>" class="hover:text-primary <?= $currentPage === 'register.php' ? 'active' : '' ?>" <?= $currentPage === 'register.php' ? 'aria-current="page"' : '' ?>>Đăng ký</a>
      <?php endif; ?>
    </nav>
  </header>

  <main class="pt-20">
    <?php $flash = get_flash(); ?>
    <?php if ($flash): ?>
      <p class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
    <?php endif; ?>