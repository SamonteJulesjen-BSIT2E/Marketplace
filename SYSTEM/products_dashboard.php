<?php
$categories = [
  'clothes' => ['T-Shirt', 'Jacket', 'Jeans'],
  'electronics' => ['Smartphone', 'Laptop', 'Headphones'],
  'essentials' => ['Toothpaste', 'Shampoo', 'Soap']
];
$currentCategory = $_GET['category'] ?? 'clothes';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Product Dashboard</title>
  <link rel="stylesheet" href="Products.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
  <header class="navbar">
    <h1>🛒 Product Dashboard</h1>
    <nav class="category-buttons">
      <a href="?category=clothes" class="<?= $currentCategory === 'clothes' ? 'active' : '' ?>">👕 Clothes</a>
      <a href="?category=electronics" class="<?= $currentCategory === 'electronics' ? 'active' : '' ?>">💻 Electronics</a>
      <a href="?category=essentials"a class="<?= $currentCategory === 'essentials' ? 'active' : '' ?>">🧼 Essentials</a>
    </nav>
  </header>

  <main>
    <section class="products">
      <h2><?= ucfirst($currentCategory) ?></h2>
      <div class="product-grid">
        <?php foreach ($categories[$currentCategory] as $product): ?>
          <div class="product-card">
            <h3><?= htmlspecialchars($product) ?></h3>
            <button class="btn">View</button>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>
</body>
</html>
