<?php
$config = include('config.php');

$conn = new mysqli(
    $config->DB_SERVERNAME,
    $config->DB_USERNAME,
    $config->DB_PASSWORD,
    $config->DB_NAME
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}