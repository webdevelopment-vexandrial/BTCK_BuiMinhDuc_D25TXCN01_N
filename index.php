<?php
require __DIR__ . '/includes/init.php';

// Đọc tham số trên URL: index.php?category=2&q=chuột
$rawCategoryId = $_GET['category'] ?? 0;
$categoryId = is_scalar($rawCategoryId) ? filter_var((string) $rawCategoryId, FILTER_VALIDATE_INT) : false;
$categoryId = $categoryId !== false && $categoryId > 0 ? $categoryId : 0;
$keyword = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) && is_scalar($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 8;

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY id')->fetchAll();

$where = [];
$params = [];
if ($categoryId > 0) {
    $where[] = 'category_id = ?';
    $params[] = $categoryId;
}
if ($keyword !== '') {
    $where[] = 'name LIKE ?';
    $params[] = '%' . $keyword . '%';
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM products' . $whereSql);
$countStmt->execute($params);
$totalProducts = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalProducts / $perPage));
$page = min($page, $totalPages);

$stmt = $pdo->prepare(
    'SELECT id, name, price, image FROM products' . $whereSql . ' ORDER BY id LIMIT ? OFFSET ?'
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
<section>
    <h2>Sản phẩm</h2>

    <form action="<?= url('index.php') ?>" method="get" class="search-form">
        <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Tìm sản phẩm...">
        <?php if ($categoryId > 0): ?>
            <input type="hidden" name="category" value="<?= $categoryId ?>">
        <?php endif; ?>
        <button type="submit" class="btn">Tìm</button>
    </form>

    <p class="category-bar">
        <a href="<?= url('index.php') ?>" class="<?= $categoryId === 0 ? 'active' : '' ?>">Tất cả</a>
        <?php foreach ($categories as $category): ?>
            <a href="<?= url('index.php?category=' . $category['id']) ?>"
               class="<?= $categoryId === (int) $category['id'] ? 'active' : '' ?>"><?= e($category['name']) ?></a>
        <?php endforeach; ?>
    </p>

    <?php if (!$products): ?>
        <p>Không tìm thấy sản phẩm nào.</p>
    <?php endif; ?>

    <div class="product-list">
        <?php foreach ($products as $product): ?>
            <article class="product-card">
                <img src="<?= url($product['image']) ?>" alt="<?= e($product['name']) ?>">
                <h3><?= e($product['name']) ?></h3>
                <p class="price"><?= format_price($product['price']) ?></p>
                <a href="<?= url('product.php?id=' . $product['id']) ?>" class="btn">Xem chi tiết</a>
                <form action="<?= url('cart-add.php') ?>" method="post" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button type="submit" class="btn btn-accent">Thêm vào giỏ</button>
                </form>
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
<?php require __DIR__ . '/includes/footer.php'; ?>
