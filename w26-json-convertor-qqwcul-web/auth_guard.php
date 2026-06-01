<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login() {
    if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
        header("Location: login.php");
        exit;
    }
}

function csrf_token() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function verify_csrf() {
    $submitted = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["csrf_token"] ?? "", $submitted)) {
        http_response_code(403);
        exit("Invalid request.");
    }
}