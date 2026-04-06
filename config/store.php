<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../assets/db.php';
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/payment.php';
require_once __DIR__ . '/auth_support.php';

const STORE_FREE_DELIVERY_THRESHOLD = 499;
const STORE_DEFAULT_DELIVERY_FEE = 35;
const STORE_DEFAULT_HANDLING_FEE = 9;
const STORE_LOW_STOCK_LIMIT = 8;
const STORE_DEFAULT_PAYMENT_METHOD = 'Cash on Delivery';

function get_default_store_categories(): array
{
    return [
        ['id' => 1, 'name' => 'Vegetables', 'description' => 'Daily fresh sabzi sourced for quick local delivery.'],
        ['id' => 2, 'name' => 'Fruits', 'description' => 'Seasonal fruits with same-day freshness and careful handling.'],
        ['id' => 3, 'name' => 'Dairy', 'description' => 'Milk, butter, curd, and chilled essentials delivered cold.'],
        ['id' => 4, 'name' => 'Bakery', 'description' => 'Fresh breads and baked favourites for breakfast and snacking.'],
        ['id' => 5, 'name' => 'Daily Needs', 'description' => 'Staples and home essentials for fast repeat orders.'],
    ];
}

function get_default_quick_commerce_products(): array
{
    return [
        ['id' => 1, 'category_id' => 1, 'name' => 'Farm Potato', 'price' => 39, 'mrp' => 46, 'unit_label' => '1 kg', 'description' => 'Clean, table-grade potatoes for everyday curries, fries, and tiffin prep.', 'image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Potato.jpg', 'stock_quantity' => 82, 'delivery_minutes' => 16, 'is_featured' => 1, 'badge_text' => 'Best Seller', 'sku' => 'HF-VEG-001'],
        ['id' => 2, 'category_id' => 1, 'name' => 'Red Tomato', 'price' => 34, 'mrp' => 40, 'unit_label' => '500 g', 'description' => 'Juicy red tomatoes ideal for gravy, salads, and daily cooking.', 'image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Tomato_(1).jpg', 'stock_quantity' => 56, 'delivery_minutes' => 14, 'is_featured' => 1, 'badge_text' => 'Farm Pick', 'sku' => 'HF-VEG-002'],
        ['id' => 3, 'category_id' => 1, 'name' => 'Red Onion', 'price' => 43, 'mrp' => 50, 'unit_label' => '1 kg', 'description' => 'Fresh onions with balanced sharpness, sorted for home kitchen use.', 'image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Onions.JPG', 'stock_quantity' => 48, 'delivery_minutes' => 18, 'is_featured' => 0, 'badge_text' => 'Kitchen Staple', 'sku' => 'HF-VEG-003'],
        ['id' => 4, 'category_id' => 2, 'name' => 'Robusta Banana', 'price' => 59, 'mrp' => 68, 'unit_label' => '6 pcs', 'description' => 'Ripe, ready-to-eat bananas packed for breakfast bowls and quick snacks.', 'image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Bananas.JPG', 'stock_quantity' => 65, 'delivery_minutes' => 15, 'is_featured' => 1, 'badge_text' => 'Ready to Eat', 'sku' => 'HF-FRU-001'],
        ['id' => 5, 'category_id' => 2, 'name' => 'Apple Pack', 'price' => 149, 'mrp' => 169, 'unit_label' => '4 pcs', 'description' => 'Crunchy apples selected for quality, lunchboxes, and healthy snacking.', 'image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Apple.JPG', 'stock_quantity' => 31, 'delivery_minutes' => 20, 'is_featured' => 1, 'badge_text' => 'Premium', 'sku' => 'HF-FRU-002'],
        ['id' => 6, 'category_id' => 2, 'name' => 'Alphonso Mango', 'price' => 189, 'mrp' => 220, 'unit_label' => '1 kg', 'description' => 'Seasonal alphonso mangoes with rich sweetness and gifting-quality selection.', 'image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/SH-Mango.jpg', 'stock_quantity' => 24, 'delivery_minutes' => 24, 'is_featured' => 1, 'badge_text' => 'Seasonal', 'sku' => 'HF-FRU-003'],
        ['id' => 7, 'category_id' => 3, 'name' => 'Amul Gold Milk', 'price' => 36, 'mrp' => 38, 'unit_label' => '500 ml', 'description' => 'Trusted full-cream milk kept in the cold chain for tea, coffee, and daily use.', 'image' => 'https://amul.com/files/products/amul-gold.png', 'stock_quantity' => 92, 'delivery_minutes' => 15, 'is_featured' => 1, 'badge_text' => 'Cold Chain', 'sku' => 'HF-DAI-001'],
        ['id' => 8, 'category_id' => 3, 'name' => 'Amul Butter', 'price' => 60, 'mrp' => 65, 'unit_label' => '100 g', 'description' => 'Classic table butter for toast, sandwiches, and everyday cooking.', 'image' => 'https://amul.com/files/products/amul_tablebutter.jpeg', 'stock_quantity' => 44, 'delivery_minutes' => 18, 'is_featured' => 0, 'badge_text' => 'Top Rated', 'sku' => 'HF-DAI-002'],
        ['id' => 9, 'category_id' => 3, 'name' => 'Fresh Curd', 'price' => 48, 'mrp' => 55, 'unit_label' => '400 g', 'description' => 'Homestyle curd with a thick set texture, chilled for same-day delivery.', 'image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Perfect_Curd_yoghurt_picture.JPG', 'stock_quantity' => 38, 'delivery_minutes' => 18, 'is_featured' => 0, 'badge_text' => 'Daily Fresh', 'sku' => 'HF-DAI-003'],
        ['id' => 10, 'category_id' => 4, 'name' => 'Whole Wheat Bread', 'price' => 45, 'mrp' => 52, 'unit_label' => '400 g', 'description' => 'Fresh loaf baked for breakfast and sandwich prep with soft slice texture.', 'image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Loaf_Of_Bread.jpg', 'stock_quantity' => 29, 'delivery_minutes' => 20, 'is_featured' => 1, 'badge_text' => 'Fresh Bake', 'sku' => 'HF-BAK-001'],
        ['id' => 11, 'category_id' => 4, 'name' => 'Butter Croissant', 'price' => 79, 'mrp' => 95, 'unit_label' => '2 pcs', 'description' => 'Flaky croissants packed fresh for breakfast combos and office snack runs.', 'image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Chocolate_and_croissant.jpg', 'stock_quantity' => 18, 'delivery_minutes' => 22, 'is_featured' => 0, 'badge_text' => 'Morning Pick', 'sku' => 'HF-BAK-002'],
        ['id' => 12, 'category_id' => 5, 'name' => 'Basmati Rice', 'price' => 96, 'mrp' => 110, 'unit_label' => '1 kg', 'description' => 'Long-grain rice for daily meals, biryani prep, and pantry restock orders.', 'image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Raw_rice.jpg', 'stock_quantity' => 74, 'delivery_minutes' => 18, 'is_featured' => 1, 'badge_text' => 'Staple Buy', 'sku' => 'HF-DLY-001'],
        ['id' => 13, 'category_id' => 5, 'name' => 'Strong Teeth Toothpaste', 'price' => 99, 'mrp' => 120, 'unit_label' => '150 g', 'description' => 'Family-size toothpaste for daily oral care and repeat convenience orders.', 'image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Toothpaste.jpg', 'stock_quantity' => 43, 'delivery_minutes' => 20, 'is_featured' => 0, 'badge_text' => 'Family Pack', 'sku' => 'HF-DLY-002'],
        ['id' => 14, 'category_id' => 5, 'name' => 'Bath Soap', 'price' => 38, 'mrp' => 45, 'unit_label' => '125 g', 'description' => 'Everyday bathing soap for repeat household orders and top-up purchases.', 'image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/A_bar_of_soap.jpg', 'stock_quantity' => 57, 'delivery_minutes' => 20, 'is_featured' => 0, 'badge_text' => 'Everyday Care', 'sku' => 'HF-DLY-003'],
    ];
}

function ensure_store_schema(mysqli $conn): void
{
    $conn->query(
        "CREATE TABLE IF NOT EXISTS users (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) NOT NULL DEFAULT 'user' AFTER email");
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS phone_number VARCHAR(20) DEFAULT NULL AFTER role");
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS address_line1 VARCHAR(255) DEFAULT NULL AFTER phone_number");
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS address_line2 VARCHAR(255) DEFAULT NULL AFTER address_line1");
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS city VARCHAR(100) DEFAULT NULL AFTER address_line2");
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS state VARCHAR(100) DEFAULT NULL AFTER city");
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS postal_code VARCHAR(20) DEFAULT NULL AFTER state");
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER password");
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login_at DATETIME DEFAULT NULL AFTER created_at");
    $conn->query("UPDATE users SET role = 'admin' WHERE email = 'admin@gmail.com'");

    $conn->query(
        "CREATE TABLE IF NOT EXISTS categories (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
    $conn->query("ALTER TABLE categories MODIFY COLUMN name VARCHAR(100) NOT NULL");
    $conn->query("ALTER TABLE categories ADD COLUMN IF NOT EXISTS description TEXT DEFAULT NULL AFTER name");

    $conn->query(
        "CREATE TABLE IF NOT EXISTS products (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            sku VARCHAR(50) DEFAULT NULL,
            name VARCHAR(120) NOT NULL,
            price INT(11) NOT NULL DEFAULT 0,
            mrp INT(11) NOT NULL DEFAULT 0,
            unit_label VARCHAR(50) NOT NULL DEFAULT '1 pack',
            description TEXT DEFAULT NULL,
            image VARCHAR(255) DEFAULT NULL,
            stock_quantity INT(11) NOT NULL DEFAULT 0,
            delivery_minutes INT(11) NOT NULL DEFAULT 20,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            badge_text VARCHAR(50) DEFAULT NULL,
            category_id INT(11) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY category_id (category_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
    $conn->query("ALTER TABLE products MODIFY COLUMN name VARCHAR(120) NOT NULL");
    $conn->query("ALTER TABLE products MODIFY COLUMN image VARCHAR(255) DEFAULT NULL");
    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS sku VARCHAR(50) DEFAULT NULL AFTER id");
    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS mrp INT(11) NOT NULL DEFAULT 0 AFTER price");
    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS unit_label VARCHAR(50) NOT NULL DEFAULT '1 pack' AFTER mrp");
    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS description TEXT DEFAULT NULL AFTER unit_label");
    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS stock_quantity INT(11) NOT NULL DEFAULT 0 AFTER image");
    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS delivery_minutes INT(11) NOT NULL DEFAULT 20 AFTER stock_quantity");
    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER delivery_minutes");
    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS badge_text VARCHAR(50) DEFAULT NULL AFTER is_featured");
    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INT(11) DEFAULT NULL AFTER badge_text");
    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER category_id");

    $conn->query(
        "CREATE TABLE IF NOT EXISTS orders (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            subtotal_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            handling_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status VARCHAR(50) NOT NULL DEFAULT 'Placed',
            payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
            delivery_eta_label VARCHAR(50) DEFAULT NULL,
            shipping_name VARCHAR(100) DEFAULT NULL,
            shipping_phone VARCHAR(20) DEFAULT NULL,
            shipping_address TEXT DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY user_id (user_id),
            CONSTRAINT orders_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS subtotal_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER user_id");
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER subtotal_amount");
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS handling_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER delivery_fee");
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER handling_fee");
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery' AFTER status");
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status VARCHAR(50) NOT NULL DEFAULT 'Pending' AFTER payment_method");
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS gateway_name VARCHAR(100) DEFAULT NULL AFTER payment_status");
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(100) DEFAULT NULL AFTER gateway_name");
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_payload TEXT DEFAULT NULL AFTER payment_reference");
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_eta_label VARCHAR(50) DEFAULT NULL AFTER payment_method");
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_name VARCHAR(100) DEFAULT NULL AFTER delivery_eta_label");
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_phone VARCHAR(20) DEFAULT NULL AFTER shipping_name");
    $conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_address TEXT DEFAULT NULL AFTER shipping_phone");

    $conn->query(
        "CREATE TABLE IF NOT EXISTS order_items (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            order_id INT(11) NOT NULL,
            product_id INT(11) NOT NULL,
            category_id INT(11) DEFAULT NULL,
            product_name VARCHAR(120) NOT NULL,
            category_name VARCHAR(100) DEFAULT NULL,
            unit_label VARCHAR(50) DEFAULT NULL,
            product_price INT(11) NOT NULL,
            quantity INT(11) NOT NULL,
            subtotal INT(11) NOT NULL,
            KEY order_id (order_id),
            CONSTRAINT order_items_ibfk_1 FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
    $conn->query("ALTER TABLE order_items ADD COLUMN IF NOT EXISTS category_id INT(11) DEFAULT NULL AFTER product_id");
    $conn->query("ALTER TABLE order_items MODIFY COLUMN product_name VARCHAR(120) NOT NULL");
    $conn->query("ALTER TABLE order_items ADD COLUMN IF NOT EXISTS category_name VARCHAR(100) DEFAULT NULL AFTER product_name");
    $conn->query("ALTER TABLE order_items ADD COLUMN IF NOT EXISTS unit_label VARCHAR(50) DEFAULT NULL AFTER category_name");

    seed_default_categories($conn);
    seed_quick_commerce_catalog_if_needed($conn);
}

function seed_default_categories(mysqli $conn): void
{
    $stmt = $conn->prepare(
        "INSERT INTO categories (id, name, description)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            description = VALUES(description)"
    );

    foreach (get_default_store_categories() as $category) {
        $id = (int) $category['id'];
        $name = $category['name'];
        $description = $category['description'];
        $stmt->bind_param('iss', $id, $name, $description);
        $stmt->execute();
    }

    $stmt->close();
}

function quick_commerce_catalog_requires_refresh(mysqli $conn): bool
{
    $result = $conn->query(
        "SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN image LIKE '%source.unsplash.com%' THEN 1 ELSE 0 END) AS placeholder_images,
            SUM(CASE WHEN name IN ('Glue', 'Eraser', 'Pen', 'Pencil', 'Candle', 'Sanitary Pads', 'Aluminium Foil', 'Tissue Paper') THEN 1 ELSE 0 END) AS legacy_items
         FROM products"
    );

    if (!$result) {
        return false;
    }

    $row = $result->fetch_assoc() ?: [];
    $total = (int) ($row['total'] ?? 0);
    $placeholderImages = (int) ($row['placeholder_images'] ?? 0);
    $legacyItems = (int) ($row['legacy_items'] ?? 0);

    if ($total === 0) {
        return true;
    }

    if ($total >= 12 && $placeholderImages >= (int) ceil($total * 0.7)) {
        return true;
    }

    return $legacyItems >= 3;
}

function seed_quick_commerce_catalog_if_needed(mysqli $conn): void
{
    if (!quick_commerce_catalog_requires_refresh($conn)) {
        return;
    }

    $conn->begin_transaction();

    try {
        $conn->query("DELETE FROM order_items");
        $conn->query("DELETE FROM orders");
        $conn->query("DELETE FROM products");
        $conn->query("ALTER TABLE order_items AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE orders AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE products AUTO_INCREMENT = 1");

        $stmt = $conn->prepare(
            "INSERT INTO products
                (id, sku, name, price, mrp, unit_label, description, image, stock_quantity, delivery_minutes, is_featured, badge_text, category_id)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        foreach (get_default_quick_commerce_products() as $product) {
            $id = (int) $product['id'];
            $sku = $product['sku'];
            $name = $product['name'];
            $price = (int) $product['price'];
            $mrp = (int) $product['mrp'];
            $unitLabel = $product['unit_label'];
            $description = $product['description'];
            $image = $product['image'];
            $stockQuantity = (int) $product['stock_quantity'];
            $deliveryMinutes = (int) $product['delivery_minutes'];
            $isFeatured = (int) $product['is_featured'];
            $badgeText = $product['badge_text'];
            $categoryId = (int) $product['category_id'];

            $stmt->bind_param(
                'issiisssiiisi',
                $id,
                $sku,
                $name,
                $price,
                $mrp,
                $unitLabel,
                $description,
                $image,
                $stockQuantity,
                $deliveryMinutes,
                $isFeatured,
                $badgeText,
                $categoryId
            );
            $stmt->execute();
        }

        $stmt->close();
        $conn->commit();
    } catch (Throwable $exception) {
        $conn->rollback();
    }
}

ensure_store_schema($conn);

function redirect_to(string $path): void
{
    header("Location: $path");
    exit();
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        redirect_to('../auth/login.php');
    }
}

function is_admin(): bool
{
    $role = strtolower(trim((string) ($_SESSION['role'] ?? '')));

    if ($role !== '') {
        return $role === 'admin';
    }

    return isset($_SESSION['email']) && $_SESSION['email'] === 'admin@gmail.com';
}

function require_admin(): void
{
    require_login();

    if (!is_admin()) {
        redirect_to('../dashboard/user_dashboard.php');
    }
}

function current_user_name(): string
{
    return $_SESSION['user_name'] ?? 'User';
}

function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function current_user_email(): string
{
    return $_SESSION['email'] ?? '';
}

function current_user_role(): string
{
    $role = strtolower(trim((string) ($_SESSION['role'] ?? 'user')));

    return $role === 'admin' ? 'admin' : 'user';
}

function current_user_verified(): bool
{
    return true;
}

function fetch_user_profile(mysqli $conn, int $userId): ?array
{
    $stmt = $conn->prepare(
        "SELECT id, name, email, role, phone_number, address_line1, address_line2, city, state, postal_code
         FROM users
         WHERE id = ?"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $profile;
}

function validate_user_profile_data(array $input): array
{
    $name = trim($input['name'] ?? '');
    $phoneNumber = preg_replace('/\s+/', '', trim((string) ($input['phone_number'] ?? '')));
    $addressLine1 = trim($input['address_line1'] ?? '');
    $addressLine2 = trim($input['address_line2'] ?? '');
    $city = trim($input['city'] ?? '');
    $state = trim($input['state'] ?? '');
    $postalCode = trim($input['postal_code'] ?? '');
    $errors = [];

    if ($name === '') {
        $errors[] = 'Name is required.';
    }

    if ($phoneNumber === '' || !preg_match('/^[0-9+\-]{7,15}$/', $phoneNumber)) {
        $errors[] = 'Enter a valid phone number.';
    }

    if ($addressLine1 === '') {
        $errors[] = 'Address line 1 is required.';
    }

    if ($city === '') {
        $errors[] = 'City is required.';
    }

    if ($state === '') {
        $errors[] = 'State is required.';
    }

    if ($postalCode === '') {
        $errors[] = 'Postal code is required.';
    }

    return [
        'errors' => $errors,
        'name' => $name,
        'phone_number' => $phoneNumber,
        'address_line1' => $addressLine1,
        'address_line2' => $addressLine2,
        'city' => $city,
        'state' => $state,
        'postal_code' => $postalCode,
    ];
}

function build_user_delivery_address(array $profile): string
{
    return trim(implode(', ', array_filter([
        trim((string) ($profile['address_line1'] ?? '')),
        trim((string) ($profile['address_line2'] ?? '')),
        trim((string) ($profile['city'] ?? '')),
        trim((string) ($profile['state'] ?? '')),
        trim((string) ($profile['postal_code'] ?? '')),
    ])));
}

function has_complete_delivery_profile(array $profile): bool
{
    return trim((string) ($profile['name'] ?? '')) !== ''
        && trim((string) ($profile['phone_number'] ?? '')) !== ''
        && trim((string) ($profile['address_line1'] ?? '')) !== ''
        && trim((string) ($profile['city'] ?? '')) !== ''
        && trim((string) ($profile['state'] ?? '')) !== ''
        && trim((string) ($profile['postal_code'] ?? '')) !== '';
}

function user_has_complete_delivery_profile(mysqli $conn, int $userId): bool
{
    $profile = fetch_user_profile($conn, $userId);

    if (!$profile) {
        return false;
    }

    return has_complete_delivery_profile($profile);
}

function get_user_delivery_snapshot(mysqli $conn, int $userId): ?array
{
    $profile = fetch_user_profile($conn, $userId);

    if (!$profile || !has_complete_delivery_profile($profile)) {
        return null;
    }

    return [
        'shipping_name' => trim((string) ($profile['name'] ?? '')),
        'shipping_phone' => trim((string) ($profile['phone_number'] ?? '')),
        'shipping_address' => build_user_delivery_address($profile),
    ];
}

function update_user_profile(mysqli $conn, int $userId, array $profileData): bool
{
    $stmt = $conn->prepare(
        "UPDATE users
         SET name = ?, phone_number = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?
         WHERE id = ?"
    );
    $stmt->bind_param(
        'sssssssi',
        $profileData['name'],
        $profileData['phone_number'],
        $profileData['address_line1'],
        $profileData['address_line2'],
        $profileData['city'],
        $profileData['state'],
        $profileData['postal_code'],
        $userId
    );
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        $_SESSION['user_name'] = $profileData['name'];
    }

    return $success;
}

function get_avatar_letter(string $name): string
{
    $name = trim($name);

    return $name === '' ? 'U' : strtoupper(substr($name, 0, 1));
}

function is_external_image(?string $image): bool
{
    return (bool) preg_match('/^https?:\/\//i', (string) $image);
}

function product_image_src(?string $image, string $localPrefix = '../uploads/'): string
{
    if (!$image) {
        return 'https://via.placeholder.com/600x400?text=No+Image';
    }

    if (is_external_image($image)) {
        return $image;
    }

    return $localPrefix . $image;
}

function product_short_description(?string $description, int $limit = 90): string
{
    $description = trim((string) $description);

    if ($description === '') {
        return 'Fresh and quality grocery item ready for quick delivery.';
    }

    if (mb_strlen($description) <= $limit) {
        return $description;
    }

    return mb_substr($description, 0, $limit - 3) . '...';
}

function product_unit_label(array $product): string
{
    $unitLabel = trim((string) ($product['unit_label'] ?? ''));

    return $unitLabel !== '' ? $unitLabel : '1 pack';
}

function product_mrp_value(array $product): int
{
    $mrp = (int) ($product['mrp'] ?? 0);
    $price = (int) ($product['price'] ?? 0);

    return $mrp > 0 ? max($mrp, $price) : $price;
}

function product_savings_amount(array $product): int
{
    return max(0, product_mrp_value($product) - (int) ($product['price'] ?? 0));
}

function product_is_in_stock(array $product): bool
{
    return (int) ($product['stock_quantity'] ?? 0) > 0;
}

function product_stock_status(array $product): string
{
    $stockQuantity = (int) ($product['stock_quantity'] ?? 0);

    if ($stockQuantity <= 0) {
        return 'Out of stock';
    }

    if ($stockQuantity <= STORE_LOW_STOCK_LIMIT) {
        return 'Only ' . $stockQuantity . ' left';
    }

    return 'In stock';
}

function product_delivery_window(array $product): string
{
    $minutes = max(10, (int) ($product['delivery_minutes'] ?? 20));

    return $minutes . '-' . ($minutes + 10) . ' mins';
}

function product_badge_label(array $product): string
{
    $badge = trim((string) ($product['badge_text'] ?? ''));

    if ($badge !== '') {
        return $badge;
    }

    return (int) ($product['is_featured'] ?? 0) === 1 ? 'Featured' : '';
}

function get_product_select_fields(): string
{
    return "p.id, p.sku, p.name, p.price, p.mrp, p.unit_label, p.description, p.image,
            p.stock_quantity, p.delivery_minutes, p.is_featured, p.badge_text, p.category_id,
            c.name AS category_name, c.description AS category_description, p.created_at";
}

function fetch_all_categories(mysqli $conn): array
{
    $result = $conn->query("SELECT id, name, description FROM categories ORDER BY id ASC");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_products(mysqli $conn, ?int $categoryId = null, string $search = '', ?int $limit = null): array
{
    $search = trim($search);
    $sql = "SELECT " . get_product_select_fields() . "
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id";
    $conditions = [];
    $types = '';
    $params = [];

    if ($categoryId !== null) {
        $conditions[] = 'p.category_id = ?';
        $types .= 'i';
        $params[] = $categoryId;
    }

    if ($search !== '') {
        $conditions[] = '(p.name LIKE ? OR p.description LIKE ? OR p.unit_label LIKE ? OR p.badge_text LIKE ? OR c.name LIKE ?)';
        $like = '%' . $search . '%';
        $types .= 'sssss';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY CASE WHEN p.stock_quantity > 0 THEN 0 ELSE 1 END ASC, p.is_featured DESC, p.id DESC';

    if ($limit !== null) {
        $sql .= ' LIMIT ' . max(1, $limit);
    }

    if ($types !== '') {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    $products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    if (isset($stmt)) {
        $stmt->close();
    }

    return $products;
}

function fetch_all_products(mysqli $conn, string $search = ''): array
{
    return fetch_products($conn, null, $search);
}

function fetch_products_by_category_id(mysqli $conn, int $categoryId, string $search = ''): array
{
    return fetch_products($conn, $categoryId, $search);
}

function fetch_recent_products(mysqli $conn, int $limit = 5): array
{
    return fetch_products($conn, null, '', $limit);
}

function fetch_product_by_id(mysqli $conn, int $productId): ?array
{
    $stmt = $conn->prepare(
        "SELECT " . get_product_select_fields() . "
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.id = ?"
    );
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $product;
}

function validate_product_data(array $input, bool $imageRequired = true): array
{
    $errors = [];
    $name = trim((string) ($input['name'] ?? ''));
    $price = trim((string) ($input['price'] ?? ''));
    $mrp = trim((string) ($input['mrp'] ?? ''));
    $unitLabel = trim((string) ($input['unit_label'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $imageUrl = trim((string) ($input['image_url'] ?? ''));
    $badgeText = trim((string) ($input['badge_text'] ?? ''));
    $stockQuantity = filter_var($input['stock_quantity'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0],
    ]);
    $deliveryMinutes = filter_var($input['delivery_minutes'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 10, 'max_range' => 120],
    ]);
    $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);
    $hasImageFile = !empty($_FILES['image']['name']);
    $priceValue = (int) round((float) $price);
    $mrpValue = $mrp === '' ? $priceValue : (int) round((float) $mrp);

    if ($name === '') {
        $errors[] = 'Product name is required.';
    }

    if ($price === '' || !is_numeric($price) || (float) $price <= 0) {
        $errors[] = 'Enter a valid selling price.';
    }

    if ($mrp !== '' && (!is_numeric($mrp) || (float) $mrp <= 0)) {
        $errors[] = 'Enter a valid MRP.';
    }

    if (empty($errors) && $mrpValue < $priceValue) {
        $errors[] = 'MRP should be greater than or equal to selling price.';
    }

    if ($unitLabel === '') {
        $errors[] = 'Unit or pack size is required.';
    }

    if ($description === '') {
        $errors[] = 'Product description is required.';
    }

    if ($stockQuantity === false) {
        $errors[] = 'Enter a valid stock quantity.';
    }

    if ($deliveryMinutes === false) {
        $errors[] = 'Delivery ETA should be between 10 and 120 minutes.';
    }

    if ($categoryId === false) {
        $errors[] = 'Please select a category.';
    }

    if ($badgeText !== '' && mb_strlen($badgeText) > 40) {
        $errors[] = 'Badge text should stay within 40 characters.';
    }

    if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'Enter a valid image URL.';
    }

    if ($imageRequired && !$hasImageFile && $imageUrl === '') {
        $errors[] = 'Please upload an image or enter an image URL.';
    }

    return [
        'errors' => $errors,
        'name' => $name,
        'price' => $priceValue,
        'mrp' => max($priceValue, $mrpValue),
        'unit_label' => $unitLabel,
        'description' => $description,
        'category_id' => $categoryId === false ? 0 : (int) $categoryId,
        'image_url' => $imageUrl,
        'stock_quantity' => $stockQuantity === false ? 0 : (int) $stockQuantity,
        'delivery_minutes' => $deliveryMinutes === false ? 20 : (int) $deliveryMinutes,
        'badge_text' => $badgeText,
        'is_featured' => isset($input['is_featured']) ? 1 : 0,
    ];
}

function handle_product_image_upload(array $file): array
{
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Please upload a valid image.'];
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Image must be smaller than 2MB.'];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowed, true)) {
        return ['success' => false, 'message' => 'Allowed image formats: JPG, JPEG, PNG, WEBP.'];
    }

    $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', basename($file['name']));
    $path = __DIR__ . '/../uploads/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return ['success' => false, 'message' => 'Failed to save uploaded image.'];
    }

    return ['success' => true, 'filename' => $filename];
}

function resolve_product_image(array $input, array $file, bool $imageRequired = true, ?string $existingImage = null): array
{
    $imageUrl = trim($input['image_url'] ?? '');
    $hasImageFile = !empty($file['name']);

    if ($hasImageFile) {
        return handle_product_image_upload($file);
    }

    if ($imageUrl !== '') {
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => 'Enter a valid image URL.'];
        }

        return ['success' => true, 'filename' => $imageUrl];
    }

    if (!$imageRequired && $existingImage !== null) {
        return ['success' => true, 'filename' => $existingImage];
    }

    return ['success' => false, 'message' => 'Please upload an image or enter an image URL.'];
}

function delete_product_image(?string $imageName): void
{
    if (!$imageName || is_external_image($imageName)) {
        return;
    }

    $path = __DIR__ . '/../uploads/' . $imageName;

    if (is_file($path)) {
        unlink($path);
    }
}

function generate_product_sku(string $name, int $categoryId, ?string $existingSku = null): string
{
    $existingSku = trim((string) $existingSku);

    if ($existingSku !== '') {
        return strtoupper($existingSku);
    }

    $base = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $name));
    $base = substr($base !== '' ? $base : 'ITEM', 0, 8);

    return 'HF-' . str_pad((string) $categoryId, 2, '0', STR_PAD_LEFT) . '-' . $base . '-' . substr(strtoupper(md5($name . microtime())), 0, 4);
}

function create_product(mysqli $conn, array $productData, string $image): bool
{
    $sku = generate_product_sku($productData['name'], (int) $productData['category_id']);
    $badgeText = trim((string) ($productData['badge_text'] ?? ''));
    if ($badgeText === '' && (int) ($productData['is_featured'] ?? 0) === 1) {
        $badgeText = 'Featured';
    }

    $stmt = $conn->prepare(
        "INSERT INTO products
            (sku, name, price, mrp, unit_label, description, image, stock_quantity, delivery_minutes, is_featured, badge_text, category_id)
         VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'ssiisssiiisi',
        $sku,
        $productData['name'],
        $productData['price'],
        $productData['mrp'],
        $productData['unit_label'],
        $productData['description'],
        $image,
        $productData['stock_quantity'],
        $productData['delivery_minutes'],
        $productData['is_featured'],
        $badgeText,
        $productData['category_id']
    );
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

function update_product(mysqli $conn, int $id, array $productData, string $image, ?string $existingSku = null): bool
{
    $sku = generate_product_sku($productData['name'], (int) $productData['category_id'], $existingSku);
    $badgeText = trim((string) ($productData['badge_text'] ?? ''));
    if ($badgeText === '' && (int) ($productData['is_featured'] ?? 0) === 1) {
        $badgeText = 'Featured';
    }

    $stmt = $conn->prepare(
        "UPDATE products
         SET sku = ?, name = ?, price = ?, mrp = ?, unit_label = ?, description = ?, image = ?,
             stock_quantity = ?, delivery_minutes = ?, is_featured = ?, badge_text = ?, category_id = ?
         WHERE id = ?"
    );
    $stmt->bind_param(
        'ssiisssiiisii',
        $sku,
        $productData['name'],
        $productData['price'],
        $productData['mrp'],
        $productData['unit_label'],
        $productData['description'],
        $image,
        $productData['stock_quantity'],
        $productData['delivery_minutes'],
        $productData['is_featured'],
        $badgeText,
        $productData['category_id'],
        $id
    );
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

function delete_product(mysqli $conn, int $id): bool
{
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

function ensure_cart_exists(): void
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function add_product_to_cart(mysqli $conn, int $productId, int $quantity = 1): array
{
    $product = fetch_product_by_id($conn, $productId);

    if (!$product) {
        return ['success' => false, 'message' => 'Product not found.'];
    }

    $stockQuantity = max(0, (int) ($product['stock_quantity'] ?? 0));
    if ($stockQuantity <= 0) {
        return ['success' => false, 'message' => 'This item is currently out of stock.'];
    }

    ensure_cart_exists();

    $quantity = max(1, $quantity);
    $currentQuantity = (int) ($_SESSION['cart'][$productId] ?? 0);
    $newQuantity = min($stockQuantity, $currentQuantity + $quantity);
    $_SESSION['cart'][$productId] = $newQuantity;

    if ($newQuantity === $currentQuantity) {
        return ['success' => false, 'message' => 'Only ' . $stockQuantity . ' unit(s) are available right now.'];
    }

    $message = $newQuantity < ($currentQuantity + $quantity)
        ? 'Stock is limited, so cart quantity was capped at the available inventory.'
        : 'Product added to cart.';

    return ['success' => true, 'message' => $message];
}

function add_to_cart(int $productId, int $quantity = 1): void
{
    global $conn;

    if ($conn instanceof mysqli) {
        add_product_to_cart($conn, $productId, $quantity);
        return;
    }

    ensure_cart_exists();
    $_SESSION['cart'][$productId] = (int) ($_SESSION['cart'][$productId] ?? 0) + max(1, $quantity);
}

function update_product_quantity_in_cart(mysqli $conn, int $productId, int $quantity): array
{
    ensure_cart_exists();

    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
        return ['success' => true, 'message' => 'Item removed from cart.'];
    }

    $product = fetch_product_by_id($conn, $productId);

    if (!$product) {
        unset($_SESSION['cart'][$productId]);
        return ['success' => false, 'message' => 'Product not found.'];
    }

    $stockQuantity = max(0, (int) ($product['stock_quantity'] ?? 0));
    if ($stockQuantity <= 0) {
        unset($_SESSION['cart'][$productId]);
        return ['success' => false, 'message' => 'This item is now out of stock and was removed from your cart.'];
    }

    $newQuantity = min($stockQuantity, $quantity);
    $_SESSION['cart'][$productId] = $newQuantity;

    $message = $newQuantity < $quantity
        ? 'Requested quantity exceeded current stock, so it was adjusted automatically.'
        : 'Cart updated successfully.';

    return ['success' => true, 'message' => $message];
}

function update_cart_item(int $productId, int $quantity): void
{
    global $conn;

    if ($conn instanceof mysqli) {
        update_product_quantity_in_cart($conn, $productId, $quantity);
        return;
    }

    ensure_cart_exists();

    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }

    $_SESSION['cart'][$productId] = $quantity;
}

function remove_cart_item(int $productId): void
{
    ensure_cart_exists();
    unset($_SESSION['cart'][$productId]);
}

function get_cart_items(mysqli $conn): array
{
    ensure_cart_exists();

    if (empty($_SESSION['cart'])) {
        return [];
    }

    $ids = array_values(array_unique(array_map('intval', array_keys($_SESSION['cart']))));
    if (empty($ids)) {
        $_SESSION['cart'] = [];
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = $conn->prepare(
        "SELECT " . get_product_select_fields() . "
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.id IN ($placeholders)"
    );
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    $seenProducts = [];

    while ($row = $result->fetch_assoc()) {
        $productId = (int) $row['id'];
        $requestedQuantity = (int) ($_SESSION['cart'][$productId] ?? 0);
        $stockQuantity = max(0, (int) ($row['stock_quantity'] ?? 0));
        $seenProducts[$productId] = true;

        if ($requestedQuantity <= 0) {
            continue;
        }

        if ($stockQuantity <= 0) {
            unset($_SESSION['cart'][$productId]);
            continue;
        }

        $quantity = min($requestedQuantity, $stockQuantity);
        if ($quantity !== $requestedQuantity) {
            $_SESSION['cart'][$productId] = $quantity;
        }

        $row['quantity'] = $quantity;
        $row['subtotal'] = $quantity * (int) $row['price'];
        $row['mrp_subtotal'] = $quantity * product_mrp_value($row);
        $row['savings'] = max(0, $row['mrp_subtotal'] - $row['subtotal']);
        $row['stock_status'] = product_stock_status($row);
        $row['delivery_label'] = product_delivery_window($row);
        $items[] = $row;
    }

    $stmt->close();

    foreach ($ids as $id) {
        if (!isset($seenProducts[$id])) {
            unset($_SESSION['cart'][$id]);
        }
    }

    usort($items, static function ($a, $b) {
        return $b['id'] <=> $a['id'];
    });

    return $items;
}

function estimate_delivery_label_from_items(array $items): string
{
    if (empty($items)) {
        return '20-30 mins';
    }

    $baseMinutes = 0;
    foreach ($items as $item) {
        $baseMinutes = max($baseMinutes, (int) ($item['delivery_minutes'] ?? 20));
    }

    $minMinutes = max(10, $baseMinutes);

    return $minMinutes . '-' . ($minMinutes + 10) . ' mins';
}

function get_cart_totals(mysqli $conn): array
{
    $items = get_cart_items($conn);
    $totalItems = 0;
    $subtotalAmount = 0;
    $mrpTotal = 0;

    foreach ($items as $item) {
        $totalItems += $item['quantity'];
        $subtotalAmount += $item['subtotal'];
        $mrpTotal += (int) ($item['mrp_subtotal'] ?? $item['subtotal']);
    }

    $discountAmount = max(0, $mrpTotal - $subtotalAmount);
    $deliveryFee = $subtotalAmount === 0
        ? 0
        : ($subtotalAmount >= STORE_FREE_DELIVERY_THRESHOLD ? 0 : STORE_DEFAULT_DELIVERY_FEE);
    $handlingFee = $subtotalAmount === 0 ? 0 : STORE_DEFAULT_HANDLING_FEE;
    $amountForFreeDelivery = $subtotalAmount > 0 && $subtotalAmount < STORE_FREE_DELIVERY_THRESHOLD
        ? STORE_FREE_DELIVERY_THRESHOLD - $subtotalAmount
        : 0;
    $totalAmount = $subtotalAmount + $deliveryFee + $handlingFee;

    return [
        'items' => $items,
        'total_items' => $totalItems,
        'subtotal_amount' => $subtotalAmount,
        'mrp_total' => $mrpTotal,
        'discount_amount' => $discountAmount,
        'delivery_fee' => $deliveryFee,
        'handling_fee' => $handlingFee,
        'amount_for_free_delivery' => $amountForFreeDelivery,
        'free_delivery_threshold' => STORE_FREE_DELIVERY_THRESHOLD,
        'estimated_delivery' => estimate_delivery_label_from_items($items),
        'payment_method' => STORE_DEFAULT_PAYMENT_METHOD,
        'total_amount' => $totalAmount,
    ];
}

function build_profile_data_from_checkout(array $checkoutData): array
{
    return [
        'name' => $checkoutData['shipping_name'],
        'phone_number' => $checkoutData['shipping_phone'],
        'address_line1' => $checkoutData['address_line1'],
        'address_line2' => $checkoutData['address_line2'],
        'city' => $checkoutData['city'],
        'state' => $checkoutData['state'],
        'postal_code' => $checkoutData['postal_code'],
    ];
}

function place_order_from_checkout(mysqli $conn, int $userId, array $checkoutData): array
{
    $cart = get_cart_totals($conn);
    $shippingName = trim((string) ($checkoutData['shipping_name'] ?? ''));
    $shippingPhone = trim((string) ($checkoutData['shipping_phone'] ?? ''));
    $shippingAddress = trim((string) ($checkoutData['shipping_address'] ?? ''));
    $paymentMethod = trim((string) ($checkoutData['payment_method'] ?? STORE_DEFAULT_PAYMENT_METHOD));
    $paymentStatus = trim((string) ($checkoutData['payment_status'] ?? 'Pending'));
    $gatewayName = trim((string) ($checkoutData['gateway_name'] ?? PAYMENT_GATEWAY_NAME));
    $paymentReference = trim((string) ($checkoutData['payment_reference'] ?? ''));
    $paymentPayload = isset($checkoutData['payment_payload']) ? (string) $checkoutData['payment_payload'] : '';

    if (empty($cart['items']) || $shippingName === '' || $shippingPhone === '' || $shippingAddress === '') {
        return ['success' => false, 'message' => 'Cart or shipping details are incomplete.'];
    }

    $conn->begin_transaction();

    try {
        $status = $paymentStatus === 'Paid' ? 'Confirmed' : 'Pending';
        $etaLabel = $cart['estimated_delivery'];
        $orderStmt = $conn->prepare(
            "INSERT INTO orders
                (user_id, subtotal_amount, delivery_fee, handling_fee, discount_amount, total_amount, status, payment_method, payment_status, gateway_name, payment_reference, payment_payload, delivery_eta_label, shipping_name, shipping_phone, shipping_address)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $orderStmt->bind_param(
            'idddddsssssssss',
            $userId,
            $cart['subtotal_amount'],
            $cart['delivery_fee'],
            $cart['handling_fee'],
            $cart['discount_amount'],
            $cart['total_amount'],
            $status,
            $paymentMethod,
            $paymentStatus,
            $gatewayName,
            $paymentReference,
            $paymentPayload,
            $etaLabel,
            $shippingName,
            $shippingPhone,
            $shippingAddress
        );
        $orderStmt->execute();
        $orderId = $conn->insert_id;
        $orderStmt->close();

        $itemStmt = $conn->prepare(
            "INSERT INTO order_items
                (order_id, product_id, category_id, product_name, category_name, unit_label, product_price, quantity, subtotal)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stockStmt = $conn->prepare("UPDATE products SET stock_quantity = GREATEST(stock_quantity - ?, 0) WHERE id = ?");

        foreach ($cart['items'] as $item) {
            $categoryId = isset($item['category_id']) ? (int) $item['category_id'] : 0;
            $categoryName = trim((string) ($item['category_name'] ?? ''));
            if ($categoryName === '') {
                $categoryName = 'Uncategorized';
            }
            $unitLabel = product_unit_label($item);

            $itemStmt->bind_param(
                'iiisssiii',
                $orderId,
                $item['id'],
                $categoryId,
                $item['name'],
                $categoryName,
                $unitLabel,
                $item['price'],
                $item['quantity'],
                $item['subtotal']
            );
            $itemStmt->execute();

            $stockStmt->bind_param('ii', $item['quantity'], $item['id']);
            $stockStmt->execute();
        }

        $itemStmt->close();
        $stockStmt->close();

        if ((int) ($checkoutData['save_to_profile'] ?? 0) === 1) {
            update_user_profile($conn, $userId, build_profile_data_from_checkout($checkoutData));
        }

        $conn->commit();
        $_SESSION['cart'] = [];

        return ['success' => true, 'order_id' => $orderId];
    } catch (Throwable $exception) {
        $conn->rollback();
        return ['success' => false, 'message' => 'Unable to place the order right now.'];
    }
}

function place_order_from_cart(mysqli $conn, int $userId): bool
{
    $deliverySnapshot = get_user_delivery_snapshot($conn, $userId);

    if ($deliverySnapshot === null) {
        return false;
    }

    $result = place_order_from_checkout($conn, $userId, [
        'shipping_name' => $deliverySnapshot['shipping_name'],
        'shipping_phone' => $deliverySnapshot['shipping_phone'],
        'shipping_address' => $deliverySnapshot['shipping_address'],
        'address_line1' => (string) ($deliverySnapshot['shipping_address'] ?? ''),
        'address_line2' => '',
        'city' => '',
        'state' => '',
        'postal_code' => '',
        'payment_method' => STORE_DEFAULT_PAYMENT_METHOD,
        'payment_status' => 'Pending',
        'gateway_name' => PAYMENT_GATEWAY_NAME,
        'payment_reference' => 'COD-' . strtoupper(substr(app_random_token(6), 0, 10)),
        'payment_payload' => json_encode(['source' => 'cart_default_flow']),
        'save_to_profile' => 0,
    ]);

    return (bool) ($result['success'] ?? false);
}

function fetch_user_orders(mysqli $conn, int $userId): array
{
    $stmt = $conn->prepare(
        "SELECT o.id, o.subtotal_amount, o.delivery_fee, o.handling_fee, o.discount_amount, o.total_amount, o.status, o.payment_method, o.payment_status, o.gateway_name, o.payment_reference, o.delivery_eta_label, o.shipping_name, o.shipping_phone, o.shipping_address, o.created_at,
                COALESCE(
                    GROUP_CONCAT(
                        CONCAT(
                            oi.product_name,
                            ' x',
                            oi.quantity,
                            ' (',
                            COALESCE(NULLIF(oi.unit_label, ''), 'pack'),
                            ')'
                        )
                        ORDER BY oi.id ASC SEPARATOR ', '
                    ),
                    ''
                ) AS items_summary
         FROM orders o
         LEFT JOIN order_items oi ON oi.order_id = o.id
         WHERE o.user_id = ?
         GROUP BY o.id, o.subtotal_amount, o.delivery_fee, o.handling_fee, o.discount_amount, o.total_amount, o.status, o.payment_method, o.payment_status, o.gateway_name, o.payment_reference, o.delivery_eta_label, o.shipping_name, o.shipping_phone, o.shipping_address, o.created_at
         ORDER BY o.id DESC"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $orders;
}

function fetch_admin_orders(mysqli $conn): array
{
    $result = $conn->query(
        "SELECT o.id, o.subtotal_amount, o.delivery_fee, o.handling_fee, o.discount_amount, o.total_amount, o.status, o.payment_method, o.payment_status, o.gateway_name, o.payment_reference, o.delivery_eta_label, o.shipping_name, o.shipping_phone, o.shipping_address, o.created_at, users.name AS user_name, users.email,
                COALESCE(
                    GROUP_CONCAT(
                        CONCAT(
                            oi.product_name,
                            ' x',
                            oi.quantity,
                            ' (',
                            COALESCE(NULLIF(oi.unit_label, ''), 'pack'),
                            ')'
                        )
                        ORDER BY oi.id ASC SEPARATOR ', '
                    ),
                    ''
                ) AS items_summary
         FROM orders o
         INNER JOIN users ON users.id = o.user_id
         LEFT JOIN order_items oi ON oi.order_id = o.id
         GROUP BY o.id, o.subtotal_amount, o.delivery_fee, o.handling_fee, o.discount_amount, o.total_amount, o.status, o.payment_method, o.payment_status, o.gateway_name, o.payment_reference, o.delivery_eta_label, o.shipping_name, o.shipping_phone, o.shipping_address, o.created_at, users.name, users.email
         ORDER BY o.id DESC"
    );

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function get_dashboard_counts(mysqli $conn): array
{
    $counts = [
        'products' => 0,
        'orders' => 0,
        'users' => 0,
        'sales' => 0,
        'featured_products' => 0,
        'low_stock' => 0,
        'out_of_stock' => 0,
    ];

    $productResult = $conn->query(
        "SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) AS featured_total,
            SUM(CASE WHEN stock_quantity > 0 AND stock_quantity <= " . STORE_LOW_STOCK_LIMIT . " THEN 1 ELSE 0 END) AS low_stock_total,
            SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) AS out_of_stock_total
         FROM products"
    );
    if ($productResult) {
        $row = $productResult->fetch_assoc() ?: [];
        $counts['products'] = (int) ($row['total'] ?? 0);
        $counts['featured_products'] = (int) ($row['featured_total'] ?? 0);
        $counts['low_stock'] = (int) ($row['low_stock_total'] ?? 0);
        $counts['out_of_stock'] = (int) ($row['out_of_stock_total'] ?? 0);
    }

    $userResult = $conn->query("SELECT COUNT(*) AS total FROM users");
    if ($userResult) {
        $counts['users'] = (int) ($userResult->fetch_assoc()['total'] ?? 0);
    }

    $orderResult = $conn->query("SELECT COUNT(*) AS total, SUM(total_amount) AS sales FROM orders");
    if ($orderResult) {
        $row = $orderResult->fetch_assoc();
        $counts['orders'] = (int) ($row['total'] ?? 0);
        $counts['sales'] = (int) round((float) ($row['sales'] ?? 0));
    }

    return $counts;
}
?>
