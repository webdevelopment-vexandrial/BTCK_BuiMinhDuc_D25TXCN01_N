<?php
require __DIR__ . '/includes/init.php';

$pageTitle = 'Giới thiệu';
require __DIR__ . '/includes/header.php';
?>
<section class="about-page">
    <header class="about-hero">
        <div class="about-hero-copy">
            <span class="about-eyebrow">CÔNG NGHỆ · TẬN TÂM · TIN CẬY</span>
            <h2>Cùng bạn xây dựng góc công nghệ lý tưởng.</h2>
            <p>
                Cửa hàng Bùi Minh Đức tuyển chọn phụ kiện công nghệ thiết thực,
                dễ sử dụng và phù hợp với nhịp sống học tập, làm việc mỗi ngày.
            </p>
            <a class="about-cta" href="<?= url('index.php') ?>">Khám phá cửa hàng <span aria-hidden="true">→</span></a>
        </div>
        <div class="about-store-art">
            <img src="<?= url('images/about-store-3d.svg') ?>" alt="Minh họa cửa hàng công nghệ 3D với quầy trưng bày và thiết bị">
            <span class="about-art-glow" aria-hidden="true"></span>
        </div>
    </header>

    <div class="about-story-grid">
        <article class="about-story-card">
            <span class="about-card-number">01 / CỬA HÀNG</span>
            <h3>Chọn đúng món đồ bạn cần</h3>
            <p>
                Chúng tôi tập trung vào phụ kiện công nghệ hữu ích, thông tin sản phẩm rõ ràng
                và mức giá hợp lý để bạn dễ dàng lựa chọn.
            </p>
        </article>
        <article class="about-story-card">
            <span class="about-card-number">02 / CAM KẾT</span>
            <h3>Mua sắm an tâm hơn</h3>
            <p>Sản phẩm được bảo hành 12 tháng, hỗ trợ giao hàng toàn quốc và đổi trả trong 7 ngày.</p>
        </article>
        <article class="about-story-card about-location-card">
            <span class="about-card-number">03 / GHÉ THĂM</span>
            <h3>Luôn sẵn sàng hỗ trợ</h3>
            <p>123 Đường Số 1, TP. Biên Hòa, Đồng Nai</p>
            <p>Giờ mở cửa: 8:00 – 21:00, tất cả các ngày trong tuần.</p>
        </article>
    </div>

    <section class="about-specifications">
        <header>
            <p class="about-eyebrow">THÔNG TIN CỬA HÀNG</p>
            <h3>Thông số hoạt động</h3>
        </header>
        <div class="about-specifications-wrap">
            <table class="about-specifications-table">
                <tbody>
                    <tr><th scope="row">Lĩnh vực</th><td>Phụ kiện và thiết bị công nghệ</td></tr>
                    <tr><th scope="row">Đối tượng phục vụ</th><td>Sinh viên, gia đình và người làm việc văn phòng</td></tr>
                    <tr><th scope="row">Bảo hành</th><td>12 tháng theo chính sách cửa hàng</td></tr>
                    <tr><th scope="row">Giao hàng</th><td>Hỗ trợ giao hàng toàn quốc</td></tr>
                    <tr><th scope="row">Đổi trả</th><td>Trong 7 ngày theo chính sách cửa hàng</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="about-journey">
        <div>
            <span class="about-eyebrow">MUA SẮM THẬT ĐƠN GIẢN</span>
            <h3>Từ lựa chọn đến tận tay bạn</h3>
        </div>
        <ol class="about-steps">
            <li><span>01</span><p>Chọn sản phẩm phù hợp</p></li>
            <li><span>02</span><p>Xác nhận thông tin đơn hàng</p></li>
            <li><span>03</span><p>Nhận hàng và tận hưởng</p></li>
        </ol>
    </section>

    <footer class="about-contact-strip">
        <div>
            <span class="about-eyebrow">CẦN TƯ VẤN?</span>
            <h3>Chúng tôi luôn sẵn lòng giúp bạn.</h3>
        </div>
        <a class="about-cta about-cta-secondary" href="<?= url('contact.php') ?>">Liên hệ với cửa hàng <span aria-hidden="true">→</span></a>
    </footer>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
