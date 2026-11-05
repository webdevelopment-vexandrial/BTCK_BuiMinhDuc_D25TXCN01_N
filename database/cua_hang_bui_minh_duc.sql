-- Cua hang Bui Minh Duc: cấu trúc bảng và dữ liệu mẫu
-- Chạy trong phpMyAdmin sau khi đã chọn cơ sở dữ liệu cua_hang_bui_minh_duc

SET NAMES utf8mb4;

DROP TABLE IF EXISTS contacts, order_items, orders, products, categories, login_attempts, users;

CREATE TABLE categories (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name        VARCHAR(200) NOT NULL,
    price       INT UNSIGNED NOT NULL,
    description TEXT,
    image       VARCHAR(255) NOT NULL DEFAULT 'images/sp01.jpg',
    stock       INT UNSIGNED NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fullname      VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(15)  NOT NULL,
    address       VARCHAR(255) NOT NULL DEFAULT '',
    role          ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
    email           VARCHAR(150) NOT NULL PRIMARY KEY,
    failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until     DATETIME NULL,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contacts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fullname     VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL,
    subject_line VARCHAR(200) NOT NULL,
    message      TEXT NOT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (fullname, email, password_hash, phone, address, role) VALUES
    ('Quản trị viên', 'admin@gmail.com',
     '$2y$10$793LCRyhMtQzt69jTLCv0eEy1.v5PYY1xYWoYpzc4j3Pjht9kQ9F6',
     '0000000000', '', 'admin')
ON DUPLICATE KEY UPDATE
    fullname = VALUES(fullname),
    password_hash = VALUES(password_hash),
    role = VALUES(role);

CREATE TABLE orders (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    fullname   VARCHAR(100) NOT NULL,
    phone      VARCHAR(15)  NOT NULL,
    address    VARCHAR(255) NOT NULL,
    note       VARCHAR(500) NOT NULL DEFAULT '',
    total      INT UNSIGNED NOT NULL,
    status     ENUM('pending', 'shipping', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    product_id   INT UNSIGNED NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    price        INT UNSIGNED NOT NULL,
    quantity     INT UNSIGNED NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (id),
    FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (id, name) VALUES
    (1, 'Chuột và bàn phím'),
    (2, 'Âm thanh và hình ảnh'),
    (3, 'Lưu trữ và phụ kiện'),
    (4, 'Thiết bị kết nối'),
    (5, 'Gaming và linh kiện');

INSERT INTO products (category_id, name, price, description, image, stock) VALUES
    (1, 'Chuột không dây M1', 250000,
     'Chuột không dây kết nối 2.4GHz, thiết kế đối xứng, nút bấm êm, phù hợp cho học tập và làm việc văn phòng.',
     'images/sp01.jpg', 20),
    (1, 'Bàn phím cơ K2', 790000,
     'Bàn phím cơ 87 phím, switch đỏ gõ nhẹ, có đèn nền trắng.',
     'images/sp02.jpg', 10),
    (2, 'Tai nghe Bluetooth T3', 450000,
     'Tai nghe chụp tai Bluetooth 5.3, pin 30 giờ, có micro đàm thoại.',
     'images/sp03.jpg', 15),
    (3, 'USB 64GB U4', 150000,
     'USB 3.2 dung lượng 64GB, vỏ kim loại, có móc treo.',
     'images/sp04.jpg', 50),
    (2, 'Webcam Full HD W5', 520000,
     'Webcam 1080p, tự lấy nét, tích hợp micro kép, kẹp được trên màn hình.',
     'images/sp05.jpg', 8),
    (3, 'Đế tản nhiệt laptop D6', 320000,
     'Đế tản nhiệt hai quạt, điều chỉnh được độ nghiêng, dùng cho laptop đến 15.6 inch.',
     'images/sp06.jpg', 12),
    (3, 'Móc khóa xoay EDC X7', 59000,
     'Móc khóa xoay nhỏ gọn với thân kim loại chắc chắn, thiết kế cầm nắm tiện tay và chuyển động xoay thư giãn. Phù hợp gắn chìa khóa hoặc balo.',
     'images/sp07.jpg', 25),
    (4, 'Hub USB-C 6 trong 1 U8', 490000,
     'Hub USB-C đa cổng giúp mở rộng kết nối cho laptop với các cổng USB và cổng xuất hình tiện dụng. Vỏ nhôm gọn nhẹ, dễ mang theo khi học tập hoặc làm việc.',
     'images/sp08.jpg', 12),
    (5, 'Ghế gaming công thái học G9', 3490000,
     'Ghế gaming tựa lưng cao với đệm hỗ trợ lưng, tay vịn chắc chắn và chân đế năm cánh ổn định. Thiết kế phù hợp cho góc chơi game và làm việc tại nhà.',
     'images/sp09.jpg', 4),
    (5, 'Laptop gaming 15.6 inch L10', 24990000,
     'Laptop gaming màn hình 15.6 inch, bàn phím có đèn nền và thiết kế tản nhiệt cho các phiên chơi game dài. Phù hợp chơi game, học tập và sáng tạo nội dung.',
     'images/sp10.jpg', 3),
    (5, 'Card đồ họa RTX 4060 8GB V11', 9490000,
     'Card đồ họa GeForce RTX 4060 bộ nhớ 8GB, hai quạt tản nhiệt và thiết kế gọn cho bộ máy chơi game. Hỗ trợ tăng tốc xử lý đồ họa và giải trí độ phân giải cao.',
     'images/sp11.jpg', 5),
    (5, 'RAM DDR4 16GB 3200MHz R12', 790000,
     'Bộ nhớ RAM DDR4 dung lượng 16GB, tốc độ 3200MHz, phù hợp nâng cấp máy tính để bàn tương thích và cải thiện khả năng đa nhiệm.',
     'images/sp12.jpg', 16);
