<?php
require_once __DIR__ . '/config/store.php';

header('Content-Type: application/json; charset=utf-8');

$categoryParam = $_GET['category_id'] ?? '';
$search = trim((string) ($_GET['search'] ?? ''));
$categoryId = null;

if ($categoryParam !== '') {
    $categoryId = filter_var($categoryParam, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if ($categoryId === false) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid category_id.',
        ]);
        exit();
    }
}

$products = $categoryId === null
    ? fetch_all_products($conn, $search)
    : fetch_products_by_category_id($conn, (int) $categoryId, $search);

$payload = array_map(static function (array $product): array {
    return [
        'id' => (int) $product['id'],
        'name' => $product['name'],
        'price' => (int) $product['price'],
        'mrp' => (int) ($product['mrp'] ?? $product['price']),
        'unit_label' => $product['unit_label'] ?? '',
        'description' => $product['description'] ?? '',
        'image' => product_image_src($product['image'] ?? null, '/uploads/'),
        'stock_quantity' => (int) ($product['stock_quantity'] ?? 0),
        'delivery_minutes' => (int) ($product['delivery_minutes'] ?? 20),
        'is_featured' => (int) ($product['is_featured'] ?? 0),
        'badge_text' => $product['badge_text'] ?? '',
        'category_id' => isset($product['category_id']) ? (int) $product['category_id'] : null,
        'category_name' => $product['category_name'] ?? '',
    ];
}, $products);

echo json_encode([
    'success' => true,
    'search' => $search,
    'category_id' => $categoryId,
    'products' => $payload,
]);
