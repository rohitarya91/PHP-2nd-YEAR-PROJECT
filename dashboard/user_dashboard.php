<?php
require_once __DIR__ . '/../config/store.php';

require_login();

if (is_admin()) {
  redirect_to('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $productId = (int) ($_POST['product_id'] ?? 0);
  $redirectSection = $_POST['redirect_section'] ?? 'shop';

  if ($action === 'add_to_cart' && $productId > 0) {
    if (!validate_csrf_token(post_csrf_token(), 'cart_api_form')) {
      set_flash('error', 'Session token expired. Please refresh and try again.');
    } else {
      $result = add_product_to_cart($conn, $productId, 1);
      set_flash($result['success'] ? 'success' : 'error', $result['message']);
    }
    redirect_to('user_dashboard.php?section=' . urlencode($redirectSection));
  }
}

$flash = get_flash();
$section = $_GET['section'] ?? 'home';
$search = trim($_GET['search'] ?? '');
$categories = fetch_all_categories($conn);
$products = fetch_all_products($conn, $search);
$homeProducts = fetch_all_products($conn);
$orders = fetch_user_orders($conn, (int) $_SESSION['user_id']);
$cart = get_cart_totals($conn);
$profile = fetch_user_profile($conn, current_user_id()) ?? [];
$profileAddress = trim(implode(', ', array_filter([
  $profile['address_line1'] ?? '',
  $profile['address_line2'] ?? '',
  $profile['city'] ?? '',
  $profile['state'] ?? '',
  $profile['postal_code'] ?? '',
])));
$categoryIcons = [
  'Vegetables' => 'fa-carrot',
  'Fruits' => 'fa-apple-whole',
  'Dairy' => 'fa-bottle-water',
  'Bakery' => 'fa-bread-slice',
  'Daily Needs' => 'fa-bag-shopping',
];

function render_home_product_card(array $product): void
{
  $productId = e((string) $product['id']);
  $csrf = e(csrf_token('cart_api_form'));
  $name = e($product['name']);
  $image = e(product_image_src($product['image']));
  $price = e((string) $product['price']);
  $mrp = e((string) product_mrp_value($product));
  $unitLabel = e(product_unit_label($product));
  $description = e(product_short_description($product['description'], 90));
  $categoryName = e($product['category_name'] ?? 'Uncategorized');
  $badgeLabel = e(product_badge_label($product));
  $stockStatus = e(product_stock_status($product));
  $deliveryLabel = e(product_delivery_window($product));
  $isInStock = product_is_in_stock($product);
  $savings = product_savings_amount($product);
  $buttonLabel = $isInStock ? 'Add to Cart' : 'Out of Stock';
  $buttonState = $isInStock ? '' : 'disabled';
  $badgeMarkup = $badgeLabel !== '' ? '<p class="muted">' . $badgeLabel . '</p>' : '';
  $savingsMarkup = $savings > 0 ? '<p class="muted">Save Rs ' . e((string) $savings) . '</p>' : '';

  echo <<<HTML
    <div class="product-card home-product-card shop-product-card wide-product-card">
      <div class="shop-product-image home-product-image">
        <img src="{$image}" alt="{$name}">
      </div>
      <div class="home-product-body">
        {$badgeMarkup}
        <p class="product-category-label">{$categoryName}</p>
        <h4>{$name}</h4>
        <p class="price">Rs {$price} <span class="muted">MRP Rs {$mrp}</span></p>
        <p class="muted">{$unitLabel} | {$stockStatus}</p>
        <p class="muted">Fast delivery in {$deliveryLabel}</p>
        <p class="product-desc">{$description}</p>
        {$savingsMarkup}
      </div>
      <form method="POST" class="home-cart-form">
        <input type="hidden" name="_csrf" value="{$csrf}">
        <input type="hidden" name="action" value="add_to_cart">
        <input type="hidden" name="product_id" value="{$productId}">
        <input type="hidden" name="redirect_section" value="home">
        <button type="submit" class="btn-primary full-btn" {$buttonState}>{$buttonLabel}</button>
      </form>
    </div>
  HTML;
}

function render_shop_product_card(array $product): void
{
  $productId = e((string) $product['id']);
  $csrf = e(csrf_token('cart_api_form'));
  $name = e($product['name']);
  $image = e(product_image_src($product['image']));
  $price = e((string) $product['price']);
  $mrp = e((string) product_mrp_value($product));
  $unitLabel = e(product_unit_label($product));
  $description = e(product_short_description($product['description'], 100));
  $categoryName = e($product['category_name'] ?? 'Uncategorized');
  $badgeLabel = e(product_badge_label($product));
  $stockStatus = e(product_stock_status($product));
  $deliveryLabel = e(product_delivery_window($product));
  $isInStock = product_is_in_stock($product);
  $savings = product_savings_amount($product);
  $buttonLabel = $isInStock ? 'Add to Cart' : 'Out of Stock';
  $buttonState = $isInStock ? '' : 'disabled';
  $badgeMarkup = $badgeLabel !== '' ? '<p class="muted">' . $badgeLabel . '</p>' : '';
  $savingsMarkup = $savings > 0 ? '<p class="muted">Save Rs ' . e((string) $savings) . '</p>' : '';

  echo <<<HTML
    <div class="product-card shop-product-card wide-product-card">
      <div class="shop-product-image">
        <img src="{$image}" alt="{$name}">
      </div>
      <div class="shop-product-body">
        {$badgeMarkup}
        <p class="product-category-label">{$categoryName}</p>
        <h4>{$name}</h4>
        <p class="price">Rs {$price} <span class="muted">MRP Rs {$mrp}</span></p>
        <p class="muted">{$unitLabel} | {$stockStatus}</p>
        <p class="muted">Fast delivery in {$deliveryLabel}</p>
        <p class="product-desc">{$description}</p>
        {$savingsMarkup}
      </div>
      <form method="POST" class="shop-cart-form">
        <input type="hidden" name="_csrf" value="{$csrf}">
        <input type="hidden" name="action" value="add_to_cart">
        <input type="hidden" name="product_id" value="{$productId}">
        <input type="hidden" name="redirect_section" value="shop">
        <button type="submit" class="btn-primary full-btn" {$buttonState}>{$buttonLabel}</button>
      </form>
    </div>
  HTML;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Harvest Fresh</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
  <style>
    #navbar {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 15px 25px;
      margin: 15px;
      background: rgba(255,255,255,0.08);
      backdrop-filter: blur(12px);
      border-radius: 15px;
      position: sticky;
      top: 10px;
      z-index: 1000;
    }

    #logo {
      font-size: 22px;
      font-weight: 600;
      white-space: nowrap;
    }

    #nav-links {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-left: auto;
      flex: 1;
      min-width: 0;
      justify-content: flex-end;
    }

    .menu-toggle {
      flex-shrink: 0;
    }

    .navbar-search {
      display: flex;
      align-items: center;
      margin: 0;
      width: min(100%, 360px);
      min-width: 0;
    }

    .navbar-search .search-bar {
      width: 100%;
    }

    .cart-link {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      white-space: nowrap;
    }

    .section-page {
      display: none;
    }

    .section-page.active {
      display: block;
    }

    .category-chip {
      width: 100%;
      border: none;
      color: inherit;
      cursor: pointer;
    }

    .category-chip.is-active {
      transform: translateY(-6px);
      box-shadow: 0 14px 28px rgba(34,197,94,0.22);
      border-color: rgba(34,197,94,0.4);
      background: rgba(34,197,94,0.14);
    }

    .category-chip.is-active span {
      background: rgba(34,197,94,0.35);
    }

    .category-result-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      margin-bottom: 18px;
      flex-wrap: wrap;
    }

    .category-result-status {
      color: rgba(255,255,255,0.72);
      font-size: 14px;
    }

    .product-category-label {
      display: inline-block;
      margin-bottom: 8px;
      padding: 5px 10px;
      border-radius: 999px;
      background: rgba(34,197,94,0.16);
      color: #d7ffe5;
      font-size: 12px;
      border: 1px solid rgba(34,197,94,0.18);
    }

    .category-empty {
      grid-column: 1 / -1;
      padding: 22px;
      border-radius: 16px;
      text-align: center;
      background: rgba(255,255,255,0.05);
      border: 1px dashed rgba(255,255,255,0.12);
      color: rgba(255,255,255,0.72);
    }

    .shop-summary-card {
      margin-bottom: 20px;
    }

    .summary-pill {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 10px 14px;
      border-radius: 999px;
      background: rgba(34,197,94,0.18);
      color: #d7ffe5;
      border: 1px solid rgba(34,197,94,0.24);
      font-size: 13px;
      white-space: nowrap;
    }

    .profile-grid {
      display: grid;
      grid-template-columns: minmax(0, 1.5fr) minmax(280px, 0.9fr);
      gap: 20px;
      align-items: start;
    }

    .profile-card-block {
      height: 100%;
    }

    .profile-summary {
      display: grid;
      gap: 6px;
    }

    .profile-summary .address-row {
      align-items: flex-start;
    }

    .profile-summary .address-row strong {
      max-width: 360px;
      text-align: right;
      line-height: 1.5;
    }

    .profile-actions {
      display: grid;
      gap: 14px;
    }

    .profile-action-card {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      padding: 18px;
      border-radius: 18px;
      text-decoration: none;
      color: inherit;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    }

    .profile-action-card:hover {
      transform: translateY(-2px);
      border-color: rgba(34,197,94,0.35);
      box-shadow: 0 16px 28px rgba(15,23,42,0.22);
    }

    .profile-action-icon {
      width: 42px;
      height: 42px;
      border-radius: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(34,197,94,0.18);
      color: #d7ffe5;
      flex-shrink: 0;
    }

    .profile-action-copy h3 {
      margin: 0 0 6px;
      font-size: 16px;
    }

    .profile-action-copy p {
      margin: 0;
      color: rgba(255,255,255,0.72);
      line-height: 1.5;
      font-size: 14px;
    }

    .profile-note {
      margin: 16px 0 0;
      color: rgba(255,255,255,0.7);
      font-size: 13px;
      line-height: 1.5;
    }

    @media (max-width: 768px) {
      #navbar {
        margin: 12px 12px 0;
        padding: 14px 16px;
        gap: 10px;
      }

      .menu-toggle {
        display: inline-flex;
        margin-left: 0;
        position: static;
        z-index: auto;
      }

      #nav-links {
        gap: 8px;
      }

      #logo {
        font-size: 18px;
      }

      .navbar-search {
        width: auto;
        flex: 1;
      }

      .search-bar {
        min-width: 0;
        padding: 10px 12px;
        font-size: 14px;
      }

      .cart-link {
        text-align: center;
        padding: 10px 12px;
        font-size: 0;
        line-height: 1;
      }

      .cart-link i,
      .cart-link .cart-count {
        font-size: 14px;
      }

      .cart-link .cart-link-text {
        display: none;
      }

      .profile-grid {
        grid-template-columns: 1fr;
      }

      .profile-summary .data-row,
      .profile-summary .address-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
      }

      .profile-summary .address-row strong {
        max-width: none;
        text-align: left;
      }
    }
  </style>
</head>

<body class="page">
  <div class="page-shell">
    <aside id="user-side-panel" class="glass-panel side-panel">
      <button type="button" class="side-panel-close" onclick="closeUserMenu()" aria-label="Close menu">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <div class="brand">
        <h2>Harvest Fresh</h2>
      </div>

      <ul class="nav-list">
        <li><a href="#" onclick="showSection('home', event)" class="<?php echo $section === 'home' ? 'active' : ''; ?>"><i class="fa-solid fa-home"></i> Home</a></li>
        <li><a href="#" onclick="showSection('shop', event)" class="<?php echo $section === 'shop' ? 'active' : ''; ?>"><i class="fa-solid fa-store"></i> Shop</a></li>
        <li><a href="#" onclick="showSection('orders', event)" class="<?php echo $section === 'orders' ? 'active' : ''; ?>"><i class="fa-solid fa-bag-shopping"></i> My Orders</a></li>
        <li><a href="#" onclick="showSection('profile', event)" class="<?php echo $section === 'profile' ? 'active' : ''; ?>"><i class="fa-solid fa-user"></i> Account</a></li>
      </ul>

      <div class="profile-card bottom-profile">
        <div class="profile-avatar"><?php echo e(get_avatar_letter(current_user_name())); ?></div>
        <h3><?php echo e(current_user_name()); ?></h3>
        <a href="../auth/logout.php" class="logout-btn">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
      </div>
    </aside>
    <div class="menu-overlay" onclick="closeUserMenu()" aria-hidden="true"></div>

    <main class="glass-panel main-panel">
      <nav id="navbar">
        <button
          type="button"
          class="menu-toggle"
          onclick="toggleUserMenu()"
          aria-label="Toggle user menu"
          aria-controls="user-side-panel"
          aria-expanded="false">
          <i class="fas fa-bars"></i>
        </button>

        <div id="logo">Harvest Fresh</div>

        <div id="nav-links">
          <form method="GET" class="panel-actions navbar-search">
            <input type="hidden" name="section" value="shop">
            <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search products..." class="search-bar">
          </form>
          <a href="cart.php" class="btn-primary cart-link" aria-label="View cart">
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="cart-link-text">View Cart</span>
            <span class="cart-count">(<?php echo e((string) $cart['total_items']); ?>)</span>
          </a>
        </div>
      </nav>

      <div class="panel-header">
        <div class="panel-title">
          <h1>Welcome, <?php echo e(current_user_name()); ?></h1>
          <p class="muted">Order essentials with real stock visibility, quick delivery windows, and live cart pricing.</p>
        </div>
      </div>

      <?php if ($flash): ?>
        <div class="content-card message-card <?php echo $flash['type'] === 'success' ? 'success-card' : 'error-card'; ?>">
          <p><?php echo e($flash['message']); ?></p>
        </div>
      <?php endif; ?>

      <section id="home-section" class="section-page <?php echo $section === 'home' ? 'active' : ''; ?>">
        <div class="home-hero">
          <div class="home-hero-overlay"></div>
          <div class="home-hero-content">
            <span class="hero-badge">Quick Commerce Ready</span>
            <h2>Essentials Delivered in <?php echo e($cart['estimated_delivery']); ?></h2>
            <p>Browse stocked vegetables, fruits, dairy, bakery, and daily needs with pack sizes, savings, and delivery windows shown before checkout.</p>
            <div class="hero-actions">
              <button type="button" class="btn-primary" onclick="openSection('shop')">Shop Now</button>
              <a href="cart.php" class="btn-ghost">View Cart</a>
            </div>
          </div>
        </div>

        <div class="content-card home-section-card">
          <div class="section-head">
            <div>
              <h2>Shop by Category</h2>
              <p class="muted">Click a category to explore products from that section.</p>
            </div>
          </div>

          <div class="category-strip">
            <button type="button" class="category-chip is-active" data-category-id="" data-category-name="All Products">
              <span><i class="fa-solid fa-store"></i></span>
              <h4>All Products</h4>
            </button>
            <?php foreach ($categories as $category): ?>
              <button
                type="button"
                class="category-chip"
                data-category-id="<?php echo e((string) $category['id']); ?>"
                data-category-name="<?php echo e($category['name']); ?>">
                <span><i class="fa-solid <?php echo e($categoryIcons[$category['name']] ?? 'fa-bag-shopping'); ?>"></i></span>
                <h4><?php echo e($category['name']); ?></h4>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="content-card home-section-card">
          <div class="category-result-info">
            <div>
              <h2 id="home-category-title">All Products</h2>
              <p class="muted" id="home-category-copy">Products from the selected category appear here.</p>
            </div>
            <button type="button" class="btn-ghost" onclick="openSection('shop')">Browse All</button>
          </div>
          <p class="category-result-status" id="home-category-status">Showing all products</p>

          <div class="home-product-grid" id="home-category-products">
            <?php if (empty($homeProducts)): ?>
              <div class="category-empty">
                <h4>No products available</h4>
                <p>Add products from the admin panel to show them here.</p>
              </div>
            <?php endif; ?>

            <?php foreach ($homeProducts as $product): ?>
              <?php render_home_product_card($product); ?>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section id="shop-section" class="section-page <?php echo $section === 'shop' ? 'active' : ''; ?>">
        <div class="content-card shop-summary-card">
          <div class="category-result-info">
            <div>
              <h2>Quick Cart Summary</h2>
              <p class="muted">Track your live cart value, savings, and next delivery estimate.</p>
            </div>
            <div class="summary-pill"><?php echo e((string) $cart['total_items']); ?> Items | Rs <?php echo e((string) $cart['total_amount']); ?></div>
          </div>
        </div>

        <div class="content-card home-section-card">
          <div class="section-head">
            <div>
              <h2>Shop by Category</h2>
              <p class="muted">Select a category to browse filtered products.</p>
            </div>
            <button type="button" class="btn-ghost" onclick="openSection('orders')">My Orders</button>
          </div>

          <div class="category-strip">
            <button type="button" class="category-chip shop-category-chip is-active" data-category-id="" data-category-name="All Products">
              <span><i class="fa-solid fa-store"></i></span>
              <h4>All Products</h4>
            </button>
            <?php foreach ($categories as $category): ?>
              <button
                type="button"
                class="category-chip shop-category-chip"
                data-category-id="<?php echo e((string) $category['id']); ?>"
                data-category-name="<?php echo e($category['name']); ?>">
                <span><i class="fa-solid <?php echo e($categoryIcons[$category['name']] ?? 'fa-bag-shopping'); ?>"></i></span>
                <h4><?php echo e($category['name']); ?></h4>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="content-card">
          <div class="category-result-info">
            <div>
              <h2 id="shop-category-title">All Products</h2>
              <p class="muted" id="shop-category-copy">Products from the selected shop category appear here.</p>
            </div>
            <button type="button" class="btn-ghost" onclick="openSection('orders')">View Orders</button>
          </div>
          <p class="category-result-status" id="shop-category-status"><?php echo $search !== '' ? e('Showing ' . count($products) . ' results for "' . $search . '".') : 'Showing all products'; ?></p>

          <div class="product-grid shop-grid-wide" id="shop-category-products">
            <?php if (empty($products)): ?>
              <div class="category-empty">
                <h4>No products found</h4>
                <p>Try another search term.</p>
              </div>
            <?php endif; ?>

            <?php foreach ($products as $product): ?>
              <?php render_shop_product_card($product); ?>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section id="orders-section" class="section-page <?php echo $section === 'orders' ? 'active' : ''; ?>">
        <div class="content-card">
          <h2>Recent Orders</h2>

          <?php if (empty($orders)): ?>
            <p class="muted">You have not placed any orders yet.</p>
          <?php else: ?>
            <?php foreach ($orders as $order): ?>
              <div class="order-item">
                <p>Order #<?php echo e((string) $order['id']); ?> - <?php echo e($order['status']); ?> - Rs <?php echo e((string) $order['total_amount']); ?></p>
                <p class="muted">
                  Subtotal Rs <?php echo e((string) $order['subtotal_amount']); ?> |
                  Savings Rs <?php echo e((string) $order['discount_amount']); ?> |
                  Delivery Rs <?php echo e((string) $order['delivery_fee']); ?> |
                  ETA <?php echo e($order['delivery_eta_label'] ?? 'Pending'); ?>
                </p>
                <p class="muted">
                  Payment: <?php echo e($order['payment_method'] ?? STORE_DEFAULT_PAYMENT_METHOD); ?> |
                  Status: <?php echo e($order['payment_status'] ?? 'Pending'); ?>
                  <?php if (!empty($order['payment_reference'])): ?> | Ref: <?php echo e($order['payment_reference']); ?><?php endif; ?>
                </p>
                <?php if (!empty($order['items_summary'])): ?>
                  <p class="muted"><?php echo e($order['items_summary']); ?></p>
                <?php endif; ?>
                <?php if (!empty($order['shipping_name']) || !empty($order['shipping_phone']) || !empty($order['shipping_address'])): ?>
                  <p class="muted">
                    Deliver to:
                    <?php echo e(trim(implode(' | ', array_filter([
                      $order['shipping_name'] ?? '',
                      $order['shipping_phone'] ?? '',
                    ])))); ?>
                  </p>
                  <p class="muted"><?php echo e($order['shipping_address'] ?? ''); ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>

      <section id="profile-section" class="section-page <?php echo $section === 'profile' ? 'active' : ''; ?>">
        <div class="profile-grid">
          <div class="content-card profile-card-block">
            <div class="section-head">
              <div>
                <h2>My Account</h2>
                <p class="muted">Review your basic profile and saved delivery details here.</p>
              </div>
            </div>

            <div class="profile-summary">
              <div class="data-row">
                <span>Full Name</span>
                <strong><?php echo e($profile['name'] ?? current_user_name()); ?></strong>
              </div>

              <div class="data-row">
                <span>Email ID</span>
                <strong><?php echo e($profile['email'] ?? current_user_email()); ?></strong>
              </div>

              <div class="data-row">
                <span>Phone Number</span>
                <strong><?php echo e($profile['phone_number'] ?? 'Not added yet'); ?></strong>
              </div>

              <div class="data-row address-row">
                <span>Delivery Address</span>
                <strong><?php echo e($profileAddress !== '' ? $profileAddress : 'No delivery address saved yet.'); ?></strong>
              </div>
            </div>
          </div>

          <div class="content-card profile-card-block">
            <div class="section-copy">
              <h2>Quick Actions</h2>
              <p class="muted">Use these shortcuts to edit your profile or update your address.</p>
            </div>

            <div class="profile-actions">
              <a class="profile-action-card" href="../account/account.php#profile-form">
                <span class="profile-action-icon"><i class="fa-solid fa-user-pen"></i></span>
                <span class="profile-action-copy">
                  <h3>Edit Profile</h3>
                  <p>Update basic details such as your name and phone number.</p>
                </span>
              </a>

              <a class="profile-action-card" href="../account/account.php#address-form">
                <span class="profile-action-icon"><i class="fa-solid fa-location-dot"></i></span>
                <span class="profile-action-copy">
                  <h3>Add Address</h3>
                  <p>Add a full delivery address or update the one you already saved.</p>
                </span>
              </a>
            </div>

            <p class="profile-note">These details will be used for future orders and deliveries.</p>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>

</html>
<script>
  const cartApiEndpoint = '../cart_api.php';
  const currentShopSearch = <?php echo json_encode($search); ?>;
  const userSidebar = document.getElementById('user-side-panel');
  const userMenuToggle = document.querySelector('.menu-toggle');
  const userMenuOverlay = document.querySelector('.menu-overlay');
  const cartCountLabels = document.querySelectorAll('.cart-count');
  const homeCategoryButtons = document.querySelectorAll('.category-chip:not(.shop-category-chip)');
  const shopCategoryButtons = document.querySelectorAll('.shop-category-chip');
  const homeCategoryProducts = document.getElementById('home-category-products');
  const shopCategoryProducts = document.getElementById('shop-category-products');
  const homeCategoryTitle = document.getElementById('home-category-title');
  const homeCategoryCopy = document.getElementById('home-category-copy');
  const homeCategoryStatus = document.getElementById('home-category-status');
  const shopCategoryTitle = document.getElementById('shop-category-title');
  const shopCategoryCopy = document.getElementById('shop-category-copy');
  const shopCategoryStatus = document.getElementById('shop-category-status');
  let dashboardToastTimeout;

  function activateSection(section) {
    document.querySelectorAll('.section-page').forEach(sec => {
      sec.classList.remove('active');
    });

    document.getElementById(section + '-section').classList.add('active');

    document.querySelectorAll('.nav-list a').forEach(link => {
      link.classList.remove('active');
    });

    const activeLink = document.querySelector(`.nav-list a[onclick*="'${section}'"]`);
    if (activeLink) {
      activeLink.classList.add('active');
    }
  }

  function showSection(section, event) {
    event.preventDefault();
    activateSection(section);
    closeUserMenu();

    if (event.currentTarget) {
      event.currentTarget.classList.add('active');
    }
  }

  function openSection(section) {
    activateSection(section);
    closeUserMenu();
  }

  function setUserMenuState(isOpen) {
    if (!userSidebar || !userMenuToggle || !userMenuOverlay) {
      return;
    }

    userSidebar.classList.toggle('active', isOpen);
    userMenuOverlay.classList.toggle('active', isOpen);
    document.body.classList.toggle('menu-open', isOpen);
    userMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  }

  function toggleUserMenu() {
    if (window.innerWidth > 768) {
      return;
    }

    setUserMenuState(!userSidebar.classList.contains('active'));
  }

  function closeUserMenu() {
    setUserMenuState(false);
  }

  function updateCartCount(totalItems) {
    cartCountLabels.forEach(label => {
      label.textContent = `(${totalItems})`;
    });
  }

  function getDashboardToast() {
    let toast = document.querySelector('.dashboard-toast');

    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'dashboard-toast';
      toast.setAttribute('aria-live', 'polite');
      document.body.appendChild(toast);
    }

    return toast;
  }

  function showDashboardToast(message, type = 'success') {
    const toast = getDashboardToast();
    toast.textContent = message;
    toast.classList.toggle('error', type === 'error');
    toast.classList.add('show');

    window.clearTimeout(dashboardToastTimeout);
    dashboardToastTimeout = window.setTimeout(() => {
      toast.classList.remove('show');
    }, 2200);
  }

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function(char) {
      const entities = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      };
      return entities[char];
    });
  }

  function truncateText(value, limit) {
    const text = String(value || '').trim();
    if (!text) {
      return 'Fresh and quality grocery item ready for quick delivery.';
    }
    return text.length <= limit ? text : text.slice(0, limit - 3) + '...';
  }

  function buildProductsEndpoint(categoryId, searchTerm = '') {
    const params = new URLSearchParams();

    if (categoryId) {
      params.set('category_id', categoryId);
    }

    if (searchTerm) {
      params.set('search', searchTerm);
    }

    const query = params.toString();
    return query ? `../catalog_api.php?${query}` : '../catalog_api.php';
  }

  function buildProductCardMarkup(product, sectionName) {
    const csrfToken = <?php echo json_encode(csrf_token('cart_api_form')); ?>;
    const mrp = Number(product.mrp || product.price || 0);
    const price = Number(product.price || 0);
    const savings = Math.max(0, mrp - price);
    const unitLabel = product.unit_label || '1 pack';
    const stockQuantity = Number(product.stock_quantity || 0);
    const stockStatus = stockQuantity <= 0 ? 'Out of stock' : (stockQuantity <= 8 ? `Only ${stockQuantity} left` : 'In stock');
    const deliveryLabel = `${Math.max(10, Number(product.delivery_minutes || 20))}-${Math.max(10, Number(product.delivery_minutes || 20)) + 10} mins`;
    const badgeLabel = String(product.badge_text || '').trim() || (Number(product.is_featured || 0) === 1 ? 'Featured' : '');
    const badgeMarkup = badgeLabel ? `<p class="muted">${escapeHtml(badgeLabel)}</p>` : '';
    const savingsMarkup = savings > 0 ? `<p class="muted">Save Rs ${escapeHtml(savings)}</p>` : '';
    const buttonDisabled = stockQuantity <= 0 ? 'disabled' : '';
    const buttonLabel = stockQuantity <= 0 ? 'Out of Stock' : 'Add to Cart';

    if (sectionName === 'shop') {
      return `
        <div class="product-card shop-product-card wide-product-card">
          <div class="shop-product-image">
            <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}">
          </div>
          <div class="shop-product-body">
            ${badgeMarkup}
            <p class="product-category-label">${escapeHtml(product.category_name || 'Uncategorized')}</p>
            <h4>${escapeHtml(product.name)}</h4>
            <p class="price">Rs ${escapeHtml(price)} <span class="muted">MRP Rs ${escapeHtml(mrp)}</span></p>
            <p class="muted">${escapeHtml(unitLabel)} | ${escapeHtml(stockStatus)}</p>
            <p class="muted">Fast delivery in ${escapeHtml(deliveryLabel)}</p>
            <p class="product-desc">${escapeHtml(truncateText(product.description, 100))}</p>
            ${savingsMarkup}
          </div>
          <form method="POST" class="shop-cart-form">
            <input type="hidden" name="_csrf" value="${escapeHtml(csrfToken)}">
            <input type="hidden" name="action" value="add_to_cart">
            <input type="hidden" name="product_id" value="${escapeHtml(product.id)}">
            <input type="hidden" name="redirect_section" value="shop">
            <button type="submit" class="btn-primary full-btn" ${buttonDisabled}>${buttonLabel}</button>
          </form>
        </div>
      `;
    }

    return `
      <div class="product-card home-product-card shop-product-card wide-product-card">
        <div class="shop-product-image home-product-image">
          <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}">
        </div>
        <div class="home-product-body">
          ${badgeMarkup}
          <p class="product-category-label">${escapeHtml(product.category_name || 'Uncategorized')}</p>
          <h4>${escapeHtml(product.name)}</h4>
          <p class="price">Rs ${escapeHtml(price)} <span class="muted">MRP Rs ${escapeHtml(mrp)}</span></p>
          <p class="muted">${escapeHtml(unitLabel)} | ${escapeHtml(stockStatus)}</p>
          <p class="muted">Fast delivery in ${escapeHtml(deliveryLabel)}</p>
          <p class="product-desc">${escapeHtml(truncateText(product.description, 90))}</p>
          ${savingsMarkup}
        </div>
        <form method="POST" class="home-cart-form">
          <input type="hidden" name="_csrf" value="${escapeHtml(csrfToken)}">
          <input type="hidden" name="action" value="add_to_cart">
          <input type="hidden" name="product_id" value="${escapeHtml(product.id)}">
          <input type="hidden" name="redirect_section" value="home">
          <button type="submit" class="btn-primary full-btn" ${buttonDisabled}>${buttonLabel}</button>
        </form>
      </div>
    `;
  }

  function renderHomeProducts(products) {
    if (!Array.isArray(products) || products.length === 0) {
      homeCategoryProducts.innerHTML = `
        <div class="category-empty">
          <h4>No products found</h4>
          <p>No products are available in this category right now.</p>
        </div>
      `;
      return;
    }

    homeCategoryProducts.innerHTML = products.map(product => buildProductCardMarkup(product, 'home')).join('');
  }

  function renderShopProducts(products) {
    if (!Array.isArray(products) || products.length === 0) {
      shopCategoryProducts.innerHTML = `
        <div class="category-empty">
          <h4>No products found</h4>
          <p>No products are available for this filter right now.</p>
        </div>
      `;
      return;
    }

    shopCategoryProducts.innerHTML = products.map(product => buildProductCardMarkup(product, 'shop')).join('');
  }

  async function loadHomeCategoryProducts(categoryId, categoryName) {
    const endpoint = buildProductsEndpoint(categoryId);
    homeCategoryTitle.textContent = categoryName;
    homeCategoryCopy.textContent = categoryId
      ? `All products from the ${categoryName} category are shown below.`
      : 'All available store products are shown below.';
    homeCategoryStatus.textContent = categoryId
      ? `Loading ${categoryName} products...`
      : 'Loading all products...';
    homeCategoryProducts.innerHTML = '<div class="category-empty">Loading products...</div>';

    try {
      const response = await fetch(endpoint, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      const data = await response.json();

      if (!response.ok || !data.success) {
        throw new Error(data.message || 'Unable to load products.');
      }

      homeCategoryStatus.textContent = categoryId
        ? `${data.products.length} products found in the ${categoryName} category.`
        : `${data.products.length} products available.`;
      renderHomeProducts(data.products);
    } catch (error) {
      homeCategoryStatus.textContent = 'Unable to load products.';
      homeCategoryProducts.innerHTML = `<div class="category-empty">${escapeHtml(error.message)}</div>`;
    }
  }

  async function loadShopCategoryProducts(categoryId, categoryName) {
    const endpoint = buildProductsEndpoint(categoryId, currentShopSearch);
    shopCategoryTitle.textContent = categoryName;
    shopCategoryCopy.textContent = categoryId
      ? `Filtered products from the ${categoryName} category are shown below.`
      : 'All filtered shop products are shown below.';
    shopCategoryStatus.textContent = categoryId
      ? `Loading ${categoryName} products...`
      : 'Loading all products...';
    shopCategoryProducts.innerHTML = '<div class="category-empty">Loading products...</div>';

    try {
      const response = await fetch(endpoint, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      const data = await response.json();

      if (!response.ok || !data.success) {
        throw new Error(data.message || 'Unable to load products.');
      }

      if (currentShopSearch) {
        shopCategoryStatus.textContent = categoryId
          ? `${data.products.length} results found in ${categoryName} for "${currentShopSearch}".`
          : `${data.products.length} results found for "${currentShopSearch}".`;
      } else {
        shopCategoryStatus.textContent = categoryId
          ? `${data.products.length} products found in the ${categoryName} category.`
          : `${data.products.length} products available.`;
      }

      renderShopProducts(data.products);
    } catch (error) {
      shopCategoryStatus.textContent = 'Unable to load products.';
      shopCategoryProducts.innerHTML = `<div class="category-empty">${escapeHtml(error.message)}</div>`;
    }
  }

  homeCategoryButtons.forEach(button => {
    button.addEventListener('click', function() {
      homeCategoryButtons.forEach(item => item.classList.remove('is-active'));
      this.classList.add('is-active');
      loadHomeCategoryProducts(this.dataset.categoryId, this.dataset.categoryName);
    });
  });

  shopCategoryButtons.forEach(button => {
    button.addEventListener('click', function() {
      shopCategoryButtons.forEach(item => item.classList.remove('is-active'));
      this.classList.add('is-active');
      loadShopCategoryProducts(this.dataset.categoryId, this.dataset.categoryName);
    });
  });

  document.addEventListener('submit', async event => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.matches('.home-cart-form, .shop-cart-form')) {
      return;
    }

    event.preventDefault();

    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonHtml = submitButton ? submitButton.innerHTML : '';

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Adding...';
    }

    try {
      const response = await fetch(cartApiEndpoint, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new FormData(form)
      });

      const data = await response.json().catch(() => ({
        success: false,
        message: 'Cart update failed.'
      }));

      if (!response.ok || !data.success) {
        throw new Error(data.message || 'Unable to add the product to cart.');
      }

      updateCartCount(data.cart.total_items || 0);
      showDashboardToast(data.message || 'Product added to cart.');
    } catch (error) {
      showDashboardToast(error.message || 'Unable to add the product to cart.', 'error');
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonHtml;
      }
    }
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      closeUserMenu();
    }
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
      closeUserMenu();
    }
  });
</script>

