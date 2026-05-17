<?php
require_once 'includes/header.php';
require_once 'convert.php';

$output = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = $_POST['input_content'];
        $fromFormat = $_POST['from_format'];
        $toFormat = $_POST['to_format'];
        
        $output = convertContent($input, $fromFormat, $toFormat);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="converter">
    <h1>Converter</h1>
    
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="formats">
            <select name="from_format">
                <option value="json">JSON</option>
                <option value="yaml">YAML</option>
                <option value="xml">XML</option>
            </select>
            →
            <select name="to_format">
                <option value="yaml">YAML</option>
                <option value="json">JSON</option>
                <option value="xml">XML</option>
                <option value="csv">CSV</option>
                <option value="properties">.properties</option>
            </select>
        </div>
        
        <textarea name="input_content" rows="15" placeholder="Paste your content here..."><?php echo htmlspecialchars($_POST['input_content'] ?? ''); ?></textarea>
        
        <button type="submit">Convert</button>
    </form>
    
    <?php if ($output): ?>
        <h2>Result</h2>
        <pre><?php echo htmlspecialchars($output); ?></pre>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>