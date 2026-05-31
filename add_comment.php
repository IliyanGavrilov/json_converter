<?php

require "auth_guard.php";
require "db.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $conversionId = (int)$_POST["conversion_id"];
    $comment = trim($_POST["comment"]);

    if ($comment !== "") {

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