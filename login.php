<?php
session_start();

$conn = new mysqli("localhost", "root", "", "jj");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            $_SESSION["uid"] = $row['user_id'];
            $_SESSION["email"] = $row['Email'];
            $_SESSION["role"] = $row['role'];

            if ($row['role'] === 'admin') {
                header("Location: dashboard1.php");
            } else {
                header("Location: home_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password!";
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Form</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="login.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<nav>
  <ul class="links">
    <b style="color: white">Online</b>
    <b style="color: white">Marketplace</b>
    <b style="color: white">System</b>
  </ul>
</nav>

<div class="wrapper">
  <form action="login.php" method="POST">
    <h1>SIGN-IN</h1>

    <?php if (!empty($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

    <div class="input-box">
      <input type="email" name="email" placeholder="Email" required>
      <i class='bx bxs-envelope'></i>
    </div>

    <div class="input-box">
      <input type="password" name="password" placeholder="Password" required>
      <i class='bx bxs-lock-alt'></i>
    </div>

    <div class="remember-forgot">
      <label><input type="checkbox">Remember Me</label>
      <a href="#">Forgot Password</a>
    </div>

    <button type="submit" class="btn">Login</button>

    <div class="register-link">
      <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
  </form>
</div>

<div class="footer">
  <p>&copy; 2025 Fashions. All Rights Reserved.</p>
</div>

</body>
</html>
