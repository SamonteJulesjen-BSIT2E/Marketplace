<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "market", 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Fetch items using the correct primary key column name 'item_id'
$sql = "SELECT items.item_id, items.itemname, items.description, items.price, items.image, users.username
        FROM items
        JOIN users ON items.user_id = users.id
        WHERE items.status = 'approved'
        ORDER BY items.item_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard - Marketplace</title>
    <link rel="stylesheet" href="userdashboard.css">
</head>
<body>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

<div class="profile-dropdown">
    <button class="dropbtn"><?php echo htmlspecialchars($_SESSION['username']); ?> ▼</button>
    <div class="dropdown-content">
        <a href="update_profile.php">Update Info</a>
        <a href="sell_item.php">Sell Item</a>
        <a href="my_orders.php">My Orders</a>
        <a href="login.php">Logout</a>
    </div>
</div>

<h3>Marketplace Items for Sale</h3>

<div class="items-container">
<?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="item">
            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['itemname']); ?>">
            <div class="item-title"><?php echo htmlspecialchars($row['itemname']); ?></div>
            <div class="item-price">$<?php echo number_format($row['price'], 2); ?></div>
            <div class="item-description"><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
            <div class="item-seller">Seller: <?php echo htmlspecialchars($row['username']); ?></div>
            
            <!-- ✅ Use item_id instead of id -->
            <form method="post" action="buy_item.php" style="margin-top:10px;">
                <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
                <button type="submit">Buy</button>
            </form>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="text-align:center;">No items for sale right now.</p>
<?php endif; ?>
</div>

</body>
</html>
