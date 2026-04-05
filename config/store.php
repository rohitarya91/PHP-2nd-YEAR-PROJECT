<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../assets/db.php';

function ensure_store_schema(mysqli $conn): void
{
    $conn->query(
        "CREATE TABLE IF NOT EXISTS categories (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS description TEXT NULL AFTER price");
    $conn->query("ALTER TABLE products MODIFY COLUMN image VARCHAR(255) DEFAULT NULL");
    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INT(11) NULL AFTER image");

    $conn->query(
        "CREATE TABLE IF NOT EXISTS orders (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status VARCHAR(50) NOT NULL DEFAULT 'Placed',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY user_id (user_id),
            CONSTRAINT orders_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS order_items (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            order_id INT(11) NOT NULL,
            product_id INT(11) NOT NULL,
            product_name VARCHAR(100) NOT NULL,
            product_price INT(11) NOT NULL,
            quantity INT(11) NOT NULL,
            subtotal INT(11) NOT NULL,
            KEY order_id (order_id),
            CONSTRAINT order_items_ibfk_1 FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    $conn->query("ALTER TABLE order_items ADD COLUMN IF NOT EXISTS category_id INT(11) NULL AFTER product_id");
    $conn->query("ALTER TABLE order_items ADD COLUMN IF NOT EXISTS category_name VARCHAR(100) DEFAULT NULL AFTER product_name");
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

function current_user_email(): string
{
    return $_SESSION['email'] ?? '';
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

function fetch_all_categories(mysqli $conn): array
{
    $result = $conn->query("SELECT id, name FROM categories ORDER BY id ASC");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_products(mysqli $conn, ?int $categoryId = null, string $search = '', ?int $limit = null): array
{
    $search = trim($search);
    $sql = "SELECT p.id, p.name, p.price, p.description, p.image, p.category_id, c.name AS category_name, p.created_at
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
        $conditions[] = '(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)';
        $like = '%' . $search . '%';
        $types .= 'sss';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY p.id DESC';

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
        "SELECT p.id, p.name, p.price, p.description, p.image, p.category_id, c.name AS category_name, p.created_at
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
    $name = trim($input['name'] ?? '');
    $price = trim($input['price'] ?? '');
    $description = trim($input['description'] ?? '');
    $imageUrl = trim($input['image_url'] ?? '');
    $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if ($name === '') {
        $errors[] = 'Product name is required.';
    }

    if ($price === '' || !is_numeric($price) || (float) $price <= 0) {
        $errors[] = 'Enter a valid price.';
    }

    if ($description === '') {
        $errors[] = 'Product description is required.';
    }

    if ($categoryId === false) {
        $errors[] = 'Please select a category.';
    }

    if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'Enter a valid image URL.';
    }

    $hasImageFile = !empty($_FILES['image']['name']);
    if ($imageRequired && !$hasImageFile && $imageUrl === '') {
        $errors[] = 'Please upload an image or enter an image URL.';
    }

    return [
        'errors' => $errors,
        'name' => $name,
        'price' => (int) round((float) $price),
        'description' => $description,
        'category_id' => $categoryId === false ? 0 : (int) $categoryId,
        'image_url' => $imageUrl,
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

function create_product(mysqli $conn, string $name, int $price, string $description, string $image, int $categoryId): bool
{
    $stmt = $conn->prepare("INSERT INTO products (name, price, description, image, category_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sissi', $name, $price, $description, $image, $categoryId);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

function update_product(mysqli $conn, int $id, string $name, int $price, string $description, string $image, int $categoryId): bool
{
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ?, category_id = ? WHERE id = ?");
    $stmt->bind_param('sissii', $name, $price, $description, $image, $categoryId, $id);
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

function add_to_cart(int $productId, int $quantity = 1): void
{
    ensure_cart_exists();
    $_SESSION['cart'][$productId] = (int) ($_SESSION['cart'][$productId] ?? 0) + $quantity;
}

function update_cart_item(int $productId, int $quantity): void
{
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

    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = $conn->prepare(
        "SELECT p.id, p.name, p.price, p.description, p.image, p.category_id, c.name AS category_name
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.id IN ($placeholders)"
    );
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];

    while ($row = $result->fetch_assoc()) {
        $qty = (int) ($_SESSION['cart'][$row['id']] ?? 0);
        $row['quantity'] = $qty;
        $row['subtotal'] = $qty * (int) $row['price'];
        $items[] = $row;
    }

    $stmt->close();

    usort($items, static function ($a, $b) {
        return $b['id'] <=> $a['id'];
    });

    return $items;
}

function get_cart_totals(mysqli $conn): array
{
    $items = get_cart_items($conn);
    $totalItems = 0;
    $totalAmount = 0;

    foreach ($items as $item) {
        $totalItems += $item['quantity'];
        $totalAmount += $item['subtotal'];
    }

    return [
        'items' => $items,
        'total_items' => $totalItems,
        'total_amount' => $totalAmount,
    ];
}

function place_order_from_cart(mysqli $conn, int $userId): bool
{
    $cart = get_cart_totals($conn);

    if (empty($cart['items'])) {
        return false;
    }

    $conn->begin_transaction();

    try {
        $status = 'Placed';
        $orderStmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, ?)");
        $orderStmt->bind_param('ids', $userId, $cart['total_amount'], $status);
        $orderStmt->execute();
        $orderId = $conn->insert_id;
        $orderStmt->close();

        $itemStmt = $conn->prepare(
            "INSERT INTO order_items (order_id, product_id, category_id, product_name, category_name, product_price, quantity, subtotal)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($cart['items'] as $item) {
            $categoryId = isset($item['category_id']) ? (int) $item['category_id'] : null;
            $categoryName = trim((string) ($item['category_name'] ?? ''));
            if ($categoryName === '') {
                $categoryName = 'Uncategorized';
            }

            $itemStmt->bind_param(
                'iiissiii',
                $orderId,
                $item['id'],
                $categoryId,
                $item['name'],
                $categoryName,
                $item['price'],
                $item['quantity'],
                $item['subtotal']
            );
            $itemStmt->execute();
        }

        $itemStmt->close();
        $conn->commit();
        $_SESSION['cart'] = [];

        return true;
    } catch (Throwable $exception) {
        $conn->rollback();
        return false;
    }
}

function fetch_user_orders(mysqli $conn, int $userId): array
{
    $stmt = $conn->prepare(
        "SELECT o.id, o.total_amount, o.status, o.created_at,
                COALESCE(
                    GROUP_CONCAT(
                        CONCAT(
                            oi.product_name,
                            ' (',
                            COALESCE(NULLIF(oi.category_name, ''), 'Uncategorized'),
                            ') x',
                            oi.quantity
                        )
                        ORDER BY oi.id ASC SEPARATOR ', '
                    ),
                    ''
                ) AS items_summary
         FROM orders o
         LEFT JOIN order_items oi ON oi.order_id = o.id
         WHERE o.user_id = ?
         GROUP BY o.id, o.total_amount, o.status, o.created_at
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
        "SELECT o.id, o.total_amount, o.status, o.created_at, users.name AS user_name, users.email,
                COALESCE(
                    GROUP_CONCAT(
                        CONCAT(
                            oi.product_name,
                            ' (',
                            COALESCE(NULLIF(oi.category_name, ''), 'Uncategorized'),
                            ') x',
                            oi.quantity
                        )
                        ORDER BY oi.id ASC SEPARATOR ', '
                    ),
                    ''
                ) AS items_summary
         FROM orders o
         INNER JOIN users ON users.id = o.user_id
         LEFT JOIN order_items oi ON oi.order_id = o.id
         GROUP BY o.id, o.total_amount, o.status, o.created_at, users.name, users.email
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
    ];

    $productResult = $conn->query("SELECT COUNT(*) AS total FROM products");
    if ($productResult) {
        $counts['products'] = (int) ($productResult->fetch_assoc()['total'] ?? 0);
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
