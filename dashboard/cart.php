<?php
require_once __DIR__ . '/../config/store.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $productId = (int) ($_POST['product_id'] ?? 0);

  if ($action === 'update') {
    $quantity = (int) ($_POST['quantity'] ?? 1);
    update_cart_item($productId, $quantity);
    set_flash('success', 'Cart updated successfully.');
  } elseif ($action === 'remove') {
    remove_cart_item($productId);
    set_flash('success', 'Item removed from cart.');
  } elseif ($action === 'checkout') {
    if (place_order_from_cart($conn, (int) $_SESSION['user_id'])) {
      set_flash('success', 'Order placed successfully.');
    } else {
      set_flash('error', 'Your cart is empty or order could not be placed.');
    }
  }

  redirect_to('cart.php');
}

$flash = get_flash();
$cart = get_cart_totals($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Cart</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="page">
  <div class="page-shell">
    <aside class="glass-panel side-panel">
      <div class="brand">
        <h2>Harvest Fresh</h2>
      </div>

      <ul class="nav-list">
        <li><a href="user_dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</a></li>
        <li><a href="../account/account.php"><i class="fa-solid fa-user"></i> Account</a></li>
        <li><a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
      </ul>

      <div class="profile-card bottom-profile">
        <div class="profile-avatar"><?php echo e(get_avatar_letter(current_user_name())); ?></div>
        <h3><?php echo e(current_user_name()); ?></h3>
      </div>
    </aside>

    <main class="glass-panel main-panel">
      <div class="panel-header">
        <div class="panel-title">
          <h1>My Cart</h1>
          <p class="muted">Review items, update quantity, and place your order.</p>
        </div>
        <a href="user_dashboard.php?section=shop" class="btn-ghost"><i class="fa-solid fa-arrow-left"></i> Continue Shopping</a>
      </div>

      <?php if ($flash): ?>
        <div class="content-card message-card <?php echo $flash['type'] === 'success' ? 'success-card' : 'error-card'; ?>">
          <p><?php echo e($flash['message']); ?></p>
        </div>
      <?php endif; ?>

      <div class="stats-grid">
        <div class="stat-card">
          <h3>Total Items</h3>
          <strong><?php echo e((string) $cart['total_items']); ?></strong>
        </div>
        <div class="stat-card">
          <h3>Total Amount</h3>
          <strong>Rs <?php echo e((string) $cart['total_amount']); ?></strong>
        </div>
      </div>

      <div class="content-card">
        <h2>Cart Items</h2>

        <?php if (empty($cart['items'])): ?>
          <p class="muted">Your cart is empty. Add products from the shop section.</p>
        <?php else: ?>
          <div class="cart-list">
            <?php foreach ($cart['items'] as $item): ?>
              <div class="cart-item">
                <div class="cart-item-left">
                  <div class="cart-thumb">
                    <img src="<?php echo e(product_image_src($item['image'])); ?>" alt="<?php echo e($item['name']); ?>">
                  </div>
                  <div class="cart-item-details">
                    <h3><?php echo e($item['name']); ?></h3>
                    <p class="muted">Category: <?php echo e($item['category_name'] ?? 'Uncategorized'); ?></p>
                    <p class="muted">Rs <?php echo e((string) $item['price']); ?> each</p>
                    <p class="cart-desc"><?php echo e(product_short_description($item['description'], 80)); ?></p>
                    <p class="price">Subtotal: Rs <?php echo e((string) $item['subtotal']); ?></p>
                  </div>
                </div>

                <div class="cart-actions">
                  <form method="POST" class="cart-form">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" value="<?php echo e((string) $item['id']); ?>">
                    <input type="number" name="quantity" min="1" value="<?php echo e((string) $item['quantity']); ?>" class="cart-qty">
                    <button type="submit" class="btn-primary">Update</button>
                  </form>

                  <form method="POST">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="product_id" value="<?php echo e((string) $item['id']); ?>">
                    <button type="submit" class="btn-ghost delete-btn">Remove</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <form method="POST" class="checkout-form">
            <input type="hidden" name="action" value="checkout">
            <button type="submit" class="btn-primary">Place Order</button>
          </form>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
