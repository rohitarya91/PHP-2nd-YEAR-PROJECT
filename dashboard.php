<?php
session_start();

if (!isset($_SESSION['email'])) {
  header("Location: login.php");
  exit();
}

if ($_SESSION['email'] != "admin@gmail.com") {
  header("Location: user_dashboard.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400&family=Open+Sans:wght@300;400;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- <link rel="stylesheet" href="style.css" /> Login Styles -->
  <link rel="stylesheet" href="dashboard.css" /> <!-- Dashboard Specific Styles -->
</head>

<body>
  <div class="app-container">
    <div class="sidebar">
      <div>
        <div class="main_content">
          <!-- Placeholder image if the original link is broken or slow. Using a generic avatar/icon for now -->
          <img
            src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name'] ?? 'User'); ?>&background=random"
            alt="Profile" />
          <h3>
            <?php
            $name = $_SESSION['user_name'] ?? 'User';
            echo "Welcome, " . htmlspecialchars($name);
            ?>
          </h3>
        </div>

        <div class="list_dash">
          <ul>
            <li onclick="switchTab('dashboard', this)" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard
            </li>
            <li onclick="switchTab('orders', this)"><i class="fa-solid fa-cart-shopping"></i> All Orders</li>
            <li onclick="switchTab('products', this)"><i class="fa-solid fa-box"></i> All Products</li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
          </ul>
        </div>
      </div>

      <div class="user-profile">
        <div class="avtar">
          <?php echo strtoupper(substr($name, 0, 1)); ?>
        </div>
        <div class="info">
          <span class="name">
            <h6><?php echo htmlspecialchars($name); ?></h6>
          </span>
          <span class="role">Admin</span>
        </div>
      </div>
    </div>

    <main class="main-content">
      <header class="top-bar">
        <h1 id="page-title">Dashboard</h1>
        <div class="actions">
          <button class="btn-icon"><i class="fa-regular fa-bell"></i></button>
          <button class="btn-primary" id="add-student-btn"><i class="fa-solid fa-plus"></i> Add Student</button>
        </div>
      </header>

      <div class="content-area">

        <!-- Dashboard View -->
        <div id="dashboard-view" class="view-section active">
          <div class="stats-grid">
            <div class="stat-box box1">
              <h1>Total Orders</h1>
              <b>12</b>
              <i class="fa-solid fa-cart-shopping"
                style="position:absolute; right:20px; bottom:20px; font-size:2rem; opacity:0.3;"></i>
            </div>
            <div class="stat-box box2">
              <h1>Products</h1>
              <b>25</b>
              <i class="fa-solid fa-box-open"
                style="position:absolute; right:20px; bottom:20px; font-size:2rem; opacity:0.3;"></i>
            </div>
            <div class="stat-box box3">
              <h1>Pending</h1>
              <b>4</b>
              <i class="fa-solid fa-clock"
                style="position:absolute; right:20px; bottom:20px; font-size:2rem; opacity:0.3;"></i>
            </div>
            <div class="stat-box box4">
              <h1>Sales</h1>
              <b>₹8,500</b>
              <i class="fa-solid fa-indian-rupee-sign"
                style="position:absolute; right:20px; bottom:20px; font-size:2rem; opacity:0.3;"></i>
            </div>
          </div>

          <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 12px;">
            <h2>Recent Activity</h2>
            <p style="color: var(--text-secondary); margin-top: 10px;">No recent activity to show.</p>
          </div>
        </div>

        <!-- Orders View -->
        <div id="orders-view" class="view-section">
          <h2>All Orders</h2>
          <p>Orders list will be displayed here.</p>
        </div>

        <!-- Products View -->
        <div id="products-view" class="view-section">
          <h2>All Products</h2>
          <p>Products list will be displayed here.</p>
        </div>

      </div>
    </main>
  </div>

  <script>
    function switchTab(viewId, element) {
      // Hide all views
      document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));

      // Show selected view
      const view = document.getElementById(viewId + '-view');
      if (view) view.classList.add('active');

      // Update sidebar active state
      document.querySelectorAll('.sidebar ul li').forEach(el => el.classList.remove('active'));
      if (element) element.classList.add('active');

      // Update Page Title
      const titles = {
        'dashboard': 'Dashboard',
        'orders': 'All Orders',
        'products': 'All Products'
      };
      document.getElementById('page-title').innerText = titles[viewId] || 'Dashboard';
    }
  </script>
</body>

</html>