<?php
session_start();

$conn = new mysqli("localhost", "root", "", "market", 3306,"/data/data/com.termux/files/usr/var/run/mysqld.sock");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Login process
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admindashboard.php");
        } else {
            header("Location: userdashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
<h2>Login</h2>
<?php if (!empty($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
<form method="post" action="">
    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <input type="submit" name="login" value="Login">
</form>
</body>
</html>
