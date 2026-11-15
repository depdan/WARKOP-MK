CREATE DATABASE IF NOT EXISTS warkop_stock
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE warkop_stock;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NULL,
    supplier_id BIGINT UNSIGNED NULL,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(160) NOT NULL,
    unit VARCHAR(30) NOT NULL DEFAULT 'pcs',
    cost_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    sell_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    stock DECIMAL(15,2) NOT NULL DEFAULT 0,
    min_stock DECIMAL(15,2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_product_supplier FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    INDEX idx_products_stock(stock),
    INDEX idx_products_name(name)
) ENGINE=InnoDB;

CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(40) NOT NULL UNIQUE,
    customer_name VARCHAR(120) NOT NULL DEFAULT 'Pelanggan Umum',
    table_no VARCHAR(30) NOT NULL DEFAULT 'Meja 1',
    order_type ENUM('Dine In','Take Away') NOT NULL DEFAULT 'Dine In',
    payment_method ENUM('Tunai','QRIS','Transfer','E-Wallet') NOT NULL DEFAULT 'Tunai',
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    cash_paid DECIMAL(15,2) NOT NULL DEFAULT 0,
    change_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    status ENUM('PAID','OPEN','VOID') NOT NULL DEFAULT 'PAID',
    note VARCHAR(255),
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user FOREIGN KEY(user_id) REFERENCES users(id),
    INDEX idx_orders_created(created_at),
    INDEX idx_orders_status(status)
) ENGINE=InnoDB;

CREATE TABLE expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expense_date DATE NOT NULL,
    category VARCHAR(80) NOT NULL DEFAULT 'Operasional',
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_expense_user FOREIGN KEY(user_id) REFERENCES users(id),
    INDEX idx_expenses_date(expense_date)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    product_name VARCHAR(160) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    quantity DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    CONSTRAINT fk_order_item_order FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_item_product FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    type ENUM('IN','OUT','ADJUSTMENT') NOT NULL,
    quantity DECIMAL(15,2) NOT NULL,
    stock_before DECIMAL(15,2) NOT NULL,
    stock_after DECIMAL(15,2) NOT NULL,
    note VARCHAR(255),
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_movement_product FOREIGN KEY(product_id) REFERENCES products(id),
    CONSTRAINT fk_movement_user FOREIGN KEY(user_id) REFERENCES users(id),
    INDEX idx_movements_created(created_at),
    INDEX idx_movements_product(product_id)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    table_name VARCHAR(80),
    record_id BIGINT UNSIGNED NULL,
    old_data JSON NULL,
    new_data JSON NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users(name,email,password,role) VALUES
('Owner Warkop MK','admin@warkop.local', '$2y$10$ttd7iqhmWFcBqfugHuUPcez0v8svICS/4R2kd2IhSkK1CKHLThu1K', 'admin'),
('Kasir Warkop MK','user@warkop.local', '$2y$10$ttd7iqhmWFcBqfugHuUPcez0v8svICS/4R2kd2IhSkK1CKHLThu1K', 'user');

INSERT INTO categories(id, name) VALUES
(1, 'Minuman'),
(2, 'Kopi'),
(3, 'Minuman Rasa'),
(4, 'Makanan'),
(5, 'Makanan Ringan'),
(6, 'Kacang-Kacangan'),
(7, 'Rokok');

INSERT INTO suppliers(id, name, phone, address) VALUES
(1, 'Grosir Sembako & Minuman', '081234567890', 'Pasar Induk'),
(2, 'Distributor Kopi & Rokok', '081298765432', 'Gudang Utama');

INSERT INTO products(id, category_id, supplier_id, sku, name, unit, cost_price, sell_price, stock, min_stock) VALUES
(1,  1, 1, 'M-001', 'Teh Pucuk', 'botol', 3500, 5000, 36, 8),
(2,  1, 1, 'M-002', 'Pocari', 'botol', 5000, 7000, 24, 6),
(3,  1, 1, 'M-003', 'Nipis Madu', 'botol', 3500, 5000, 24, 6),
(4,  1, 1, 'M-004', 'Cincau Panda', 'kaleng', 4800, 7000, 20, 5),
(5,  1, 1, 'M-005', 'Larutan Cap Kaki Tiga', 'kaleng', 4800, 7000, 20, 5),
(6,  1, 1, 'M-006', 'Aqua Botol Sedang', 'botol', 2500, 4000, 40, 10),
(7,  1, 1, 'M-007', 'Aqua Botol Besar', 'botol', 5500, 8000, 18, 5),
(8,  1, 1, 'M-008', 'Aqua Gelas', 'gelas', 500, 1000, 60, 15),
(9,  1, 1, 'M-009', 'Coca-Cola', 'botol', 3500, 5000, 24, 6),
(10, 1, 1, 'M-010', 'Sprite', 'botol', 3500, 5000, 24, 6),
(11, 1, 1, 'M-011', 'Fanta', 'botol', 3500, 5000, 24, 6),
(12, 1, 1, 'M-012', 'Golda', 'botol', 3200, 5000, 24, 6),
(13, 1, 1, 'M-013', 'Mizone', 'botol', 4200, 6000, 20, 5),
(14, 6, 1, 'M-014', 'Kacang Koro', 'bks', 600, 1000, 40, 10),
(15, 6, 1, 'M-015', 'Kacang Kulit', 'bks', 600, 1000, 40, 10),
(16, 6, 1, 'M-016', 'Kacang Garuda', 'bks', 600, 1000, 40, 10),
(17, 6, 1, 'M-017', 'Tictac', 'bks', 600, 1000, 40, 10),
(18, 5, 1, 'M-018', 'Malkis Coklat', 'bks', 650, 1000, 35, 8),
(19, 5, 1, 'M-019', 'Malkis Abon', 'bks', 650, 1000, 35, 8),
(20, 5, 1, 'M-020', 'Better Coklat', 'bks', 1700, 2500, 30, 8),
(21, 5, 1, 'M-021', 'Better Vanila', 'bks', 1700, 2500, 30, 8),
(22, 5, 1, 'M-022', 'Beng-Beng', 'pcs', 1700, 2500, 36, 8),
(23, 5, 1, 'M-023', 'Chocolatos', 'pcs', 650, 1000, 45, 10),
(24, 5, 1, 'M-024', 'Roti Aoka', 'pcs', 2000, 3000, 24, 6),
(25, 2, 2, 'M-025', 'Kopi', 'gelas', 2000, 4000, 50, 10),
(26, 2, 2, 'M-026', 'Kapal Api', 'gelas', 2000, 4000, 55, 10),
(27, 2, 2, 'M-027', 'Kopi Liong', 'gelas', 2000, 4000, 45, 10),
(28, 2, 2, 'M-028', 'Kopi Luwak', 'gelas', 2000, 4000, 40, 10),
(29, 2, 2, 'M-029', 'Kopi ABC Susu', 'gelas', 2200, 4000, 48, 10),
(30, 2, 2, 'M-030', 'Kopi Oplet', 'gelas', 2000, 4000, 35, 8),
(31, 2, 2, 'M-031', 'Kopi Good Day Capucino', 'gelas', 3200, 6000, 36, 8),
(32, 2, 2, 'M-032', 'Kopi Good Day Freezz', 'gelas', 3200, 6000, 30, 8),
(33, 3, 1, 'M-033', 'Nutrisari Jeruk Peras', 'gelas', 2000, 4000, 40, 10),
(34, 3, 1, 'M-034', 'Nutrisari Anggur', 'gelas', 2000, 4000, 36, 8),
(35, 3, 1, 'M-035', 'Marimas Anggur', 'gelas', 2200, 5000, 36, 8),
(36, 3, 1, 'M-036', 'Marimas Coconut', 'gelas', 2200, 5000, 30, 8),
(37, 3, 1, 'M-037', 'Marimas Jeruk', 'gelas', 2200, 5000, 36, 8),
(38, 3, 1, 'M-038', 'Drink Beng-Beng', 'gelas', 3200, 6000, 30, 8),
(39, 3, 1, 'M-039', 'Chocolatos Matcha', 'gelas', 2800, 5000, 30, 8),
(40, 3, 1, 'M-040', 'Chocolatos Coklat', 'gelas', 2800, 5000, 32, 8),
(41, 3, 1, 'M-041', 'Susu Jahe', 'gelas', 2800, 5000, 28, 6),
(42, 3, 1, 'M-042', 'AHM', 'gelas', 2600, 5000, 24, 6),
(43, 7, 2, 'M-043', 'Sampoerna Mild', 'bks', 34500, 38000, 20, 5),
(44, 7, 2, 'M-044', 'Gudang Garam Filter', 'bks', 26000, 29000, 24, 5),
(45, 7, 2, 'M-045', '76 Apel', 'bks', 14800, 17000, 18, 5),
(46, 7, 2, 'M-046', 'Sampoerna Prima', 'bks', 14800, 17000, 18, 5),
(47, 7, 2, 'M-047', 'Dji Sam Soe', 'bks', 20500, 23000, 20, 5),
(48, 7, 2, 'M-048', 'Magnum', 'bks', 26000, 29000, 18, 5),
(49, 7, 2, 'M-049', 'Super', 'bks', 26000, 29000, 18, 5),
(50, 7, 2, 'M-050', 'Gudang Garam Filter Kaleng', 'pcs', 3500, 5000, 15, 4),
(51, 4, 1, 'M-051', 'Indomie Telur', 'porsi', 6500, 12000, 28, 6),
(52, 5, 1, 'M-052', 'Kerupuk', 'bks', 1200, 2000, 30, 8),
(53, 6, 1, 'M-053', 'Kacang Garing', 'bks', 600, 1000, 35, 8),
(54, 2, 2, 'M-054', 'Good Day Merah', 'gelas', 2200, 4000, 36, 8),
(55, 7, 2, 'M-055', 'Signature', 'bks', 26000, 29000, 16, 4),
(56, 7, 2, 'M-056', 'Djarum Super', 'bks', 26000, 29000, 16, 4),
(57, 1, 1, 'M-057', 'Floridina', 'botol', 3400, 5000, 24, 6),
(58, 1, 1, 'M-058', 'Le Minerale', 'botol', 2500, 4000, 6, 10);

INSERT INTO orders(order_code,customer_name,table_no,order_type,payment_method,subtotal,discount,total_amount,cash_paid,change_amount,note,user_id) VALUES
('WRK-20261112-001','Budi Santoso','Meja 3','Dine In','Tunai',25000,0,25000,50000,25000,'Kapal api panas, indomie pedas',2),
('WRK-20261112-002','Rizky','Meja 1','Dine In','QRIS',18000,0,18000,18000,0,'Good day capucino es batu dikit',2);

INSERT INTO order_items(order_id,product_id,product_name,unit_price,quantity,subtotal) VALUES
(1,26,'Kapal Api',4000,2,8000),
(1,51,'Indomie Telur',12000,1,12000),
(1,1,'Teh Pucuk',5000,1,5000),
(2,31,'Kopi Good Day Capucino',6000,2,12000),
(2,38,'Drink Beng-Beng',6000,1,6000);

INSERT INTO expenses(expense_date,category,description,amount,user_id) VALUES
('2026-08-05','Kulakan Stok','Restok Indomie, Kapal Api & Gas LPG 3kg',152000,1),
(CURDATE(),'Operasional','Es batu kristal, tisu & plastik',28000,1);
