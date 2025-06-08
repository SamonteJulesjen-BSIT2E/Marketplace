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

// Handle approval actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['item_id'])) {
    $itemId = (int)$_POST['item_id'];
    $action = $_POST['action'];

    if ($action === 'approved') {
        $conn->query("UPDATE items SET status='approved' WHERE id=$itemId");
    } elseif ($action === 'rejected') {
        $conn->query("DELETE FROM items WHERE id=$itemId");
    }
}

// Fetch pending items
$pendingItems = $conn->query("SELECT items.*, users.username FROM items JOIN users ON items.user_id = users.id WHERE items.status='pending' ORDER BY items.id DESC");

// Fetch users (excluding admins)
$users = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #0f2e1e;
            margin: 0;
            display: flex;
            color: #f5f5f5;
        }
        .sidebar {
            width: 220px;
            background: #14532d;
            color: #fff;
            padding: 20px;
            height: 100vh;
        }
        .sidebar h2 {
            font-size: 22px;
            margin-bottom: 25px;
            border-bottom: 1px solid #2f855a;
            padding-bottom: 10px;
        }
        .sidebar a {
            color: #f5f5f5;
            display: block;
            margin: 18px 0;
            text-decoration: none;
            font-size: 16px;
        }
        .sidebar a:hover {
            text-decoration: underline;
        }
        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        h2 {
            color: #f5f5f5;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #1e3a2a;
            margin-bottom: 40px;
        }
        th, td {
            border: 1px solid #35654a;
            padding: 10px;
            text-align: left;
            color: #e2e8f0;
        }
        th {
            background: #166534;
        }
        img {
            max-width: 80px;
            height: auto;
            border-radius: 4px;
        }
        button {
            padding: 6px 12px;
            background: #16a34a;
            border: none;
            color: #fff;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background: #22c55e;
        }
        .danger-btn {
            background: #dc2626;
        }
        .danger-btn:hover {
            background: #f87171;
        }
        form {
            display: inline;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
    </style>
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
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
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

<script>
    function showSection(sectionId) {
        document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
        document.getElementById(sectionId).classList.add('active');
    }
</script>

</body>
</html>
