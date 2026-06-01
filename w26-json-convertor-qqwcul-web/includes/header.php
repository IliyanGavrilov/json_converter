<?php
require_once 'auth_guard.php';
require_login();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JSON Converter</title>
    <link rel="stylesheet" href="/json_converter/public/style.css">
</head>
<body>
<nav>
    <a href="/json_converter/index.php">Converter</a>
    <a href="/json_converter/history.php">History</a>
    <a href="/json_converter/settings.php">Settings</a>
    <a href="/json_converter/logout.php">Logout</a>
    <span>Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
</nav>
<main>