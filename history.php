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

$sqlComm = "SELECT *
FROM conversion_comments
WHERE conversion_id = ?
ORDER BY created_at ASC";

$commentsStmt = $conn->prepare($sqlComm);


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
            <?php
            $commentsStmt->execute([$conversion["id"]]);
            $comments = $commentsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            ?>

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
                <p class="date">
                    <?= htmlspecialchars($conversion["created_at"]) ?>
                </p>
                <div class="comments">
                <h4>Comments</h4>
                <?php if (empty($comments)): ?>
                    <p>No comments.</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <?= htmlspecialchars($comment["comment"]) ?>
                            <small>
                                <?= htmlspecialchars($comment["created_at"]) ?>
                            </small>
                            <form class="delete-form" action="delete_comment.php" method="post">
                                <input type="hidden" name="comment_id" value="<?= $comment["id"] ?>">
                                <button type="submit" class="delete-btn">
                                    Delete
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
                    <form action="add_comment.php" method="post">
                        <input
                            type="hidden"
                            name="conversion_id"
                            value="<?= $conversion["id"] ?>"
                        >
                        <textarea name="comment" required placeholder="Add comment..."></textarea>

                        <button type="submit">
                            Add Comment
                        </button>
                    </form>
            </div>
        <?php endforeach; ?>
    </section>
</body>
</html>