
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🌿Harvest Fresh</title>

  <!-- Bootstrap CSS CDN -->
  <style>
      #container{
          width: 50px;
          height: 50px;
          border : 1px solid rgb(255, 255, 255);
          background: linear-gradient(rgb(247, 140, 140),rgb(117, 117, 241));
          border-radius: 50%;
          position: fixed;
          left: 1620px;
          cursor: pointer;
        }#container h1{
            text-align: center;
            font-size: 35px;
            font-weight:100;
            font-family: 'Courier New', Courier, monospace;
        }.dropdown-toggle::after {
    display: none !important;
}
        </style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    
    <!-- Logo / Brand -->
    <a class="navbar-brand fw-bold" href="#">🌿Harvest Fresh</a>

    <!-- Toggle Button (Mobile) -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
            data-bs-target="#navbarNav" aria-controls="navbarNav" 
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">

        <li class="nav-item">
          <a class="nav-link active" href="#">Home</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="#">Shop</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="#">About</a>
        </li>

        <!-- Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" 
             data-bs-toggle="dropdown">
            Categories
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Fruits</a></li>
            <li><a class="dropdown-item" href="#">Vegetables</a></li>
            <li><a class="dropdown-item" href="#">Organic Items</a></li>
          </ul>
        </li>

      </ul>

      <!-- Search Form -->
      
      <div id="container">
         <h1 class="dropdown-toggle"  role="button" 
            data-bs-toggle="dropdown" aria-expanded="false">R</h1>
    
           <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="account.php">View Account</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
<!-- Navbar End -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>