<?php
require "auth_guard.php";
require "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf();
try{
    $id = bin2hex(random_bytes(16));
    
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (id, username, email, password)
        VALUES (?, ?, ?, ?)");

    $stmt->execute([
        $id,
        $username,
        $email,
        $hashedPassword
    ]);
    header("Location: login.php");
    exit();
} catch (Exception $e) {
    $error = "Username or email is already taken.";
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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
        <input type="email" name="email" placeholder="Email" required>
        <br><br>
        <input type="password" name="password" placeholder="Password" required>
        <br><br>
        <button type="submit">Register</button>
        <br><br><br><br>
        <a href="login.php">
        <button type="button">Go to Login</button>
        </a>
    </form>
</body>
</html>