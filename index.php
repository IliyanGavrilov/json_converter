<?php
require_once 'includes/header.php';
require_once 'convert.php';
require_once 'transformations.php';
require_once 'db.php';

$output = '';
$error = '';

// Load user's value mappings
$mappings = [];
$stmt = $conn->prepare("SELECT * FROM value_mappings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$mappings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = $_POST['input_content'];
        $fromFormat = $_POST['from_format'];
        $toFormat = $_POST['to_format'];
        $transformation = $_POST['transformation'] ?? 'none';
        
        $data = parseInput($input, $fromFormat);
        $data = applyValueMappings($data, $mappings);
        $data = applyTransformation($data, $transformation);
        $output = outputFormat($data, $toFormat);
        
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

        <div class="transformation">
            <label>Key transformation:</label>
            <select name="transformation">
                <option value="none">None</option>
                <option value="camel">camelCase</option>
                <option value="snake">snake_case</option>
                <option value="upper">UPPER_CASE</option>
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