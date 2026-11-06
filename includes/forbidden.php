<?php
require __DIR__ . '/header.php';
?>
<section class="forbidden-page" aria-labelledby="forbidden-title">
    <div class="forbidden-card">
        <span class="forbidden-eyebrow">KHU VỰC ĐƯỢC BẢO VỆ</span>
        <p class="forbidden-code" aria-hidden="true">403</p>
        <h2 id="forbidden-title">Bạn không có quyền truy cập trang này</h2>
        <p class="forbidden-description">
            Cửa hàng Bùi Minh Đức mang đến các phụ kiện công nghệ được tuyển chọn,
            giúp bạn học tập, làm việc và giải trí thuận tiện hơn.
        </p>
        <div class="forbidden-actions">
            <a class="forbidden-button forbidden-button-primary" href="<?= url('index.php') ?>">Về cửa hàng</a>
            <a class="forbidden-button forbidden-button-secondary" href="<?= url('about.php') ?>">Giới thiệu cửa hàng</a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/footer.php'; ?>
