<?php
require "auth_guard.php";
require "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf();

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"]  = $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["logged_in"] = true;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username or password.";
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
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

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
