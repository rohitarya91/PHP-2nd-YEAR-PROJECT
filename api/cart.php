<?php
require_once __DIR__ . '/../config/store.php';

require_login();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed.',
    ]);
    exit();
}

$action = $_POST['action'] ?? '';
$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($action !== 'add_to_cart' || $productId === false) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid cart request.',
    ]);
    exit();
}

$product = fetch_product_by_id($conn, (int) $productId);

if (!$product) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Product not found.',
    ]);
    exit();
}

add_to_cart((int) $productId, $quantity === false ? 1 : (int) $quantity);
$cart = get_cart_totals($conn);

echo json_encode([
    'success' => true,
    'message' => 'Product added to cart.',
    'cart' => [
        'total_items' => (int) $cart['total_items'],
        'total_amount' => (int) $cart['total_amount'],
    ],
]);
