<?php
require_once __DIR__ . '/../config/store.php';

require_admin();

if (!request_is_post() || !validate_csrf_token(post_csrf_token(), 'admin_delete_product_form')) {
  set_flash('error', 'Invalid delete request.');
  redirect_to('dashboard.php?section=products');
}

$productId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$product = fetch_product_by_id($conn, $productId);

if (!$product) {
  set_flash('error', 'Product not found.');
  redirect_to('dashboard.php?section=products');
}

$checkStmt = $conn->prepare("SELECT id FROM order_items WHERE product_id = ? LIMIT 1");
$checkStmt->bind_param('i', $productId);
$checkStmt->execute();
$hasOrders = $checkStmt->get_result()->num_rows > 0;
$checkStmt->close();

if ($hasOrders) {
  set_flash('error', 'This product is already used in an order, so it cannot be deleted.');
  redirect_to('dashboard.php?section=products');
}

if (delete_product($conn, $productId)) {
  delete_product_image($product['image']);
  remove_cart_item($productId);
  set_flash('success', 'Product deleted successfully.');
} else {
  set_flash('error', 'Unable to delete product.');
}

redirect_to('dashboard.php?section=products');
?>
