<?php
require_once __DIR__ . '/config/store.php';

$categories = fetch_all_categories($conn);
$initialProducts = fetch_all_products($conn);
$isLoggedIn = current_user_id() > 0;
$dashboardHref = is_admin() ? './dashboard/dashboard.php' : './dashboard/user_dashboard.php';
$primaryActionHref = $isLoggedIn ? $dashboardHref : './auth/login.php';
$primaryActionLabel = $isLoggedIn ? 'Open Dashboard' : 'Login';
$heroActionLabel = $isLoggedIn ? 'Go to Dashboard' : 'Shop Fresh';

function render_public_product_card(array $product): void
{
    $name = e($product['name'] ?? '');
    $price = e((string) ($product['price'] ?? 0));
    $mrp = e((string) product_mrp_value($product));
    $unitLabel = e(product_unit_label($product));
    $description = e(product_short_description($product['description'] ?? '', 120));
    $categoryName = e($product['category_name'] ?? 'Uncategorized');
    $image = e(product_image_src($product['image'] ?? null, 'uploads/'));
    $badgeLabel = e(product_badge_label($product));
    $stockStatus = e(product_stock_status($product));
    $deliveryLabel = e(product_delivery_window($product));
    $badgeMarkup = $badgeLabel !== '' ? "<span class=\"category-tag\">{$badgeLabel}</span>" : '';

    echo <<<HTML
      <article class="card product-card-live">
        <div class="product-image">
          <img src="{$image}" alt="{$name}">
        </div>
        <div class="product-content">
          {$badgeMarkup}
          <span class="category-tag">{$categoryName}</span>
          <h3>{$name}</h3>
          <p class="product-price">Rs {$price} <span style="font-size: 13px; color: rgba(255,255,255,0.7);">MRP Rs {$mrp}</span></p>
          <p style="color: rgba(255,255,255,0.72); margin-bottom: 8px;">{$unitLabel} | {$stockStatus}</p>
          <p style="color: rgba(255,255,255,0.72); margin-bottom: 10px;">Fast delivery in {$deliveryLabel}</p>
          <p>{$description}</p>
        </div>
      </article>
    HTML;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description"
    content="Harvest Fresh: Organic fruits, vegetables, and farm-fresh products delivered directly from local farmers to your table." />
  <meta name="keywords"
    content="organic produce, fresh vegetables, farm-to-table, local farming, healthy food delivery" />
  <title>Harvest Fresh</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    :root {
      --bg-dark: #0f172a;
      --green: #22c55e;
      --green-dark: #16a34a;
      --glass: rgba(255, 255, 255, 0.08);
      --text: #ffffff;
      --muted: rgba(255, 255, 255, 0.7);
      --border: rgba(255, 255, 255, 0.14);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f172a, #14532d);
      color: var(--text);
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    button {
      font: inherit;
    }

    .container {
      width: 90%;
      max-width: 1100px;
      margin: auto;
    }

    section {
      padding: 60px 20px;
      margin: 20px;
      border-radius: 20px;
      background: var(--glass);
      backdrop-filter: blur(10px);
    }

    h2 {
      text-align: center;
      margin-bottom: 18px;
      font-size: 34px;
    }

    .section-copy {
      color: var(--muted);
      text-align: center;
      max-width: 680px;
      margin: 0 auto 30px;
    }

    #navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 25px;
      margin: 15px;
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(12px);
      border-radius: 15px;
      position: sticky;
      top: 10px;
      z-index: 1000;
    }

    #logo {
      font-size: 22px;
      font-weight: 600;
    }

    #nav-links {
      list-style: none;
      display: flex;
      align-items: center;
      gap: 20px;
    }

    #nav-links a {
      font-size: 16px;
      color: #fff;
      transition: 0.3s;
    }

    #nav-links a:hover {
      color: var(--green);
    }

    .nav-btn {
      background: var(--green);
      padding: 8px 18px;
      border-radius: 20px;
    }

    .nav-btn:hover {
      background: var(--green-dark);
      color: #fff;
    }

    #menu-icon {
      display: none;
      font-size: 22px;
      cursor: pointer;
    }

    .hero {
      height: 90vh;
      margin: 20px;
      border-radius: 25px;
      background:
        linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
        url("https://images.unsplash.com/photo-1542838132-92c53300491e");
      background-size: cover;
      background-position: center;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
    }

    .hero-content {
      max-width: 700px;
    }

    .hero-content h1 {
      font-size: 50px;
      font-weight: 700;
    }

    .hero-content p {
      color: var(--muted);
      margin: 15px 0;
      font-size: 18px;
    }

    .btn {
      display: inline-block;
      padding: 12px 28px;
      background: var(--green);
      border-radius: 30px;
      color: #fff;
      transition: 0.3s;
      border: none;
    }

    .btn:hover {
      background: var(--green-dark);
      transform: scale(1.05);
    }

    .about-content {
      display: flex;
      align-items: center;
      gap: 30px;
    }

    .about-content img {
      width: 45%;
      border-radius: 12px;
    }

    .about-content p {
      color: var(--muted);
      line-height: 1.7;
    }

    .cards,
    .catalog-products {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 25px;
    }

    .card {
      padding: 20px;
      text-align: center;
      border-radius: 20px;
      background: var(--glass);
      backdrop-filter: blur(10px);
      transition: 0.3s;
    }

    .card:hover {
      transform: translateY(-10px) scale(1.03);
      box-shadow: 0 10px 40px rgba(34, 197, 94, 0.4);
    }

    .card img {
      width: 110px;
      height: 110px;
      border-radius: 50%;
      margin-bottom: 10px;
    }

    .card h3 {
      margin-bottom: 10px;
    }

    .card p {
      color: var(--muted);
      font-size: 14px;
    }

    .category-toolbar {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 14px;
      margin-bottom: 24px;
    }

    .category-button {
      border: 1px solid var(--border);
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      padding: 12px 18px;
      border-radius: 999px;
      cursor: pointer;
      transition: 0.25s ease;
    }

    .category-button:hover,
    .category-button.is-active {
      background: var(--green);
      border-color: transparent;
      color: #fff;
      transform: translateY(-2px);
    }

    .catalog-status {
      text-align: center;
      color: var(--muted);
      margin-bottom: 24px;
      min-height: 24px;
    }

    .product-card-live {
      padding: 0;
      text-align: left;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .product-card-live img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      border-radius: 0;
      margin: 0;
    }

    .product-image {
      background: rgba(255, 255, 255, 0.06);
    }

    .product-content {
      padding: 20px;
    }

    .category-tag {
      display: inline-block;
      margin-bottom: 10px;
      padding: 6px 12px;
      border-radius: 999px;
      background: rgba(34, 197, 94, 0.18);
      color: #bbf7d0;
      font-size: 12px;
      font-weight: 600;
    }

    .product-price {
      color: #86efac;
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .catalog-empty {
      grid-column: 1 / -1;
      text-align: center;
      padding: 30px 20px;
      border-radius: 18px;
      border: 1px dashed var(--border);
      color: var(--muted);
      background: rgba(255, 255, 255, 0.05);
    }

    .contact form {
      max-width: 500px;
      margin: auto;
      display: flex;
      flex-direction: column;
    }

    .contact input,
    .contact textarea {
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      background: rgba(255, 255, 255, 0.1);
      color: #fff;
    }

    .contact input::placeholder,
    .contact textarea::placeholder {
      color: rgba(255, 255, 255, 0.6);
    }

    .contact button {
      padding: 12px;
      background: var(--green);
      border: none;
      border-radius: 25px;
      color: #fff;
      cursor: pointer;
    }

    .contact button:hover {
      background: var(--green-dark);
    }

    footer {
      background: rgba(0, 0, 0, 0.6);
      padding: 40px 20px;
    }

    @media (max-width: 768px) {
      #menu-icon {
        display: block;
      }

      #nav-links {
        position: absolute;
        top: 70px;
        right: 0;
        width: 220px;
        flex-direction: column;
        padding: 20px;
        background: rgba(0, 0, 0, 0.9);
        border-radius: 10px;
        display: none;
      }

      #nav-links.show {
        display: flex !important;
      }

      #nav-links li {
        margin: 10px 0;
      }

      .about-content {
        flex-direction: column;
        text-align: center;
      }

      .about-content img {
        width: 100%;
      }

      .hero-content h1 {
        font-size: 38px;
      }
    }
  </style>
</head>

<body>
  <nav id="navbar">
    <div id="logo">Harvest Fresh</div>

    <div id="menu-icon" onclick="toggleMenu()">
      <i class="fas fa-bars"></i>
    </div>

    <ul id="nav-links">
      <li><a href="#home">Home</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="#services">Shop</a></li>
      <li><a href="#contact">Contact</a></li>
      <li><a href="<?php echo e($primaryActionHref); ?>" class="nav-btn"><?php echo e($primaryActionLabel); ?></a></li>
    </ul>
  </nav>

  <section id="home" class="hero">
    <div class="hero-content">
      <h1 id="mainText">Fresh From Farm to Your Table</h1>
      <p>
        Real inventory, real grocery imagery, and quick-commerce essentials delivered with a fast local promise.
      </p>
      <a href="<?php echo e($primaryActionHref); ?>" class="btn"><?php echo e($heroActionLabel); ?></a>
    </div>
  </section>

  <section id="about" class="about">
    <div class="container">
      <h2>About Harvest Fresh</h2>
      <div class="about-content">
        <img
          src="https://imgs.search.brave.com/jNhXjk55Vd-TRGcd_4LeotpvSoQLE3MVA9gEsDJfpcU/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly90My5m/dGNkbi5uZXQvanBn/LzAwLzg2LzEzLzc2/LzM2MF9GXzg2MTM3NjQ3X29vVVJFSW85YTluRDNaQVhjdXpraThoUExoSHZOT2MxLmpwZw"
          alt="Fresh farm produce including various fruits and vegetables" />
        <p>
          Harvest Fresh is now shaped like a practical quick-commerce store:
          curated categories, real product photos, stock-aware catalog cards,
          and a delivery-focused shopping flow built for everyday grocery use.
        </p>
      </div>
    </div>
  </section>

  <section id="services" class="services">
    <div class="container">
      <h2>Shop by Category</h2>
      <p class="section-copy">
        Click any category to browse stocked items with pack sizes, delivery ETA,
        and pricing details. The list updates instantly without refreshing the page.
      </p>

      <div class="category-toolbar" id="category-list">
        <button type="button" class="category-button is-active" data-category-id="" data-category-name="All Products">
          All Products
        </button>
        <?php foreach ($categories as $category): ?>
          <button
            type="button"
            class="category-button"
            data-category-id="<?php echo e((string) $category['id']); ?>"
            data-category-name="<?php echo e($category['name']); ?>">
            <?php echo e($category['name']); ?>
          </button>
        <?php endforeach; ?>
      </div>

      <p class="catalog-status" id="selected-category-label">Showing all products</p>

      <div class="catalog-products" id="product-list">
        <?php if (empty($initialProducts)): ?>
          <div class="catalog-empty">No products available right now.</div>
        <?php else: ?>
          <?php foreach ($initialProducts as $product): ?>
            <?php render_public_product_card($product); ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section id="contact" class="contact">
    <div class="container">
      <h2>Get In Touch</h2>
      <form id="contactForm" onsubmit="handleSubmit(event)">
        <input type="text" placeholder="Store Manager Name" required />
        <input type="email" placeholder="Business Email" required />
        <textarea placeholder="Tell us your delivery area, product needs, or onboarding query" required></textarea>
        <button type="submit">Request Callback</button>
      </form>
    </div>
  </section>

  <footer class="bg-dark text-white mt-5 p-4">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <h5>Harvest Fresh</h5>
          <p>Farm to table freshness.</p>
        </div>

        <div class="col-md-4">
          <h5>Quick Links</h5>
          <ul class="list-unstyled">
            <li><a class="text-white" href="#home">Home</a></li>
            <li><a class="text-white" href="#services">Shop</a></li>
            <li><a class="text-white" href="#contact">Contact</a></li>
          </ul>
        </div>

        <div class="col-md-4">
          <h5>Follow Us</h5>
          <ul class="list-unstyled">
            <li><a class="text-white" href="#" aria-label="Facebook"><i>Facebook</i></a></li>
            <li><a class="text-white" href="#" aria-label="Instagram"><i>Instagram</i></a></li>
            <li><a class="text-white" href="#" aria-label="Twitter"><i>Twitter</i></a></li>
          </ul>
        </div>
      </div>

      <hr />
      <p class="text-center mb-0">&copy; 2026 Harvest Fresh</p>
    </div>
  </footer>

  <script>
    const productList = document.getElementById('product-list');
    const categoryButtons = document.querySelectorAll('.category-button');
    const selectedCategoryLabel = document.getElementById('selected-category-label');

    function toggleMenu() {
      document.getElementById('nav-links').classList.toggle('show');
    }

    function handleSubmit(event) {
      event.preventDefault();
      alert("Thank you for your message! We'll get back to you soon.");
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

    function normalizeImagePath(imagePath) {
      const path = String(imagePath || '');
      return path.startsWith('../uploads/') ? path.replace('../uploads/', 'uploads/') : path;
    }

    function renderProducts(products) {
      if (!Array.isArray(products) || products.length === 0) {
        productList.innerHTML = '<div class="catalog-empty">No products found for this category.</div>';
        return;
      }

      productList.innerHTML = products.map(product => {
        const mrp = Number(product.mrp || product.price || 0);
        const price = Number(product.price || 0);
        const unitLabel = product.unit_label || '1 pack';
        const stockQuantity = Number(product.stock_quantity || 0);
        const stockStatus = stockQuantity <= 0 ? 'Out of stock' : (stockQuantity <= 8 ? `Only ${stockQuantity} left` : 'In stock');
        const deliveryMinutes = Math.max(10, Number(product.delivery_minutes || 20));
        const badgeLabel = String(product.badge_text || '').trim() || (Number(product.is_featured || 0) === 1 ? 'Featured' : '');
        const badgeMarkup = badgeLabel ? `<span class="category-tag">${escapeHtml(badgeLabel)}</span>` : '';

        return `
        <article class="card product-card-live">
          <div class="product-image">
            <img src="${escapeHtml(normalizeImagePath(product.image))}" alt="${escapeHtml(product.name)}">
          </div>
          <div class="product-content">
            ${badgeMarkup}
            <span class="category-tag">${escapeHtml(product.category_name || 'Uncategorized')}</span>
            <h3>${escapeHtml(product.name)}</h3>
            <p class="product-price">Rs ${escapeHtml(price)} <span style="font-size: 13px; color: rgba(255,255,255,0.7);">MRP Rs ${escapeHtml(mrp)}</span></p>
            <p style="color: rgba(255,255,255,0.72); margin-bottom: 8px;">${escapeHtml(unitLabel)} | ${escapeHtml(stockStatus)}</p>
            <p style="color: rgba(255,255,255,0.72); margin-bottom: 10px;">Fast delivery in ${escapeHtml(`${deliveryMinutes}-${deliveryMinutes + 10} mins`)}</p>
            <p>${escapeHtml(truncateText(product.description, 120))}</p>
          </div>
        </article>
      `;
      }).join('');
    }

    async function loadProducts(categoryId, categoryName) {
      const endpoint = categoryId ? `catalog_api.php?category_id=${encodeURIComponent(categoryId)}` : 'catalog_api.php';
      selectedCategoryLabel.textContent = categoryId
        ? `Showing ${categoryName} products`
        : 'Showing all products';
      productList.innerHTML = '<div class="catalog-empty">Loading products...</div>';

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

        renderProducts(data.products);
      } catch (error) {
        productList.innerHTML = `<div class="catalog-empty">${escapeHtml(error.message)}</div>`;
      }
    }

    categoryButtons.forEach(button => {
      button.addEventListener('click', function() {
        categoryButtons.forEach(item => item.classList.remove('is-active'));
        this.classList.add('is-active');
        loadProducts(this.dataset.categoryId, this.dataset.categoryName);
      });
    });
  </script>
</body>

</html>
