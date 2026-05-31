<section class="container">
    <h1>Your Conversions</h1>
    <?php if (count($conversions) === 0): ?>
        <p class="empty-message">No conversions found.</p>
    <?php endif; ?>
    <?php foreach ($conversions as $conversion): ?>
        <?php
        $commentsStmt->execute([$conversion["id"]]);
        $comments = $commentsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        ?>

        <article class="conversion-card">
            <header class="types">
                <span><?= htmlspecialchars($conversion["input_format"]) ?></span>
                <span>&#x2192;</span>
                <span><?= htmlspecialchars($conversion["output_format"]) ?></span>
            </header>
            <pre class="input-content"><?= htmlspecialchars($conversion["input_content"]) ?></pre>
            <p class="conversion-arrow">&#x2193;</p>
            <pre class="output-content"><?= htmlspecialchars($conversion["output_content"]) ?></pre>
            <p class="date">
                <time datetime="<?= htmlspecialchars($conversion["created_at"]) ?>">
                    <?= htmlspecialchars($conversion["created_at"]) ?>
                </time>
            </p>
            <div class="comments">
                <h4>Comments</h4>
                <?php if (empty($comments)): ?>
                    <p>No comments.</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <?= htmlspecialchars($comment["comment"]) ?>
                            <small><?= htmlspecialchars($comment["created_at"]) ?></small>
                            <form class="delete-form" action="delete_comment.php" method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                <input type="hidden" name="comment_id" value="<?= htmlspecialchars($comment["id"]) ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <form action="add_comment.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="conversion_id" value="<?= htmlspecialchars($conversion["id"]) ?>">
                <label for="comment-<?= htmlspecialchars($conversion["id"]) ?>" class="sr-only">Add a comment</label>
                <textarea id="comment-<?= htmlspecialchars($conversion["id"]) ?>" name="comment" required placeholder="Add comment..."></textarea>
                <button type="submit">Add Comment</button>
            </form>
        </article>
    <?php endforeach; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
