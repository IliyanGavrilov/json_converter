<?php
require 'auth_guard.php';
require "db.php";

require_login();

$sql = "SELECT *
FROM conversions
WHERE user_id = ?
ORDER BY created_at DESC
";

$stmt = $conn->prepare($sql);

$stmt->execute([$_SESSION["user_id"]]);

$result = $stmt->get_result();
$conversions = $result->fetch_all(MYSQLI_ASSOC);
?>

<?php foreach ($conversions as $conversion): ?>
    <hr>

    <p>
        <?= htmlspecialchars($conversion["input_type"]) ?>
        ----&gt
        <?= htmlspecialchars($conversion["output_type"]) ?>
    </p>

    <p>
        <?= htmlspecialchars($conversion["comment"]) ?>
    </p>

    <p>
        <?= htmlspecialchars($conversion["created_at"]) ?>
    </p>

<?php endforeach; ?>