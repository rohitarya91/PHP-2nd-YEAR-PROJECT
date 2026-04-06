<?php
require_once __DIR__ . '/../config/store.php';

require_admin();

$flash = get_flash();
$errors = [];
$section = $_GET['section'] ?? 'dashboard';
$search = trim($_GET['search'] ?? '');
$name = current_user_name();
$safeName = e($name);
$avatarLetter = get_avatar_letter($name);
$formData = [
  'name' => '',
  'price' => '',
  'mrp' => '',
  'unit_label' => '1 pack',
  'description' => '',
  'category_id' => '',
  'stock_quantity' => '25',
  'delivery_minutes' => '20',
  'badge_text' => '',
  'is_featured' => 0,
  'image_url' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $section = 'add-product';
  enforce_csrf_or_errors($errors, 'admin_add_product_form');
  $validated = validate_product_data($_POST);
  $errors = array_merge($errors, $validated['errors']);
  $formData = [
    'name' => $validated['name'],
    'price' => (string) $validated['price'],
    'mrp' => (string) $validated['mrp'],
    'unit_label' => $validated['unit_label'],
    'description' => $validated['description'],
    'category_id' => (string) $validated['category_id'],
    'stock_quantity' => (string) $validated['stock_quantity'],
    'delivery_minutes' => (string) $validated['delivery_minutes'],
    'badge_text' => $validated['badge_text'],
    'is_featured' => (int) $validated['is_featured'],
    'image_url' => $validated['image_url'],
  ];

  if (empty($errors)) {
    $imageResult = resolve_product_image($_POST, $_FILES['image'], true);

    if (!$imageResult['success']) {
      $errors[] = $imageResult['message'];
    } elseif (create_product($conn, $validated, $imageResult['filename'])) {
      set_flash('success', 'Product added successfully.');
      redirect_to('dashboard.php?section=products');
    } else {
      $errors[] = 'Unable to add product right now.';
    }
  }
}

$counts = get_dashboard_counts($conn);
$categories = fetch_all_categories($conn);
$products = fetch_all_products($conn, $search);
$recentProducts = fetch_recent_products($conn);
$orders = fetch_admin_orders($conn);

$pageTitles = [
  'dashboard' => ['Admin Dashboard', 'Track catalog health, inventory movement, and order operations.'],
  'products' => ['All Products', 'Manage pricing, stock, delivery ETA, and merchandising from one place.'],
  'orders' => ['All Orders', 'Track customer orders with billing and delivery snapshots.'],
  'add-product' => ['Add Product', 'Create sellable quick-commerce SKUs with stock, MRP, and delivery info.'],
];

$pageTitle = $pageTitles[$section][0] ?? $pageTitles['dashboard'][0];
$pageSubtitle = $pageTitles[$section][1] ?? $pageTitles['dashboard'][1];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&family=Open+Sans:wght@300;400;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/dashbord_style.css">
  <style>
    .section-page {
      display: none;
    }

    .section-page.active {
      display: block;
    }
  </style>
</head>

<body class="page">
  <header>

  </header>
  <div class="page-shell">
    <aside id="admin-side-panel" class="glass-panel side-panel">
      <button type="button" class="side-panel-close" onclick="closeAdminMenu()" aria-label="Close menu">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <div class="profile-card">
        <div class="profile-avatar"><?php echo e($avatarLetter); ?></div>
        <h3>Welcome, <?php echo $safeName; ?></h3>
        <p class="muted">Admin</p>
      </div>

      <ul class="nav-list">
        <li><a href="#" onclick="showAdminSection('dashboard', event)"
            class="<?php echo $section === 'dashboard' ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line"></i>
            Dashboard</a></li>
        <li><a href="#" onclick="showAdminSection('products', event)"
            class="<?php echo $section === 'products' ? 'active' : ''; ?>"><i class="fa-solid fa-box"></i> All
            Products</a></li>
        <li><a href="#" onclick="showAdminSection('orders', event)"
            class="<?php echo $section === 'orders' ? 'active' : ''; ?>"><i class="fa-solid fa-cart-shopping"></i> All
            Orders</a></li>
        <li><a href="#" onclick="showAdminSection('add-product', event)"
            class="<?php echo $section === 'add-product' ? 'active' : ''; ?>"><i class="fa-solid fa-plus"></i> Add
            Product</a></li>
        <li><a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
      </ul>

      <div class="content-card side-note-card">
        <p class="muted">Signed in as <?php echo $safeName; ?></p>
      </div>
    </aside>
    <div class="menu-overlay" onclick="closeAdminMenu()" aria-hidden="true"></div>

    <main class="glass-panel main-panel">
      <div class="panel-header">
        <button
          type="button"
          class="menu-toggle"
          onclick="toggleAdminMenu()"
          aria-label="Toggle admin menu"
          aria-controls="admin-side-panel"
          aria-expanded="false">
          <i class="fa fa-bars"></i>
        </button>
        <div class="panel-title">
          <h1 id="admin-page-title"><?php echo e($pageTitle); ?></h1>
          <p class="muted" id="admin-page-subtitle"><?php echo e($pageSubtitle); ?></p>
        </div>

        <div class="panel-actions">
          <button type="button" class="btn-ghost" onclick="openAdminSection('products')">
            <i class="fa-solid fa-box"></i> Products
          </button>
          <button type="button" class="btn-primary" onclick="openAdminSection('add-product')">
            <i class="fa-solid fa-plus"></i> Add Product
          </button>
        </div>
      </div>

      <?php if ($flash): ?>
        <div
          class="content-card message-card <?php echo $flash['type'] === 'success' ? 'success-card' : 'error-card'; ?>">
          <p><?php echo e($flash['message']); ?></p>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="content-card message-card error-card">
          <?php foreach ($errors as $error): ?>
            <p><?php echo e($error); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <section id="dashboard-section" class="section-page <?php echo $section === 'dashboard' ? 'active' : ''; ?>">
        <div class="content-card section-card">
          <h2>Store Overview</h2>
          <div class="stats-grid">
            <div class="stat-card">
              <h3>Total Products</h3>
              <strong><?php echo e((string) $counts['products']); ?></strong>
            </div>

            <div class="stat-card">
              <h3>Total Orders</h3>
              <strong><?php echo e((string) $counts['orders']); ?></strong>
            </div>

            <div class="stat-card">
              <h3>Total Users</h3>
              <strong><?php echo e((string) $counts['users']); ?></strong>
            </div>

            <div class="stat-card">
              <h3>Total Sales</h3>
              <strong>Rs <?php echo e((string) $counts['sales']); ?></strong>
            </div>

            <div class="stat-card">
              <h3>Featured SKUs</h3>
              <strong><?php echo e((string) $counts['featured_products']); ?></strong>
            </div>

            <div class="stat-card">
              <h3>Low Stock</h3>
              <strong><?php echo e((string) $counts['low_stock']); ?></strong>
            </div>

            <div class="stat-card">
              <h3>Out of Stock</h3>
              <strong><?php echo e((string) $counts['out_of_stock']); ?></strong>
            </div>
          </div>
        </div>

        <div class="content-card section-card">
          <h2>Recent Activity</h2>
          <?php if (empty($recentProducts)): ?>
            <p class="muted">No recent product activity to show.</p>
          <?php else: ?>
            <?php foreach ($recentProducts as $recentProduct): ?>
              <div class="order-item">
                <p><?php echo e($recentProduct['name']); ?> was added on
                  <?php echo e(date('d M Y, h:i A', strtotime($recentProduct['created_at']))); ?>
                </p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>

      <section id="products-section" class="section-page <?php echo $section === 'products' ? 'active' : ''; ?>">
        <div class="content-card section-card">
          <div class="section-heading-row">
            <div>
              <h2>All Products</h2>
              <p class="muted">Edit or delete products from the list below.</p>
            </div>
            <form method="GET" class="search-form">
              <input type="hidden" name="section" value="products">
              <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search products..."
                class="search-input">
              <button type="submit" class="btn-ghost"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </form>
          </div>

          <div class="product-grid admin-product-grid">
            <?php if (empty($products)): ?>
              <div class="content-card empty-card">
                <p class="muted">No products found.</p>
              </div>
            <?php endif; ?>

            <?php foreach ($products as $row): ?>
              <div class="product-card pro-card store-product-card">
                <div class="product-img">
                  <img src="<?php echo e(product_image_src($row['image'])); ?>" alt="<?php echo e($row['name']); ?>">
                </div>

                <div class="product-body">
                  <?php $badgeLabel = product_badge_label($row); ?>
                  <?php if ($badgeLabel !== ''): ?>
                    <p class="muted"><?php echo e($badgeLabel); ?></p>
                  <?php endif; ?>
                  <h3><?php echo e($row['name']); ?></h3>
                  <p class="muted"><?php echo e($row['category_name'] ?? 'Uncategorized'); ?> | <?php echo e(product_unit_label($row)); ?></p>
                  <p class="price">Rs <?php echo e((string) $row['price']); ?><?php if (product_savings_amount($row) > 0): ?> <span class="muted">MRP Rs <?php echo e((string) product_mrp_value($row)); ?></span><?php endif; ?></p>
                  <p class="product-desc"><?php echo e(product_short_description($row['description'], 110)); ?></p>
                  <p class="muted"><?php echo e(product_stock_status($row)); ?> | ETA <?php echo e(product_delivery_window($row)); ?></p>
                  <p class="muted">Added on <?php echo e(date('d M Y', strtotime($row['created_at']))); ?></p>
                </div>

                <div class="product-footer">
                  <div class="admin-actions full-actions">
                    <a href="edit_product.php?id=<?php echo e((string) $row['id']); ?>" class="action-btn edit-btn"><i
                        class="fa-solid fa-pen"></i> Edit</a>
                    <form method="POST" action="delete_product.php" onsubmit="return confirm('Delete this product?');">
                      <?php echo csrf_field('admin_delete_product_form'); ?>
                      <input type="hidden" name="id" value="<?php echo e((string) $row['id']); ?>">
                      <button type="submit" class="action-btn delete-btn-link"><i class="fa-solid fa-trash"></i> Delete</button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section id="orders-section" class="section-page <?php echo $section === 'orders' ? 'active' : ''; ?>">
        <div class="content-card section-card">
          <h2>All Orders</h2>
          <p class="muted section-subtext">Every placed order is listed here.</p>
          <?php if (empty($orders)): ?>
            <p class="muted">No orders placed yet.</p>
          <?php else: ?>
            <?php foreach ($orders as $order): ?>
              <div class="order-item">
                <p>#<?php echo e((string) $order['id']); ?> | <?php echo e($order['user_name']); ?> |
                  <?php echo e($order['email']); ?> | Rs <?php echo e((string) $order['total_amount']); ?> |
                  <?php echo e($order['status']); ?>
                </p>
                <p class="muted">
                  Subtotal: Rs <?php echo e((string) $order['subtotal_amount']); ?> |
                  Savings: Rs <?php echo e((string) $order['discount_amount']); ?> |
                  Delivery: Rs <?php echo e((string) $order['delivery_fee']); ?> |
                  Handling: Rs <?php echo e((string) $order['handling_fee']); ?>
                </p>
                <p class="muted">
                  Payment: <?php echo e($order['payment_method'] ?? STORE_DEFAULT_PAYMENT_METHOD); ?> |
                  Payment Status: <?php echo e($order['payment_status'] ?? 'Pending'); ?> |
                  ETA: <?php echo e($order['delivery_eta_label'] ?? 'Pending'); ?>
                  <?php if (!empty($order['payment_reference'])): ?> | Ref: <?php echo e($order['payment_reference']); ?><?php endif; ?>
                </p>
                <?php if (!empty($order['items_summary'])): ?>
                  <p class="muted"><?php echo e($order['items_summary']); ?></p>
                <?php endif; ?>
                <?php if (!empty($order['shipping_name']) || !empty($order['shipping_phone']) || !empty($order['shipping_address'])): ?>
                  <p class="muted">
                    Delivery contact:
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

      <section id="add-product-section" class="section-page <?php echo $section === 'add-product' ? 'active' : ''; ?>">
        <div class="content-card section-card">
          <h2>Add Product</h2>
          <p class="muted section-subtext">Create production-ready catalog items with pricing, stock, unit size, ETA,
            and merchandising labels. If both image options are filled, the uploaded file is used first.</p>
          <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field('admin_add_product_form'); ?>
            <input type="text" name="name" placeholder="Product Name" required class="input-field"
              value="<?php echo e($formData['name']); ?>">
            <input type="number" name="price" placeholder="Price" required class="input-field"
              value="<?php echo e($formData['price']); ?>">
            <input type="number" name="mrp" placeholder="MRP" required class="input-field"
              value="<?php echo e($formData['mrp']); ?>">
            <input type="text" name="unit_label" placeholder="Unit / Pack Size (e.g. 500 g, 1 L)" required class="input-field"
              value="<?php echo e($formData['unit_label']); ?>">
            <select name="category_id" required class="input-field ">
              <option value="" style="color: #555;">Select Category</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo e((string) $category['id']); ?>" <?php echo $formData['category_id'] === (string) $category['id'] ? 'selected' : ''; ?> style="color: #555;">
                  <?php echo e($category['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <input type="number" name="stock_quantity" placeholder="Available Stock" required class="input-field"
              value="<?php echo e($formData['stock_quantity']); ?>">
            <input type="number" name="delivery_minutes" placeholder="Delivery ETA in Minutes" required class="input-field"
              value="<?php echo e($formData['delivery_minutes']); ?>">
            <input type="text" name="badge_text" placeholder="Badge Text (e.g. Best Seller)" class="input-field"
              value="<?php echo e($formData['badge_text']); ?>">
            <label class="muted" style="display:flex; align-items:center; gap:10px;">
              <input type="checkbox" name="is_featured" value="1" <?php echo (int) $formData['is_featured'] === 1 ? 'checked' : ''; ?>>
              Mark as featured product
            </label>
            <textarea name="description" placeholder="Product Description" required
              class="input-field text-area-field"><?php echo e($formData['description']); ?></textarea>
            <div class="file-input-wrapper">
              <label>Upload Product Image:</label>
              <input type="file" name="image" class="input-field" accept=".jpg,.jpeg,.png,.webp">
            </div>
            <input type="url" name="image_url" placeholder="Or paste image URL" class="input-field"
              value="<?php echo e($formData['image_url']); ?>">
            <button type="submit" class="btn-primary full-width">Add Product</button>
          </form>
        </div>
      </section>
    </main>
  </div>
</body>

</html>
<script>
  const adminSidebar = document.getElementById('admin-side-panel');
  const adminMenuToggle = document.querySelector('.menu-toggle');
  const adminMenuOverlay = document.querySelector('.menu-overlay');
  const sectionMeta = {
    dashboard: {
      title: 'Admin Dashboard',
      subtitle: 'View store statistics and recent activity.'
    },
    products: {
      title: 'All Products',
      subtitle: 'Manage your full product catalog from one place.'
    },
    orders: {
      title: 'All Orders',
      subtitle: 'Track every placed order with real data.'
    },
    'add-product': {
      title: 'Add Product',
      subtitle: 'Add a new product using upload or image URL.'
    }
  };

  function activateAdminSection(section) {
    document.querySelectorAll('.section-page').forEach(sec => {
      sec.classList.remove('active');
    });

    const sectionElement = document.getElementById(section + '-section');
    if (sectionElement) {
      sectionElement.classList.add('active');
    }

    document.querySelectorAll('.nav-list a').forEach(link => {
      link.classList.remove('active');
    });

    const activeLink = document.querySelector(`.nav-list a[onclick*="'${section}'"]`);
    if (activeLink) {
      activeLink.classList.add('active');
    }

    if (sectionMeta[section]) {
      document.getElementById('admin-page-title').textContent = sectionMeta[section].title;
      document.getElementById('admin-page-subtitle').textContent = sectionMeta[section].subtitle;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('section', section);
    if (section !== 'products') {
      url.searchParams.delete('search');
    }
    window.history.replaceState({}, '', url);
  }

  function showAdminSection(section, event) {
    event.preventDefault();
    activateAdminSection(section);
    closeAdminMenu();
  }

  function openAdminSection(section) {
    activateAdminSection(section);
    closeAdminMenu();
  }

  function setAdminMenuState(isOpen) {
    if (!adminSidebar || !adminMenuToggle || !adminMenuOverlay) {
      return;
    }

    adminSidebar.classList.toggle('active', isOpen);
    adminMenuOverlay.classList.toggle('active', isOpen);
    document.body.classList.toggle('menu-open', isOpen);
    adminMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  }

  function toggleAdminMenu() {
    if (window.innerWidth > 768) {
      return;
    }

    setAdminMenuState(!adminSidebar.classList.contains('active'));
  }

  function closeAdminMenu() {
    setAdminMenuState(false);
  }

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      closeAdminMenu();
    }
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
      closeAdminMenu();
    }
  });
</script>
