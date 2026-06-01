<?php
require_once 'includes/header.php';
require 'db.php';

$stmt = $conn->prepare("SELECT * FROM conversions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION["user_id"]]);
$conversions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$commentsStmt = $conn->prepare("SELECT * FROM conversion_comments WHERE conversion_id = ? ORDER BY created_at ASC");

require 'views/history.php';
