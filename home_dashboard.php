<?php
$conn = new mysqli("localhost", "root", "", "jj");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

session_start();


$view = isset($_GET['view']) ? $_GET['view'] : 'marketplace';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['product_id'])) {
        $productId = (int)$_POST['product_id'];
        $_SESSION['cart'][] = $productId;
        header("Location: " . $_SERVER['PHP_SELF'] . "?view=cart");
        exit();
    }

    if (!empty($_POST['name']) && !empty($_POST['price']) && !empty($_POST['description'])) {
        $name = $conn->real_escape_string($_POST["name"]);
        $price = (float)$_POST["price"];
        $description = $conn->real_escape_string($_POST['description']);
        $userId = (int)$_SESSION["user_id"];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $imageName = uniqid() . "_" . basename($_FILES['image']['name']);
    $targetDir = "uploads/";
    $targetFile = $targetDir . $imageName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO products (product_name, user_id, price, Description, Image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sidss", $name, $userId, $price, $description, $imageName);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?view=marketplace");
            exit();
        } else {
            echo "<script>alert('Database Insert Failed: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Failed to upload image.');</script>";
    }
} else {
    echo "<script>alert('Please upload an image.');</script>";
}
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>IntegraTrade Marketplace</title>
    <link rel="stylesheet" href="home_dashboard.css">
</head>
<body>

<div class="sidebar">
    <h2>2Eforlife</h2>
    <a href="?view=marketplace">Marketplace</a>
    <a href="?view=cart">My Cart</a>
    <a href="?view=add">Add Product</a>
    <a href="login.php">Logout</a>
</div>

<div class="content">
    <?php if ($view == 'marketplace'): ?>
        <h1>Marketplace</h1>
        <form method="GET" class="search-form">
            <input type="hidden" name="view" value="marketplace">
            <input type="text" name="search" placeholder="Search product..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Search</button>
        </form>
        <div class="marketplace">
            <?php
            $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
            $sql = "SELECT * FROM products";
            if ($search !== '') {
                $sql .= " WHERE product_name LIKE '%$search%'";
            }
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()):
            ?>
                <div class="product-card">
                    <img src="uploads/<?php echo htmlspecialchars($row['Image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                        <p>₱<?php echo number_format($row['price'], 2); ?></p>
                        <small><?php echo htmlspecialchars($row['Description']); ?></small>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo (int)$row['product_id']; ?>">
                            <button type="submit" class="btn">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php endwhile;
            } else {
                echo "<p>No products found.</p>";
            }
            ?>
        </div>

    <?php elseif ($view == 'cart'): ?>
        <h1>My Cart</h1>
        <?php
        if (!empty($_SESSION['cart'])) {
            $cartIds = implode(",", $_SESSION['cart']);
            $cartResult = $conn->query("SELECT * FROM products WHERE product_id IN ($cartIds)");
            while ($cartItem = $cartResult->fetch_assoc()) {
                echo "<p><strong>" . htmlspecialchars($cartItem['product_name']) . "</strong> - ₱" . number_format($cartItem['price'], 2) . "</p>";
            }
        } else {
            echo "<p>Your cart is empty.</p>";
        }
        ?>

    <?php elseif ($view == 'add'): ?>
        <h1>Add New Product</h1>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Product Name" required>
            <input type="number" name="price" step="0.01" placeholder="Price" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="file" name="image" accept="image/*" required>
            <button type="submit">Add Product</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
