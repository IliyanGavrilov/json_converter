<?php

require "auth_guard.php";
require "db.php";

require_login();
verify_csrf();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $conversionId = (int)$_POST["conversion_id"];
    $comment = trim($_POST["comment"]);

    $ownership = $conn->prepare("SELECT id FROM conversions WHERE id = ? AND user_id = ?");
    $ownership->execute([$conversionId, $_SESSION["user_id"]]);

    if ($comment !== "" && $ownership->get_result()->fetch_assoc()) {
        $stmt = $conn->prepare("
            INSERT INTO conversion_comments
            (conversion_id, user_id, comment)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $conversionId,
            $_SESSION["user_id"],
            $comment
        ]);
    }
}

        header("Location: history.php");
        exit();