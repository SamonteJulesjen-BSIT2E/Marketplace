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

$username = $_SESSION['username'];

// Get user ID from username
$stmt = $conn->prepare("SELECT id, location FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();
$user_id = $user['id'];
$user_location = $user['location'];

$stmt->close();

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_location = trim($_POST['location']);

    $stmt = $conn->prepare("UPDATE users SET location = ? WHERE id = ?");
    $stmt->bind_param("si", $new_location, $user_id);
    if ($stmt->execute()) {
        $user_location = $new_location;
        $message = "Profile updated successfully.";
    } else {
        $message = "Error updating profile.";
    }
    $stmt->close();
}

// Handle delete item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $delete_item_id = intval($_POST['item_id']);

    // Verify the item belongs to user before deleting
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_item_id, $user_id);
    $stmt->execute();
    $deleted = $stmt->affected_rows > 0;
    $stmt->close();

    if ($deleted) {
        $message = "Item deleted successfully.";
    } else {
        $message = "Failed to delete item.";
    }
}

// Fetch user's items
$stmt = $conn->prepare("SELECT id, itemname, description, price, image FROM items WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Profile & Your Items</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .message { padding: 10px; background-color: #e0ffe0; border: 1px solid #0a0; margin-bottom: 15px; }
        .error { background-color: #ffe0e0; border-color: #a00; }
        form { margin-bottom: 30px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { padding: 8px; width: 300px; margin-bottom: 15px; }
        button { padding: 8px 15px; cursor: pointer; }
        .items-container { display: flex; flex-wrap: wrap; gap: 15px; }
        .item {
            border: 1px solid #ddd;
            padding: 10px;
            width: 250px;
            border-radius: 5px;
            box-sizing: border-box;
            position: relative;
        }
        .item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 3px;
        }
        .item-title {
            font-weight: bold;
            margin-top: 8px;
            font-size: 1.1em;
        }
        .item-price {
            color: green;
            font-weight: bold;
            margin: 5px 0;
        }
        .delete-form {
            position: absolute;
            top: 5px;
            right: 5px;
        }
        .delete-form button {
            background-color: #c00;
            border: none;
            color: white;
            font-weight: bold;
            padding: 3px 7px;
            border-radius: 3px;
        }
    </style>
</head>
<body>

<h2>Update Your Profile</h2>

<?php if (!empty($message)): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<form method="post" action="">
    <label for="location">Location (for delivery):</label>
    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($user_location); ?>" required>
    <button type="submit" name="update_profile">Update Profile</button>
</form>

<h2>Your Items for Sale</h2>

<?php if ($items_result->num_rows > 0): ?>
    <div class="items-container">
        <?php while ($item = $items_result->fetch_assoc()): ?>
            <div class="item">
                <?php if (!empty($item['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['itemname']); ?>">
                <?php else: ?>
                    <img src="placeholder.png" alt="No image available">
                <?php endif; ?>
                <div class="item-title"><?php echo htmlspecialchars($item['itemname']); ?></div>
                <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                <div class="item-description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></div>

                <form method="post" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this item?');">
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                    <button type="submit" name="delete_item">X</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>You have no items listed for sale.</p>
<?php endif; ?>

<p><a href="userdashboard.php">Back to Dashboard</a></p>

</body>
</html>
