<?php
require "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
} catch (Exception $e) {
    $id = bin2hex(random_bytes(16));

    $stmt->execute([$id, $username, $email, $hashedPassword]);
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>
    
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <br><br>
        <input type="email" name="email" placeholder="Email" required>
        <br><br>
        <input type="password" name="password" placeholder="Password" required>
        <br><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>