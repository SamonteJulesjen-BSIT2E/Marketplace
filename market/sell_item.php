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
$userQuery = $conn->prepare("SELECT id FROM users WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();
if ($userResult->num_rows === 0) {
    die("User not found.");
}
$user = $userResult->fetch_assoc();
$user_id = $user['id'];

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sell'])) {
    $itemname = trim($_POST['itemname'] ?? "");
    $description = trim($_POST['description'] ?? "");
    $price = trim($_POST['price'] ?? "");

    if (empty($itemname) || empty($price)) {
        $error = "Item name and price are required.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Price must be a positive number.";
    }

    if (!isset($_FILES['image'])) {
        $error = "No image file uploaded.";
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
            UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.",
            UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
            UPLOAD_ERR_NO_FILE => "No file was uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload.",
        ];
        $error = $uploadErrors[$_FILES['image']['error']] ?? "Unknown upload error.";
    }

    if (!$error) {
        $image_name = basename($_FILES['image']['name']);
        $target_dir = "uploads/";  // use a relative folder like your movie code
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO items (user_id, itemname, description, price, image, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("issds", $user_id, $itemname, $description, $price, $image_name);
            if ($stmt->execute()) {
                $success = "Item listed successfully! Awaiting admin approval.";
            } else {
                $error = "Database error: " . $stmt->error;
                unlink($target_file);  // remove file if DB insert fails
            }
            $stmt->close();
        } else {
            $error = "Failed to upload image file.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sell Item</title>
    <style>
        /* Minimal styling */
        body { max-width: 600px; margin: 30px auto; font-family: Arial, sans-serif; }
        label { display: block; margin-top: 10px; }
        input[type=text], input[type=number], textarea, input[type=file] { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        input[type=submit] { margin-top: 15px; padding: 10px 20px; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<h2>List an Item for Sale</h2>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php elseif ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <label for="itemname">Item Name *</label>
    <input type="text" id="itemname" name="itemname" required>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="4"></textarea>

    <label for="price">Price (USD) *</label>
    <input type="number" id="price" name="price" min="0" step="0.01" required>

    <label for="image">Item Image *</label>
    <input type="file" id="image" name="image" accept="image/*" required>

    <input type="submit" name="sell" value="List Item">
</form>

</body>
</html>
