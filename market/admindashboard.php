<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "market", 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['item_id'])) {
    $itemId = (int)$_POST['item_id'];
    $action = $_POST['action'];

    if ($action === 'approved') {
        $conn->query("UPDATE items SET status='approved' WHERE item_id=$itemId");
    } elseif ($action === 'rejected') {
        $conn->query("DELETE FROM items WHERE item_id=$itemId");
    }
}

$pendingItems = $conn->query("SELECT items.*, users.username FROM items JOIN users ON items.user_id = users.id WHERE items.status='pending' ORDER BY items.item_id DESC");
$users = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admindashboard.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="#" onclick="showSection('pending')">📦 Pending Items</a>
    <a href="#" onclick="showSection('users')">👥 Users</a>
    <a href="login.php">🚪 Logout</a>
</div>

<div class="content">
    <div id="pending" class="section active">
        <h2>Pending Items for Approval</h2>
        <?php if ($pendingItems->num_rows === 0): ?>
            <p>No pending items.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Price (USD)</th>
                        <th>Image</th>
                        <th>Seller</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $pendingItems->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['itemname']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($item['description'])); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <?php if ($item['image']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Item Image">
                                <?php else: ?>
                                    No image
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['username']); ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                    <button type="submit" name="action" value="approved">Approve</button>
                                    <button type="submit" name="action" value="rejected" class="danger-btn" onclick="return confirm('Reject this item?');">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div id="users" class="section">
        <h2>Registered Users</h2>
        <?php if ($users->num_rows === 0): ?>
            <p>No users found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Password</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['password']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script src="admindashboard.js"></script>
</body>
</html>
