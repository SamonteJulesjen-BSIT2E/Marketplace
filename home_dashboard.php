<?php
$conn = new mysqli("localhost", "root", "", "jj");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['product_id'])) {
        $productId = (int) $_POST['product_id'];
        $_SESSION['cart'][] = $productId;
    } elseif (isset($_POST['name']) && isset($_POST['price']) && isset($_POST['description'])) {
        $name        = $conn->real_escape_string($_POST["name"]);
        $price       = (float) $_POST["price"];
        $description = $conn->real_escape_string($_POST["description"]);
        $userId      = (int) $_SESSION['user_id'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $imageName  = uniqid() . "_" . basename($_FILES['image']['name']);
            $targetDir  = "uploads/";
            $targetFile = $targetDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $insertQuery = "INSERT INTO products (product_name, user_id, price, Description, Image)
                                VALUES ('$name', $userId, $price, '$description', '$imageName')";
                if ($conn->query($insertQuery)) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "#products");
                    exit();
                } else {
                    echo "<script>alert('Failed to add product.');</script>";
                }
            } else {
                echo "<script>alert('Failed to upload image.');</script>";
            }
        } else {
            echo "<script>alert('No image uploaded.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Marketplace</title>
  <link rel="stylesheet" href="home_dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script>
    function scrollToSection(id) {
      document.getElementById(id).scrollIntoView({ behavior: 'smooth' });
    }
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
</head>
<body>
<div class="sidebar">
  <a href="#products" class="sidebar-link">🛍️ Products</a>
  <a href="#sell" class="sidebar-link">💼 Sell</a>
  <a href="#orders" class="sidebar-link">📦 Orders</a>
</div>

<header class="navbar">
  <div class="navbar-left">
    <div class="logo">🛒 IntegraTrade</div>
    <div class="profile">Profile</div>
  </div>
  <form class="navbar-search" method="GET" action="#products" onsubmit="handleSearch(event)">
    <input type="text" id="searchInput" placeholder="Search products...">
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
  <div class="product-cards">
    <?php
    $productListQuery = "SELECT p.product_id, p.product_name, p.price, p.Image, u.firstname 
                         FROM products p 
                         LEFT JOIN users u ON p.user_id = u.user_id 
                         ORDER BY p.product_id DESC LIMIT 6";
    $productListResult = $conn->query($productListQuery);
    if ($productListResult && $productListResult->num_rows > 0):
      while ($product = $productListResult->fetch_assoc()):
    ?>
      <div class="product-card">
        <img src="uploads/<?= htmlspecialchars($product['Image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
        <div class="product-overlay">
          <h3><?= htmlspecialchars($product['product_name']) ?></h3>
          <p>₱<?= number_format($product['price'], 2) ?></p>
          <small>Seller: <?= htmlspecialchars($product['firstname'] ?? 'Unknown') ?></small>
          <form method="POST">
            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
            <button class="btn" type="submit">Add to Cart</button>
          </form>
        </div>
      </div>
    <?php endwhile; else: echo "<p>No products available.</p>"; endif; ?>
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
      $cartQuery = "SELECT product_name, price FROM products WHERE product_id IN ($cartIds)";
      $cartResult = $conn->query($cartQuery);
      if ($cartResult && $cartResult->num_rows > 0):
        while ($item = $cartResult->fetch_assoc()):
    ?>
      <li><?= htmlspecialchars($item['product_name']) ?> - ₱<?= number_format($item['price'], 2) ?></li>
    <?php endwhile; else: echo "<li>No items found in the database.</li>"; endif; ?>
    </ul>
  <?php endif; ?>
</section>

<section id="about">
  <h2>About Us</h2>
  <p>MarketDash is a platform connecting buyers and sellers across a wide range of categories.</p>
</section>

<section id="sell">
  <h2>Sell a Product</h2>
  <form method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Product Name" required>
    <input type="number" name="price" step="0.01" placeholder="Price" required>
    <textarea name="description" placeholder="Description" required></textarea>
    <input type="file" name="image" accept="image/*" required>
    <button class="btn" type="submit">List Product</button>
  </form>
</section>

<section id="orders">
  <h2>Your Orders</h2>
  <p>(Order management coming soon...)</p>
</section>
</main>
</body>
</html>
