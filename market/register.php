<?php
session_start();

$conn = new mysqli("localhost", "root", "", "market", 3306, "/data/data/com.termux/files/usr/var/run/mysqld.sock");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Registration process
if (isset($_POST['register'])) {
    $username        = mysqli_real_escape_string($conn, $_POST['username']);
    $email           = mysqli_real_escape_string($conn, $_POST['email']);
    $password        = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Check if username exists
    $checkQuery = "SELECT * FROM users WHERE username='$username'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $error = "Username already taken!";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        $query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        if (mysqli_query($conn, $query)) {
            $success = "Registration successful! <a href='login.php'>Login here</a>";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
<h2>Register</h2>

<?php
if (!empty($error)) {
    echo "<p style='color:red;'>$error</p>";
}
if (!empty($success)) {
    echo "<p style='color:green;'>$success</p>";
}
?>

<form method="post" action="">
    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Confirm Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <input type="submit" name="register" value="Register">
</form>

<p><a href="login.php">Already have an account? Login here.</a></p>
</body>
</html>
