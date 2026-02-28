<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="Harvest Fresh: Organic fruits, vegetables, and farm-fresh products delivered directly from local farmers to your table."
    />
    <meta
      name="keywords"
      content="organic produce, fresh vegetables, farm-to-table, local farming, healthy food delivery"
    />
    <title>🌿Harvest Fresh</title>
    <link rel="stylesheet" href="./bootstrap.min.css" />
    <!-- <link rel="stylesheet" href="./style.css"> -->
    <!-- Removed empty link tag: <link rel="stylesheet" href="./"> -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap"
      rel="stylesheet"
    />

    <!-- CSS Start -->
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: Arial, sans-serif;
      }
      a {
        text-decoration: none;
      }

      /* Navbar */
      #navbar {
        color: #000;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f5fdf6;

        padding: 15px 20px;
        font-family: "Merriweather", serif;
      }

      #logo {
        color: #000000;
        font-size: 25px;
        font-weight: bold;
        font-family: "Merriweather", serif;
      }

      /* Links */
      #nav-links {
        list-style: none;
        display: flex;
      }

      #nav-links li {
        margin-left: 20px;
      }

      #nav-links a {
        text-decoration: none;
        color: rgb(0, 0, 0);
        font-size: 20px;
      }

      #nav-links a:hover {
        color: #ffffff;
      }

      /* Hamburger */
      #menu-icon {
        display: none;
        font-size: 26px;
        color: rgb(0, 0, 0);
        cursor: pointer;
      }

      /* Hero Section */
      .hero {
        width: 100%;
        height: 100vh;
        background:
          linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)),
          url("https://imgs.search.brave.com/Fu4G0ETI9ENFcHRrV81Ej-8-PfifYX64o7WqGLdq7N8/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9zdGF0/aWMudmVjdGVlenku/Y29tL3N5c3RlbS9y/ZXNvdXJjZXMvdGh1/bWJuYWlscy8wNDkv/MDAzLzk1Ni9zbWFs/bC9mcmVzaC1mcnVp/dHMtYW5kLXZlZ2V0/YWJsZXMtd2l0aC1i/bGFuay1zcGFjZS1w/aG90by5qcGc");
        /* change image path */
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 20px;
      }

      #maintext {
        color: black;
        font-family: "Merriweather", serif;
      }

      .hero-content {
        max-width: 700px;
        color: #fff;
        font-family: "Merriweather", serif;
      }

      .hero-content h1 {
        font-size: 48px;
        margin-bottom: 15px;
        font-weight: bold;
      }

      .hero-content p {
        font-size: 18px;
        margin-bottom: 25px;
        line-height: 1.6;
        font-family:
          "Gill Sans", "Gill Sans MT", Calibri, "Trebuchet MS", sans-serif;
      }

      .btn {
        display: inline-block;
        padding: 12px 30px;
        background-color: #4caf50;
        color: #fff;
        text-decoration: none;
        font-size: 16px;
        border-radius: 30px;
        transition: background 0.3s ease;
      }

      .btn:hover {
        background-color: #3e8e41;
      }

      /* Responsive */
      @media (max-width: 768px) {
        .hero-content h2 {
          font-size: 34px;
        }

        #menu-icon {
          display: block;
        }

        #nav-links {
          display: none;
        }

        .hero-content p {
          font-size: 16px;
        }
      }

      @media (max-width: 480px) {
        .hero-content h2 {
          font-size: 28px;
        }

        .btn {
          padding: 10px 24px;
          font-size: 14px;
        }
      }

      /*  */
      /* Common */
      .container {
        width: 90%;
        max-width: 1100px;
        margin: auto;
      }

      section {
        padding: 60px 0;
      }

      h2 {
        text-align: center;
        margin-bottom: 40px;
        font-size: 36px;
        color: #2e7d32;
      }

      /* About Section */
      .about-content {
        display: flex;
        align-items: center;
        gap: 30px;
      }

      .about-content img {
        width: 45%;
        border-radius: 10px;
      }

      .about-content p {
        font-size: 17px;
        line-height: 1.7;
        color: #555;
      }

      /* Services Section */
      .services {
        background: #f5fdf6;
      }

      .cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
      }

      .card {
        background: #fff;
        padding: 25px;
        text-align: center;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
      }

      .card:hover {
        transform: translateY(-8px);
      }

      .card img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 15px;
      }

      .card h3 {
        color: #388e3c;
        margin-bottom: 10px;
      }

      .card p {
        font-size: 15px;
        color: #666;
      }

      /* Contact Section */
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
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
      }

      .contact textarea {
        resize: none;
        height: 120px;
      }

      .contact button {
        padding: 12px;
        background: #4caf50;
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 16px;
        cursor: pointer;
      }

      .contact button:hover {
        background: #388e3c;
      }

      /* Responsive */
      @media (max-width: 768px) {
        .about-content {
          flex-direction: column;
          text-align: center;
        }

        .about-content img {
          width: 100%;
        }

        .cards {
          grid-template-columns: 1fr;
        }
      }

      /* ================= FOOTER ================= */
      footer {
        background-color: #000;
        color: #fff;
        padding: 40px 20px;
      }

      footer h5 {
        margin-bottom: 15px;
      }

      footer a {
        color: #fff;
        text-decoration: none;
      }

      footer a:hover {
        text-decoration: underline;
      }
    </style>
    <!-- css End -->
  </head>

  <body>
    <!-- Header -->
    <nav id="navbar" class="bg-success">
      <div id="logo">🌿Harvest Fresh</div>

      <div id="menu-icon" onclick="toggleMenu()">☰</div>

      <ul id="nav-links">
        <li><a href="#home">Home</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#services">Services</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
      <div class="hero-content">
        <h1 id="mainText">Fresh From Farm to Your Table</h1>
        <p>
          Organic fruits, vegetables, and farm-fresh products delivered with
          care.
        </p>
        <a href="./login.php" class="btn">Shop Fresh</a>
      </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
      <div class="container">
        <h2>About Harvest Fresh</h2>
        <div class="about-content">
          <img
            src="https://imgs.search.brave.com/a33Qg1ExK95rssY-oKkCWv2ON7mlQp_xN0e7N8BKrX4/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9hcGVkYS5nb3YuaW4vc2l0ZXMvZGVmYXVsdC9maWxlcy9pbWFnZXNfcGxhaW5fZmllbGQvb2ZmLWZydWl0cy5qcGc"
            alt="Fresh farm produce including various fruits and vegetables"
          />
          <p>
            Harvest Fresh is committed to delivering organic fruits, vegetables,
            and farm products directly from local farmers to your home. We
            believe in healthy living, sustainable farming, and fresh food you
            can trust.
          </p>
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
      <div class="container">
        <h2>What We Offer</h2>

        <div class="cards">
          <a href="./login.php">
            <div class="card">
              <img
                src="https://imgs.search.brave.com/GVZKzl3BWeHTlYKbf4IjloTo0BSmQaLs-nP1y9xKl5A/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9zdGF0/aWMudmVjdGVlenku/Y29tL3N5c3RlbS9y/ZXNvdXJjZXMvdGh1/bWJuYWlscy8wNTUv/MDMwLzU0Ny9zbWFs/bC9hLWJhc2tldC1v/Zi12ZWdldGFibGVz/LXBuZy5wbmc"
                alt="Basket of fresh vegetables including potatoes, tomatoes, and onions"
              />
              <h3>Fresh Vegetables</h3>
              <p>
                Handpicked seasonal vegetables grown using natural and organic
                farming methods.
              </p>
            </div>
          </a>

          <a href="./login.php">
            <div class="card">
              <img
                src="https://imgs.search.brave.com/FlxMF05-1rH248VBIZmwV82CcO95weY6cNshZrvQbeE/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9zdGF0/aWMudmVjdGVlenku/Y29tL3N5c3RlbS9y/ZXNvdXJjZXMvdGh1/bWJuYWlscy8wMjcv/NzkzLzY3My9zbWFs/bC9mcmVzaC1mcnVp/dHMtZmFsbGluZy1p/bi13YXRlci1zcGxh/c2gtYWktZ2VuZXJl/dGl2ZS1mcmVlLXBo/b3RvLmpwZw"
                alt="Basket full of fresh fruits including bananas, apples, and grapes"
              />
              <h3>Organic Fruits</h3>
              <p>
                Juicy, chemical-free fruits sourced directly from trusted local
                farms.
              </p>
            </div>
          </a>

          <div class="card">
            <img
              src="https://imgs.search.brave.com/9OCRlzxkc_wr6-1fu0xIIYFHGOJ1k3xYyRZLM54_DUo/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9zdGF0/aWMudmVjdGVlenku/Y29tL3N5c3RlbS9y/ZXNvdXJjZXMvdGh1/bWJuYWlscy8wNTAv/NDM3Lzc5Ny9zbWFs/bC9mcmVzaC1mcnVp/dC1kZWxpdmVyeS1z/ZXJ2aWNlLXRydWNr/LWlsbHVzdHJhdGlv/bi12ZWN0b3IuanBn"
              alt="Illustration of a truck delivering fresh fruit"
            />
            <h3>Fast Delivery</h3>
            <p>
              Quick and safe delivery ensuring freshness from farm to your
              doorstep.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
      <div class="container">
        <h2>Get In Touch</h2>
        <form id="contactForm" onsubmit="handleSubmit(event)">
          <input type="text" placeholder="Your Name" required />
          <input type="email" placeholder="Your Email" required />
          <textarea placeholder="Your Message" required></textarea>
          <button type="submit">Send Message</button>
        </form>
      </div>
    </section>

    <!-- ================= FOOTER ================= -->
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
              <li>
                <a class="text-white" href="#" aria-label="Facebook">
                  <i>Facebook</i>
                </a>
              </li>
              <li>
                <a class="text-white" href="#" aria-label="Instagram"
                  ><i>Instagram</i></a
                >
              </li>
              <li>
                <a class="text-white" href="#" aria-label="Twitter"
                  ><i>Twitter</i></a
                >
              </li>
            </ul>
          </div>
        </div>

        <hr />
        <p class="text-center mb-0">&copy; 2024 Harvest Fresh</p>
      </div>
    </footer>
    <!-- ================= FOOTER END ================= -->

    <script>
      function toggleMenu() {
        document.getElementById("nav-links").classList.toggle("show");
      }

      function handleSubmit(event) {
        event.preventDefault(); // Prevent actual form submission
        alert("Thank you for your message! We'll get back to you soon.");
        // In a real app, you'd send this data to a server via AJAX or similar
      }
    </script>
  </body>
</html>
