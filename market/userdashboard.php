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

// Only fetch items with status 'approved'
$sql = "SELECT items.id, items.itemname, items.description, items.price, items.image, users.username
        FROM items
        JOIN users ON items.user_id = users.id
        WHERE items.status = 'approved'
        ORDER BY items.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard - Marketplace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #0b3d0b;  /* dark green */
            padding: 20px;
            color: white;
            margin: 0;
            position: relative;
            min-height: 100vh;
        }

        /* Dropdown fixed top right */
        .profile-dropdown {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .dropbtn {
            background-color: #1f6f1f;
            color: white;
            padding: 10px 16px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .dropbtn:hover, .dropbtn:focus {
            background-color: #298229;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #144214;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.7);
            border-radius: 5px;
            right: 0;
        }

        .dropdown-content a {
            color: #d4e6d4;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #276627;
        }

        .profile-dropdown:hover .dropdown-content {
            display: block;
        }

        /* Headings centered and pushed below dropdown */
        h2, h3 {
            text-align: center;
            color: #d4e6d4;
            margin-top: 70px;
        }

        /* Container for items */
        .items-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        /* Polaroid style item card */
        .item {
            background: #144214;
            width: 280px;
            padding: 15px 15px 25px 15px;
            box-shadow:
                0 10px 20px rgba(0,0,0,0.5),
                0 6px 6px rgba(0,0,0,0.4);
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
            font-family: 'Courier New', Courier, monospace;
            transition: 0.3s;
            color: #d4e6d4;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .item:hover {
            box-shadow:
                0 16px 40px rgba(0,0,0,0.7),
                0 8px 10px rgba(0,0,0,0.6);
        }

        .item img {
            width: 260px;
            height: 200px;
            border-radius: 4px;
            margin-bottom: 15px;
            object-fit: cover;
            box-shadow: 0 5px 10px rgba(0,0,0,0.5);
        }

        /* Details below image */
        .item-title {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 8px;
            color: #a5d6a7;
        }

        .item-price {
            color: #81c784;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .item-description {
            font-style: italic;
            margin-bottom: 12px;
            min-height: 50px;
            color: #c8e6c9;
        }

        .item-seller {
            font-size: 13px;
            color: #b2dfdb;
            margin-bottom: 15px;
        }

        /* Buy button full width beneath details */
        .item form button {
            background-color: #388e3c;
            border: none;
            color: white;
            padding: 10px 18px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .item form button:hover {
            background-color: #66bb6a;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .items-container {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
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
            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['itemname']); ?>" >
            <div class="item-title"><?php echo htmlspecialchars($row['itemname']); ?></div>
            <div class="item-price">$<?php echo number_format($row['price'], 2); ?></div>
            <div class="item-description"><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
            <div class="item-seller">Seller: <?php echo htmlspecialchars($row['username']); ?></div>
            <form method="post" action="buy_item.php" style="margin-top:10px;">
                <input type="hidden" name="item_id" value="<?php echo $row['id']; ?>">
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
