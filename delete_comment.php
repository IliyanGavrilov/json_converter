<?php

require "auth_guard.php";
require "db.php";

require_login();
verify_csrf();

$commentId = (int)$_POST["comment_id"];

$stmt = $conn->prepare("
    DELETE cc
    FROM conversion_comments cc
    JOIN conversions c
        ON cc.conversion_id = c.id
    WHERE cc.id = ?
      AND c.user_id = ?
");

$stmt->execute([
    $commentId,
    $_SESSION["user_id"]
]);

header("Location: history.php");
exit();