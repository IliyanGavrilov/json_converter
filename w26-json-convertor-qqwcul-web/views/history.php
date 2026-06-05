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
            <div class="history-editor"
                 data-content="<?= htmlspecialchars($conversion["input_content"]) ?>"
                 data-format="<?= htmlspecialchars($conversion["input_format"]) ?>"></div>
            <p class="conversion-arrow">&#x2193;</p>
            <div class="history-editor"
                 data-content="<?= htmlspecialchars($conversion["output_content"]) ?>"
                 data-format="<?= htmlspecialchars($conversion["output_format"]) ?>"></div>
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
                <a href="sharing.php?id=<?= $conversion['id'] ?>" target="_blank"">Share This Conversion</a>
            </div>
            <form action="add_comment.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="conversion_id" value="<?= htmlspecialchars($conversion["id"]) ?>">
                <label for="comment-<?= htmlspecialchars($conversion["id"]) ?>" class="sr-only">Add a comment</label>
                <textarea id="comment-<?= htmlspecialchars($conversion["id"]) ?>" name="comment" required placeholder="Add comment..."></textarea>
                <button type="submit">Add Comment</button>
            </form>
            <form action="index.php" method="POST" style="margin-top: 8px;">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="input_content"  value="<?= htmlspecialchars($conversion["input_content"]) ?>">
                <input type="hidden" name="from_format"    value="<?= htmlspecialchars($conversion["input_format"]) ?>">
                <input type="hidden" name="to_format"      value="<?= htmlspecialchars($conversion["output_format"]) ?>">
                <input type="hidden" name="reload_conversion" value="1">
                <button type="submit" class="reload-btn">Reload in Converter</button>
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
    var SVG_COPY  = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 0 1 0 1.5h-1.5a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 0 0 .25-.25v-1.5a.75.75 0 0 1 1.5 0v1.5A1.75 1.75 0 0 1 9.25 16h-7.5A1.75 1.75 0 0 1 0 14.25Z"/><path d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0 1 14.25 11h-7.5A1.75 1.75 0 0 1 5 9.25Zm1.75-.25a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 0 0 .25-.25v-7.5a.25.25 0 0 0-.25-.25Z"/></svg>';
    var SVG_CHECK = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M13.78 4.22a.75.75 0 0 1 0 1.06l-7.25 7.25a.75.75 0 0 1-1.06 0L2.22 9.28a.751.751 0 0 1 .018-1.042.751.751 0 0 1 1.042-.018L6 10.94l6.72-6.72a.75.75 0 0 1 1.06 0Z"/></svg>';

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
            value:        el.dataset.content,
            mode:         getModeForFormat(el.dataset.format),
            lineNumbers:  true,
            readOnly:     'nocursor',
            lineWrapping: true
        });
        editor.setSize('100%', 200);

        var btn = document.createElement('button');
        btn.type      = 'button';
        btn.title     = 'Copy to clipboard';
        btn.innerHTML = SVG_COPY;
        btn.style.cssText = 'position:absolute;top:8px;right:8px;z-index:10;width:28px;height:28px;padding:0;display:flex;align-items:center;justify-content:center;background:rgba(30,30,30,0.4);color:#fff;border:none;border-radius:6px;cursor:pointer;opacity:0.5;transition:opacity 0.15s,background 0.15s;';
        btn.addEventListener('mouseover', function () { btn.style.opacity = '1';   btn.style.background = 'rgba(30,30,30,0.72)'; });
        btn.addEventListener('mouseout',  function () { btn.style.opacity = '0.5'; btn.style.background = 'rgba(30,30,30,0.4)';  });
        btn.addEventListener('click', function () {
            navigator.clipboard.writeText(editor.getValue()).then(function () {
                btn.innerHTML = SVG_CHECK;
                setTimeout(function () { btn.innerHTML = SVG_COPY; }, 1500);
            });
        });
        editor.getWrapperElement().appendChild(btn);
    });
}());
</script>

<?php require_once 'includes/footer.php'; ?>