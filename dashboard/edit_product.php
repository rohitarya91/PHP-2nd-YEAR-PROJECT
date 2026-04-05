<?php
require_once __DIR__ . '/../config/store.php';

require_admin();

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = fetch_product_by_id($conn, $productId);
$categories = fetch_all_categories($conn);

if (!$product) {
  set_flash('error', 'Product not found.');
  redirect_to('dashboard.php?section=products');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $validated = validate_product_data($_POST, false);
  $errors = $validated['errors'];

  if (empty($errors)) {
    $imageResult = resolve_product_image($_POST, $_FILES['image'], false, $product['image']);

    if (!$imageResult['success']) {
      $errors[] = $imageResult['message'];
    } elseif (update_product($conn, $productId, $validated['name'], $validated['price'], $validated['description'], $imageResult['filename'], $validated['category_id'])) {
      if ($imageResult['filename'] !== $product['image']) {
        delete_product_image($product['image']);
      }

      set_flash('success', 'Product updated successfully.');
      redirect_to('dashboard.php?section=products');
    } else {
      $errors[] = 'Unable to update product right now.';
    }
  }

  $product['name'] = $validated['name'];
  $product['price'] = $validated['price'];
  $product['description'] = $validated['description'];
  $product['category_id'] = $validated['category_id'];
  foreach ($categories as $category) {
    if ((int) $category['id'] === (int) $validated['category_id']) {
      $product['category_name'] = $category['name'];
      break;
    }
  }
  if (!empty($validated['image_url'])) {
    $product['image'] = $validated['image_url'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Product</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/dashbord_style.css">
</head>

<body class="page">
  <div class="page-shell">
    <aside class="glass-panel side-panel">
      <div class="profile-card">
        <div class="profile-avatar"><?php echo e(get_avatar_letter(current_user_name())); ?></div>
        <h3>Welcome, <?php echo e(current_user_name()); ?></h3>
        <p class="muted">Admin</p>
      </div>
      <ul class="nav-list">
        <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
        <li><a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
      </ul>
    </aside>

    <main class="glass-panel main-panel">
      <div class="panel-header">
        <div class="panel-title">
          <h1>Edit Product</h1>
          <p class="muted">Update product details without changing the current design.</p>
        </div>
        <a href="dashboard.php?section=products" class="btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="content-card message-card error-card">
          <?php foreach ($errors as $error): ?>
            <p><?php echo e($error); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="content-card edit-form-card">
        <form method="POST" enctype="multipart/form-data">
          <input type="text" name="name" placeholder="Product Name" required class="input-field" value="<?php echo e($product['name']); ?>">
          <input type="number" name="price" placeholder="Price" required class="input-field" value="<?php echo e((string) $product['price']); ?>">
          <select name="category_id" required class="input-field">
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo e((string) $category['id']); ?>" <?php echo (string) ($product['category_id'] ?? '') === (string) $category['id'] ? 'selected' : ''; ?>>
                <?php echo e($category['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <textarea name="description" placeholder="Product Description" required class="input-field text-area-field"><?php echo e($product['description']); ?></textarea>
          <div class="file-input-wrapper">
            <label>Change Product Image:</label>
            <input type="file" name="image" class="input-field" accept=".jpg,.jpeg,.png,.webp">
          </div>
          <input type="url" name="image_url" placeholder="Or paste new image URL" class="input-field" value="<?php echo is_external_image($product['image']) ? e($product['image']) : ''; ?>">

          <div class="product-grid single-product-grid single-wide-grid">
            <div class="product-card pro-card store-product-card">
              <div class="product-img">
                <img src="<?php echo e(product_image_src($product['image'])); ?>" alt="<?php echo e($product['name']); ?>">
              </div>
              <div class="product-body">
                <h3><?php echo e($product['name']); ?></h3>
                <p class="muted"><?php echo e($product['category_name'] ?? 'Uncategorized'); ?></p>
                <p class="price">Rs <?php echo e((string) $product['price']); ?></p>
                <p class="product-desc"><?php echo e(product_short_description($product['description'], 120)); ?></p>
              </div>
            </div>
          </div>

          <button type="submit" class="btn-primary full-width">Save Changes</button>
        </form>
      </div>
    </main>
  </div>
</body>

</html>
