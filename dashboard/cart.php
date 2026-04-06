<?php
require_once __DIR__ . '/../config/store.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrfErrors = [];
  if (!enforce_csrf_or_errors($csrfErrors, 'cart_actions_form')) {
    set_flash('error', $csrfErrors[0] ?? 'Invalid cart request.');
    redirect_to('cart.php');
  }

  $action = $_POST['action'] ?? '';
  $productId = (int) ($_POST['product_id'] ?? 0);
  $userId = current_user_id();

  if ($action === 'update') {
    $quantity = (int) ($_POST['quantity'] ?? 1);
    $result = update_product_quantity_in_cart($conn, $productId, $quantity);
    set_flash($result['success'] ? 'success' : 'error', $result['message']);
  } elseif ($action === 'remove') {
    remove_cart_item($productId);
    set_flash('success', 'Item removed from cart.');
  } elseif ($action === 'checkout') {
    redirect_to('checkout.php');
  }

  redirect_to('cart.php');
}

$flash = get_flash();
$cart = get_cart_totals($conn);
$deliveryProfile = fetch_user_profile($conn, current_user_id()) ?? [];
$deliveryAddress = build_user_delivery_address($deliveryProfile);
$hasDeliveryProfile = has_complete_delivery_profile($deliveryProfile);
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
          <h3>Cart Subtotal</h3>
          <strong>Rs <?php echo e((string) $cart['subtotal_amount']); ?></strong>
        </div>
        <div class="stat-card">
          <h3>Estimated Delivery</h3>
          <strong><?php echo e($cart['estimated_delivery']); ?></strong>
        </div>
        <div class="stat-card">
          <h3>Total Payable</h3>
          <strong>Rs <?php echo e((string) $cart['total_amount']); ?></strong>
        </div>
      </div>

      <div class="content-card">
        <div class="category-result-info">
          <div>
            <h2>Delivery Details</h2>
            <p class="muted">Orders are placed using the address saved in your account.</p>
          </div>
          <a href="../account/account.php#address-form" class="btn-ghost"><i class="fa-solid fa-pen"></i> Update Address</a>
        </div>

        <?php if ($hasDeliveryProfile): ?>
          <div class="data-row">
            <span>Recipient</span>
            <strong><?php echo e($deliveryProfile['name'] ?? current_user_name()); ?></strong>
          </div>
          <div class="data-row">
            <span>Phone Number</span>
            <strong><?php echo e($deliveryProfile['phone_number'] ?? ''); ?></strong>
          </div>
          <div class="data-row address-row">
            <span>Address</span>
            <strong><?php echo e($deliveryAddress); ?></strong>
          </div>
        <?php else: ?>
          <p class="muted">Add your full profile and delivery address before checkout so the order can be delivered correctly.</p>
        <?php endif; ?>
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
                    <p class="muted">Category: <?php echo e($item['category_name'] ?? 'Uncategorized'); ?> | <?php echo e(product_unit_label($item)); ?></p>
                    <p class="muted">Rs <?php echo e((string) $item['price']); ?> each<?php if (product_savings_amount($item) > 0): ?> | MRP Rs <?php echo e((string) product_mrp_value($item)); ?><?php endif; ?></p>
                    <p class="cart-desc"><?php echo e(product_short_description($item['description'], 80)); ?></p>
                    <p class="muted"><?php echo e(product_stock_status($item)); ?> | ETA <?php echo e(product_delivery_window($item)); ?></p>
                    <?php if ((int) ($item['savings'] ?? 0) > 0): ?>
                      <p class="muted">You save Rs <?php echo e((string) $item['savings']); ?> on this line item.</p>
                    <?php endif; ?>
                    <p class="price">Subtotal: Rs <?php echo e((string) $item['subtotal']); ?></p>
                  </div>
                </div>

                <div class="cart-actions">
                  <form method="POST" class="cart-form">
                    <?php echo csrf_field('cart_actions_form'); ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" value="<?php echo e((string) $item['id']); ?>">
                    <input type="number" name="quantity" min="1" max="<?php echo e((string) ($item['stock_quantity'] ?? 1)); ?>" value="<?php echo e((string) $item['quantity']); ?>" class="cart-qty">
                    <button type="submit" class="btn-primary">Update</button>
                  </form>

                  <form method="POST">
                    <?php echo csrf_field('cart_actions_form'); ?>
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="product_id" value="<?php echo e((string) $item['id']); ?>">
                    <button type="submit" class="btn-ghost delete-btn">Remove</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <form method="POST" class="checkout-form">
            <?php echo csrf_field('cart_actions_form'); ?>
            <input type="hidden" name="action" value="checkout">
            <button type="submit" class="btn-primary" <?php echo empty($cart['items']) ? 'disabled' : ''; ?>>Proceed to Checkout</button>
          </form>

          <div class="content-card" style="margin-top: 20px;">
            <h2>Bill Summary</h2>
            <div class="data-row">
              <span>MRP Total</span>
              <strong>Rs <?php echo e((string) $cart['mrp_total']); ?></strong>
            </div>
            <div class="data-row">
              <span>Selling Price Total</span>
              <strong>Rs <?php echo e((string) $cart['subtotal_amount']); ?></strong>
            </div>
            <div class="data-row">
              <span>Total Savings</span>
              <strong>Rs <?php echo e((string) $cart['discount_amount']); ?></strong>
            </div>
            <div class="data-row">
              <span>Delivery Fee</span>
              <strong>Rs <?php echo e((string) $cart['delivery_fee']); ?></strong>
            </div>
            <div class="data-row">
              <span>Handling Fee</span>
              <strong>Rs <?php echo e((string) $cart['handling_fee']); ?></strong>
            </div>
            <div class="data-row">
              <span>Payment Method</span>
              <strong><?php echo e($cart['payment_method']); ?></strong>
            </div>
            <div class="data-row">
              <span>Estimated Delivery</span>
              <strong><?php echo e($cart['estimated_delivery']); ?></strong>
            </div>
            <div class="data-row">
              <span>Total Payable</span>
              <strong>Rs <?php echo e((string) $cart['total_amount']); ?></strong>
            </div>
            <?php if ((int) $cart['amount_for_free_delivery'] > 0): ?>
              <p class="muted" style="margin-top: 12px;">Add items worth Rs <?php echo e((string) $cart['amount_for_free_delivery']); ?> more to unlock free delivery.</p>
            <?php else: ?>
              <p class="muted" style="margin-top: 12px;">You have unlocked free delivery on this order.</p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
