<?php
require_once __DIR__ . '/../config/store.php';

header('Content-Type: application/json; charset=utf-8');

$categoryParam = $_GET['category_id'] ?? '';
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
    ? fetch_all_products($conn)
    : fetch_products_by_category_id($conn, $categoryId);

$payload = array_map(static function (array $product): array {
    return [
        'id' => (int) $product['id'],
        'name' => $product['name'],
        'price' => (int) $product['price'],
        'description' => $product['description'] ?? '',
        'image' => product_image_src($product['image'] ?? null, '../uploads/'),
        'category_id' => isset($product['category_id']) ? (int) $product['category_id'] : null,
        'category_name' => $product['category_name'] ?? '',
    ];
}, $products);

echo json_encode([
    'success' => true,
    'products' => $payload,
]);
