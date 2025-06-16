<?php

$host = 'localhost';
$db   = 'jj';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int) $_POST['product_id'];
    $_SESSION['cart'][] = $productId;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Marketplace</title>
  <link rel="stylesheet" href="home_dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <script>
    function scrollToSection(id) {
      document.getElementById(id).scrollIntoView({ behavior: 'smooth' });
    }
  </script>
</head>
<body>
  <div class="sidebar">
  
  <a href="Products.php" class="sidebar-link">🛍️ Products</a>

    <a href="#sell" class="sidebar-link">💼 Sell</a>
    <a href="#orders" class="sidebar-link">📦 Orders</a>
  </div>

  <header class="navbar">
    <div class="navbar-left">
      <div class="logo">🛒 IntegraTrade</div>
      <div class="profile">Profile</div>
      <i class='bx bxs-user'></i>
    </div>

    <form class="navbar-search" method="GET" action="#products" onsubmit="handleSearch(event)">
    <input type="text" name="q" id="searchInput" placeholder="Search products...">
    <button type="submit">🔍</button>
  </form>

    <nav class="navbar-right">
      <a href="#home" onclick="scrollToSection('home')">Home</a>
    

      <a href="#products" onclick="scrollToSection('products')">Products</a>
      <a href="#cart" onclick="scrollToSection('cart')">Cart (<?= count($_SESSION['cart']) ?>)</a>
      <a href="#about" onclick="scrollToSection('about')">About</a>
    </nav>
  </header>

  <main>
  <section id="home">
  <h1>Welcome to IntegraTrade!</h1>
  <p>Your one-stop marketplace for amazing products.</p>
  <img src="welcome.jpg" alt="Welcome Banner" class="welcome-img">
</section>


    <section id="products">
      <h2>Featured Products</h2>
      <img src="shoes1.jpeg" alt="Welcome Banner" class="welcome-img">
      <div class="product-cards">
        <section class="featured-products">
        
  <h2>Featured Products</h2>
  <div class="product-card">
    <img src="shoes3.jpeg" alt="Product Image" class="product-img" />
    <div class="product-overlay">
      <h3>Nike Air Max Portal</h3>
      <p>Step into the future with Nike Air Max Portal where style meets next-gen comfort. Bold design. Limitless energy.</p>
      <button class="btn">View Details</button>
    </div>
  </div>
</section>

        <?php
        $stmt = $pdo->query("SELECT product_id, product_name, price FROM products LIMIT 6");
        while ($product = $stmt->fetch()):
        ?>
          <div class="product-card">
            <h3><?= htmlspecialchars($product['product_name']) ?></h3>
            <p>₱<?= number_format($product['price'], 2) ?></p>
            <form method="POST">
              <input type="hidden" name="id" value="<?= $product['product_id'] ?>">
              <button class="btn" type="submit">Add to Cart</button>
            </form>
          </div>
        <?php endwhile; ?>
      </div>
    </section>

    <section id="cart">
      <h2>Your Cart</h2>
      <?php if (empty($_SESSION['cart'])): ?>
        <p>Your cart is empty.</p>
      <?php else: ?>
        <ul>
        <?php
          $cartIds = implode(',', array_map('intval', $_SESSION['cart']));
          $stmt = $pdo->query("SELECT product_name, price FROM products WHERE product_id IN ($cartIds)");
          while ($item = $stmt->fetch()):
        ?>
          <li><?= htmlspecialchars($item['name']) ?> - ₱<?= number_format($item['price'], 2) ?></li>
        <?php endwhile; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section id="about">
      <h2>About Us</h2>
      <p>MarketDash is a platform connecting buyers and sellers across a wide range of categories.</p>
    </section>

    <section id="sell">
      <h2>Sell a Product</h2>
      <form method="POST" action="add_product.php">
        <input type="text" name="name" placeholder="Product Name" required>
        <input type="number" name="price" step="0.01" placeholder="Price" required>
        <button class="btn" type="submit">List Product</button>
      </form>
    </section>

    <section id="orders">
      <h2>Your Orders</h2>
      <p>(Order management coming soon...)</p>
    </section>
  </main>
  <script>
  function handleSearch(event) {
    event.preventDefault();
    const query = document.getElementById('searchInput').value.trim().toLowerCase();

    const products = document.querySelectorAll('.product-card');
    products.forEach(card => {
      const name = card.querySelector('h3').textContent.toLowerCase();
      card.style.display = name.includes(query) ? 'block' : 'none';
    });

    scrollToSection('products');
  }
</script>

</body>
</html>
