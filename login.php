<?php
session_start();

require "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");

    $stmt->execute([$username]);

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION['logged_in'] = true;
        
        header("Location: index.php");
        exit();

    } else {
        echo "Invalid credentials";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="/json_converter/public/style.css">
</head>
<body class="auth-page">
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <br><br>
        <input type="password" name="password" placeholder="Password" required>
        <br><br>
        <button type="submit">Login</button>
        <br><br><br><br>
        <a href="register.php">
        <button type="button">Go to Register</button>
        </a>
    </form>
</body>
</html>
