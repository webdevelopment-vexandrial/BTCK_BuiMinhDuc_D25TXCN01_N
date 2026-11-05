<?php
require __DIR__ . '/includes/init.php';

// Đọc tham số trên URL: index.php?category=2&q=chuột
$rawCategoryId = $_GET['category'] ?? 0;
$categoryId = is_scalar($rawCategoryId) ? filter_var((string) $rawCategoryId, FILTER_VALIDATE_INT) : false;
$categoryId = $categoryId !== false && $categoryId > 0 ? $categoryId : 0;
$keyword = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) && is_scalar($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$sortOptions = [
    'newest' => 'Mới nhất',
    'price_asc' => 'Giá tăng dần',
    'price_desc' => 'Giá giảm dần',
];
$sort = isset($_GET['sort']) && is_string($_GET['sort']) && isset($sortOptions[$_GET['sort']])
    ? $_GET['sort']
    : 'newest';
$sortSql = [
    'newest' => 'p.id DESC',
    'price_asc' => 'p.price ASC, p.id DESC',
    'price_desc' => 'p.price DESC, p.id DESC',
][$sort];
$perPage = 8;

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY id')->fetchAll();

$where = [];
$params = [];
if ($categoryId > 0) {
    $where[] = 'p.category_id = ?';
    $params[] = $categoryId;
}
if ($keyword !== '') {
    $where[] = 'p.name LIKE ?';
    $params[] = '%' . $keyword . '%';
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM products p' . $whereSql);
$countStmt->execute($params);
$totalProducts = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalProducts / $perPage));
$page = min($page, $totalPages);

$stmt = $pdo->prepare(
    'SELECT p.id, p.name, p.price, p.image, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id' . $whereSql . ' ORDER BY ' . $sortSql . ' LIMIT ? OFFSET ?'
);
foreach ($params as $index => $value) {
    $stmt->bindValue($index + 1, $value);
}
$stmt->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, ($page - 1) * $perPage, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

$pageTitle = 'Trang chủ';
require __DIR__ . '/includes/header.php';
?>
<section class="home-page">
    <section id="hero" class="home-hero">
        <div class="hero-copy">
            <p class="hero-eyebrow">PHỤ KIỆN CÔNG NGHỆ · CHỌN LỌC MỖI NGÀY</p>
            <h2>Nâng tầm góc làm việc <span>của bạn</span></h2>
            <p class="hero-description">
                Tìm những món đồ công nghệ hữu ích, chất lượng và hợp với phong cách của bạn.
            </p>
            <a class="hero-cta" href="#product-catalog">Khám phá sản phẩm <span aria-hidden="true">→</span></a>
        </div>
        <div class="hero-art" aria-hidden="true">
            <span class="hero-orbit hero-orbit-one"></span>
            <span class="hero-orbit hero-orbit-two"></span>
            <span class="hero-device">✦</span>
        </div>
    </section>

    <section id="product-catalog" class="catalog-section">
        <div class="catalog-heading">
            <div>
                <p class="section-eyebrow">BỘ SƯU TẬP CỦA CHÚNG TÔI</p>
                <h2>Sản phẩm nổi bật</h2>
            </div>
            <p class="catalog-hint">Chọn món đồ phù hợp cho bạn</p>
        </div>

        <form action="<?= url('index.php') ?>" method="get" class="search-form">
            <span class="search-icon" aria-hidden="true">⌕</span>
            <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Bạn đang tìm sản phẩm nào?" aria-label="Tìm sản phẩm">
            <?php if ($categoryId > 0): ?>
                <input type="hidden" name="category" value="<?= $categoryId ?>">
            <?php endif; ?>
            <button type="submit" class="btn">Tìm kiếm</button>
        </form>

        <form action="<?= url('index.php') ?>" method="get" class="catalog-sort-form">
            <?php if ($categoryId > 0): ?>
                <input type="hidden" name="category" value="<?= $categoryId ?>">
            <?php endif; ?>
            <?php if ($keyword !== ''): ?>
                <input type="hidden" name="q" value="<?= e($keyword) ?>">
            <?php endif; ?>
            <label for="sort">Sắp xếp</label>
            <select id="sort" name="sort">
                <?php foreach ($sortOptions as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $sort === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Áp dụng</button>
        </form>

        <p class="catalog-result-count" role="status">
            <?php if ($totalProducts === 0): ?>
                Không có sản phẩm phù hợp.
            <?php else: ?>
                Hiển thị <?= count($products) ?> trong tổng số <?= $totalProducts ?> sản phẩm
                <?php if ($keyword !== ''): ?>cho từ khóa “<?= e($keyword) ?>”<?php endif; ?>.
            <?php endif; ?>
        </p>

        <div class="category-filter" aria-label="Lọc sản phẩm theo loại">
            <span class="filter-label">Danh mục</span>
            <nav class="category-bar">
                <a href="<?= url('index.php') ?>" class="<?= $categoryId === 0 ? 'active' : '' ?>" <?= $categoryId === 0 ? 'aria-current="page"' : '' ?>>Tất cả</a>
                <?php foreach ($categories as $category): ?>
                    <a href="<?= url('index.php?category=' . $category['id']) ?>"
                       class="<?= $categoryId === (int) $category['id'] ? 'active' : '' ?>"
                       <?= $categoryId === (int) $category['id'] ? 'aria-current="page"' : '' ?>><?= e($category['name']) ?></a>
                <?php endforeach; ?>
            </nav>
        </div>

        <?php if (!$products): ?>
            <p class="empty-products">Không tìm thấy sản phẩm nào. Thử từ khóa hoặc danh mục khác nhé.</p>
        <?php endif; ?>

        <div class="product-list">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <div class="product-image-wrap">
                        <img src="<?= url($product['image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                    </div>
                    <div class="product-card-content">
                        <p class="product-category-label"><?= e($product['category_name']) ?></p>
                        <h3><?= e($product['name']) ?></h3>
                        <p class="price"><?= format_price($product['price']) ?></p>
                        <a href="<?= url('product.php?id=' . $product['id']) ?>" class="btn">Xem chi tiết <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Phân trang sản phẩm">
                <?php
                $paginationParams = [];
                if ($categoryId > 0) {
                    $paginationParams['category'] = $categoryId;
                }
                if ($keyword !== '') {
                    $paginationParams['q'] = $keyword;
                }
                if ($sort !== 'newest') {
                    $paginationParams['sort'] = $sort;
                }
                ?>
                <?php if ($page > 1): ?>
                    <?php $paginationParams['page'] = $page - 1; ?>
                    <a href="<?= url('index.php?' . http_build_query($paginationParams)) ?>">« Trước</a>
                <?php endif; ?>
                <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                    <?php $paginationParams['page'] = $pageNumber; ?>
                    <a href="<?= url('index.php?' . http_build_query($paginationParams)) ?>"
                       class="<?= $pageNumber === $page ? 'active' : '' ?>"
                       <?= $pageNumber === $page ? 'aria-current="page"' : '' ?>><?= $pageNumber ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <?php $paginationParams['page'] = $page + 1; ?>
                    <a href="<?= url('index.php?' . http_build_query($paginationParams)) ?>">Sau »</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
