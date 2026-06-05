<?php
require_once 'db.php';
require_once 'convert.php';
require_once 'transformations.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM conversions WHERE id = ?");
$stmt->execute([$id]);
$conversion = $stmt->get_result()->fetch_assoc();

if (!$conversion) {
    die('Conversion not found');
}

$stmt = $conn->prepare("SELECT * FROM conversion_comments WHERE conversion_id = ? ORDER BY created_at DESC");
$stmt->execute([$conversion['id']]);
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$output = '';
$error = '';
$input = $conversion['input_content'];
$fromFormat = $conversion['input_format'];
$toFormat = $conversion['output_format'];


require_once 'includes/header.php';
?>

<section class="container">
    <div class="conversion-card">

<?php
$currentUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
$shareUrl = str_replace(basename($_SERVER['SCRIPT_NAME']), 'sharing.php', $currentUrl) . '?id=' . $id;
?>

        <div class="share-link">
            <input type="text" value="<?= $shareUrl ?>" id="shareUrl" readonly onclick="this.select()">
            <button onclick="this.previousElementSibling.select(); document.execCommand('copy'); alert('Link copied!');">Copy Link</button>
        </div>
        
        <div class="types">
            <span><?= htmlspecialchars($conversion["input_format"]) ?></span>
            <span>→</span>
            <span><?= htmlspecialchars($conversion["output_format"]) ?></span>
            <span class="badge">Shared View</span>
        </div>
        
        <h3>Input Content:</h3>
        <div class="editor-display"><?= htmlspecialchars($conversion["input_content"]) ?></div>
        

        <p class="conversion-arrow">&#x2193;</p>
        
        <h3>Output Content:</h3>
        <div class="editor-display"><?= htmlspecialchars($output ?: $conversion["output_content"]) ?></div>
        
        <p class="date">
            Created: <?= htmlspecialchars($conversion["created_at"]) ?>
        </p>

        <div class="comments">
            <h2>Comments (<?= count($comments) ?>)</h2>
            
            <?php if (empty($comments)): ?>
                <p class="empty-message" style="text-align: left;">No comments yet.</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <?= nl2br(htmlspecialchars($comment["comment"])) ?>
                        <small>Posted: <?= htmlspecialchars($comment["created_at"]) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <form action="add_comment.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="conversion_id" value="<?= htmlspecialchars($conversion["id"]) ?>">
                <label for="comment-<?= htmlspecialchars($conversion["id"]) ?>" class="sr-only">Add a comment</label>
                <textarea id="comment-<?= htmlspecialchars($conversion["id"]) ?>" name="comment" required placeholder="Add comment..."></textarea>
                <button type="submit">Add Comment</button>
            </form>
        </div>
        
        <div class="action-buttons">
            <form action="index.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="input_content" value="<?= htmlspecialchars($conversion["input_content"]) ?>">
                <input type="hidden" name="from_format" value="<?= htmlspecialchars($conversion["input_format"]) ?>">
                <input type="hidden" name="to_format" value="<?= htmlspecialchars($conversion["output_format"]) ?>">
                <button type="submit">Open in Converter</button>
            </form>
            
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>