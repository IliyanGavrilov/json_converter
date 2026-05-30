<?php
require_once 'auth_guard.php';
require_once 'includes/header.php';
require "db.php";

require_login();

$sql = "SELECT *
FROM conversions
WHERE user_id = ?
ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

$stmt->execute([$_SESSION["user_id"]]);

$result = $stmt->get_result();
$conversions = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Conversions</title>
    <link rel="stylesheet" href="/json_converter/public/style.css">
</head>

<body>
    <section class="container">
        <h1>Your Conversions</h1>
        <?php if (count($conversions) === 0): ?>
            <p class="empty-message">
                No conversions found.
            </p>
        <?php endif; ?>
        <?php foreach ($conversions as $conversion): ?>

            <div class="conversion-card">
                <p class="types">
                    <?= htmlspecialchars($conversion["input_format"]) ?>
                    ===&gt
                    <?= htmlspecialchars($conversion["output_format"]) ?>
                </p>
                <pre class="types">
<?= htmlspecialchars($conversion["input_content"]) ?>

            | |
            | |
            | |
             &#x25BC;
<?= htmlspecialchars($conversion["output_content"]) ?>
                </pre>
                <p class="comment">
                    <?= htmlspecialchars($conversion["comment"]) ?>
                </p>
                <p class="date">
                    <?= htmlspecialchars($conversion["created_at"]) ?>
                </p>
            </div>
        <?php endforeach; ?>
    </section>
</body>
</html>