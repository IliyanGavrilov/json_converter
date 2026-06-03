<?php
require_once 'auth_guard.php';
require_login();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JSON Converter</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
<nav>
    <a href="index.php">Converter</a>
    <a href="history.php">History</a>
    <a href="settings.php">Settings</a>
    <a href="logout.php">Logout</a>
    <span>Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
</nav>
<main>