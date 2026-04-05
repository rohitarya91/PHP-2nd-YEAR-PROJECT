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
    add_to_cart($productId, 1);
    set_flash('success', 'Product added to cart.');
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
  $name = e($product['name']);
  $image = e(product_image_src($product['image']));
  $price = e((string) $product['price']);
  $description = e(product_short_description($product['description'], 90));
  $categoryName = e($product['category_name'] ?? 'Uncategorized');

  echo <<<HTML
    <div class="product-card home-product-card shop-product-card wide-product-card">
      <div class="shop-product-image home-product-image">
        <img src="{$image}" alt="{$name}">
      </div>
      <div class="home-product-body">
        <p class="product-category-label">{$categoryName}</p>
        <h4>{$name}</h4>
        <p class="price">Rs {$price}</p>
        <p class="product-desc">{$description}</p>
      </div>
      <form method="POST" class="home-cart-form">
        <input type="hidden" name="action" value="add_to_cart">
        <input type="hidden" name="product_id" value="{$productId}">
        <input type="hidden" name="redirect_section" value="home">
        <button type="submit" class="btn-primary full-btn">Add to Cart</button>
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
          <p class="muted">Explore fresh products and track your orders.</p>
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
            <span class="hero-badge">Fresh Grocery Delivery</span>
            <h2>Fresh &amp; Organic Products</h2>
            <p>Farm-fresh fruits, vegetables, dairy, and daily essentials delivered in 25-40 minutes with the same trusted quality.</p>
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
              <p class="muted">Category par click karo aur usi category ke saare products dekho.</p>
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
              <p class="muted" id="home-category-copy">Selected category ke products yahan dikhte hain.</p>
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
        <div class="stats-grid">
          <div class="stat-card">
            <h3>My Orders</h3>
            <strong><?php echo e((string) count($orders)); ?></strong>
          </div>

          <div class="stat-card">
            <h3>Products</h3>
            <strong><?php echo e((string) count($products)); ?></strong>
          </div>

          <div class="stat-card">
            <h3>Cart Items</h3>
            <strong id="shop-cart-count"><?php echo e((string) $cart['total_items']); ?></strong>
          </div>
        </div>

        <div class="content-card">
          <h2>Shop Fresh Products</h2>

          <div class="product-grid shop-grid-wide">
            <?php if (empty($products)): ?>
              <div class="product-card">
                <h4>No products found</h4>
                <p>Try another search term.</p>
              </div>
            <?php endif; ?>

            <?php foreach ($products as $product): ?>
              <div class="product-card shop-product-card wide-product-card">
                <div class="shop-product-image">
                  <img src="<?php echo e(product_image_src($product['image'])); ?>" alt="<?php echo e($product['name']); ?>">
                </div>
                <div class="shop-product-body">
                  <h4><?php echo e($product['name']); ?></h4>
                  <p class="price">Rs <?php echo e((string) $product['price']); ?></p>
                  <p class="product-desc"><?php echo e(product_short_description($product['description'], 100)); ?></p>
                </div>
                <form method="POST" class="shop-cart-form">
                  <input type="hidden" name="action" value="add_to_cart">
                  <input type="hidden" name="product_id" value="<?php echo e((string) $product['id']); ?>">
                  <input type="hidden" name="redirect_section" value="shop">
                  <button type="submit" class="btn-primary full-btn">Add to Cart</button>
                </form>
              </div>
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
                <?php if (!empty($order['items_summary'])): ?>
                  <p class="muted"><?php echo e($order['items_summary']); ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>

      <section id="profile-section" class="section-page <?php echo $section === 'profile' ? 'active' : ''; ?>">
        <div class="content-card">
          <h2>My Profile</h2>

          <div class="data-row">
            <span>Name</span>
            <strong><?php echo e(current_user_name()); ?></strong>
          </div>

          <div class="data-row">
            <span>Email</span>
            <strong><?php echo e(current_user_email()); ?></strong>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>

</html>
<script>
  const cartApiEndpoint = '../api/cart.php';
  const userSidebar = document.getElementById('user-side-panel');
  const userMenuToggle = document.querySelector('.menu-toggle');
  const userMenuOverlay = document.querySelector('.menu-overlay');
  const cartCountLabels = document.querySelectorAll('.cart-count');
  const shopCartCount = document.getElementById('shop-cart-count');
  const homeCategoryButtons = document.querySelectorAll('.category-chip');
  const homeCategoryProducts = document.getElementById('home-category-products');
  const homeCategoryTitle = document.getElementById('home-category-title');
  const homeCategoryCopy = document.getElementById('home-category-copy');
  const homeCategoryStatus = document.getElementById('home-category-status');
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

    if (shopCartCount) {
      shopCartCount.textContent = String(totalItems);
    }
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

  function renderHomeProducts(products) {
    if (!Array.isArray(products) || products.length === 0) {
      homeCategoryProducts.innerHTML = `
        <div class="category-empty">
          <h4>No products found</h4>
          <p>Is category me abhi koi product available nahi hai.</p>
        </div>
      `;
      return;
    }

    homeCategoryProducts.innerHTML = products.map(product => `
      <div class="product-card home-product-card shop-product-card wide-product-card">
        <div class="shop-product-image home-product-image">
          <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}">
        </div>
        <div class="home-product-body">
          <p class="product-category-label">${escapeHtml(product.category_name || 'Uncategorized')}</p>
          <h4>${escapeHtml(product.name)}</h4>
          <p class="price">Rs ${escapeHtml(product.price)}</p>
          <p class="product-desc">${escapeHtml(truncateText(product.description, 90))}</p>
        </div>
        <form method="POST" class="home-cart-form">
          <input type="hidden" name="action" value="add_to_cart">
          <input type="hidden" name="product_id" value="${escapeHtml(product.id)}">
          <input type="hidden" name="redirect_section" value="home">
          <button type="submit" class="btn-primary full-btn">Add to Cart</button>
        </form>
      </div>
    `).join('');
  }

  async function loadHomeCategoryProducts(categoryId, categoryName) {
    const endpoint = categoryId ? `../api/products.php?category_id=${encodeURIComponent(categoryId)}` : '../api/products.php';
    homeCategoryTitle.textContent = categoryName;
    homeCategoryCopy.textContent = categoryId
      ? `${categoryName} category ke saare products niche dikh rahe hain.`
      : 'Store ke saare available products niche dikh rahe hain.';
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
        throw new Error(data.message || 'Products load nahi ho paaye.');
      }

      homeCategoryStatus.textContent = categoryId
        ? `${categoryName} category me ${data.products.length} product mile.`
        : `${data.products.length} products available.`;
      renderHomeProducts(data.products);
    } catch (error) {
      homeCategoryStatus.textContent = 'Products load nahi ho paaye.';
      homeCategoryProducts.innerHTML = `<div class="category-empty">${escapeHtml(error.message)}</div>`;
    }
  }

  homeCategoryButtons.forEach(button => {
    button.addEventListener('click', function() {
      homeCategoryButtons.forEach(item => item.classList.remove('is-active'));
      this.classList.add('is-active');
      loadHomeCategoryProducts(this.dataset.categoryId, this.dataset.categoryName);
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
        throw new Error(data.message || 'Product add nahi ho paaya.');
      }

      updateCartCount(data.cart.total_items || 0);
      showDashboardToast(data.message || 'Product added to cart.');
    } catch (error) {
      showDashboardToast(error.message || 'Product add nahi ho paaya.', 'error');
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
