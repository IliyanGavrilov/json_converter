<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<style>
    .history-editor .CodeMirror { border: 1px solid #ddd; font-size: 13px; }
</style>

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
            <div class="code-block-wrapper">
                <div class="history-editor"
                     data-content="<?= htmlspecialchars($conversion["input_content"]) ?>"
                     data-format="<?= htmlspecialchars($conversion["input_format"]) ?>"></div>
                <button type="button" class="copy-btn" title="Copy to clipboard">Copy</button>
            </div>
            <p class="conversion-arrow">&#x2193;</p>
            <div class="code-block-wrapper">
                <div class="history-editor"
                     data-content="<?= htmlspecialchars($conversion["output_content"]) ?>"
                     data-format="<?= htmlspecialchars($conversion["output_format"]) ?>"></div>
                <button type="button" class="copy-btn" title="Copy to clipboard">Copy</button>
            </div>
            <p class="date">
                <time datetime="<?= htmlspecialchars($conversion["created_at"]) ?>">
                    <?= htmlspecialchars($conversion["created_at"]) ?>
                </time>
            </p>
            <div class="comments">
                <h2>Comments</h2>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/yaml/yaml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/properties/properties.min.js"></script>
<script>
(function () {
    function getModeForFormat(format) {
        var modes = {
            'json':       'application/json',
            'yaml':       'text/x-yaml',
            'xml':        'application/xml',
            'properties': 'text/x-properties',
            'csv':        'text/plain',
            'ini':        'text/x-ini'
        };
        return modes[format] || 'text/plain';
    }

    document.querySelectorAll('.history-editor').forEach(function (el) {
        var editor = CodeMirror(el, {
            value:       el.dataset.content,
            mode:        getModeForFormat(el.dataset.format),
            lineNumbers: true,
            readOnly:    true,
            lineWrapping: true
        });
        editor.setSize('100%', 200);

        var btn = el.parentElement.querySelector('.copy-btn');
        btn.addEventListener('click', function () {
            navigator.clipboard.writeText(editor.getValue()).then(function () {
                btn.textContent = 'Copied!';
                setTimeout(function () { btn.textContent = 'Copy'; }, 1500);
            });
        });
    });
}());
</script>

<?php require_once 'includes/footer.php'; ?>
