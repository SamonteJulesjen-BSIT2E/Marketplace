<?php
session_start();

$conn = new mysqli("localhost", "root", "", "jj");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $phone = trim($_POST['phone']);
    $role = 'user';

    if (empty($email) || empty($firstname) || empty($lastname) || empty($phone)) {
        $error = "All fields are required.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
            $stmt->close();
        } else {
            $stmt->close();

            // Insert new user
            $insert = $conn->prepare("INSERT INTO users (Email, Firstname, Lastname, phone, role) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("sssss", $email, $firstname, $lastname, $phone, $role);

            if ($insert->execute()) {
                $success = "Registration successful!";
            } else {
                $error = "Error during registration: " . $conn->error;
            }

            $insert->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SIGN-UP</title>
    <link rel="stylesheet" href="../Styles/register.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <form action="" method="POST">
            <h1>SIGN-UP</h1>

            <?php if (!empty($error)) : ?>
                <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <?php if (!empty($success)) : ?>
                <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
                <i class="bx bxs-envelope"></i>
            </div>

            <div class="input-box">
                <input type="text" name="firstname" placeholder="Firstname" required>
                <i class="bx bxs-user"></i>
            </div>

            <div class="input-box">
                <input type="text" name="lastname" placeholder="Lastname" required>
                <i class="bx bxs-user"></i>
            </div>

            <div class="input-box">
                <input type="text" name="phone" placeholder="Phone Number" required>
                <i class="bx bxs-phone"></i>
            </div>

            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class="bx bxs-lock-alt"></i>
            </div>

            <div class="input-box">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <i class="bx bxs-lock-alt"></i>
            </div>

            <button type="submit" class="btn">Register</button>

            <div class="login-link">
                <p>Already have an account? <a href="login.php">Log in</a></p>
            </div>
        </form>
    </div>
    <div class="footer">
        <p>&copy; 2025 KUPAL. All Rights Reserved.</p>
    </div>
</body>
</html>
