<?php
require_once 'includes/header.php';
require_once 'convert.php';
require_once 'transformations.php';
require_once 'db.php';

$output = '';
$error = '';
$user_id = $_SESSION['user_id'];

// Load defaults from user settings
$stmt = $conn->prepare("SELECT * FROM settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$settings = $stmt->get_result()->fetch_assoc();

// Fallback defaults
if (!$settings) {
    $settings = [
        'auto_save' => 1,
        'default_input_format' => 'json',
        'default_output_format' => 'yaml',
        'default_transformation' => 'none',
        'default_indentation' => 2
    ];
}

// Load user's value mappings
$stmt = $conn->prepare("SELECT * FROM value_mappings WHERE user_id = ?");
$stmt->execute([$user_id]);
$mappings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['manual_save'])) {
        $stmt = $conn->prepare("INSERT INTO conversions 
            (user_id, input_format, output_format, input_content, output_content, comment) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $_POST['from_format'],
            $_POST['to_format'],
            $_POST['input_content'],
            $_POST['output_content'],
            $_POST['comment'] ?? null
        ]);
        header("Location: history.php");
        exit();
    }

    try {
        $input = $_POST['input_content'];
        $fromFormat = $_POST['from_format'];
        $toFormat = $_POST['to_format'];
        $transformation = $_POST['transformation'] ?? 'none';

        $data = parseInput($input, $fromFormat);
        $data = applyValueMappings($data, $mappings);
        $data = applyTransformation($data, $transformation);
        $output = outputFormat($data, $toFormat);

        if ($settings['auto_save']) {
            $stmt = $conn->prepare("INSERT INTO conversions 
                (user_id, input_format, output_format, input_content, output_content) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $fromFormat, $toFormat, $input, $output]);
        }

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
        <section class="form-formats">
            <select id="from_format" name="from_format">
                <option value="json" <?php echo ($_POST['from_format'] ?? $settings['default_input_format']) === 'json' ? 'selected' : ''; ?>>JSON</option>
                <option value="yaml" <?php echo ($_POST['from_format'] ?? $settings['default_input_format']) === 'yaml' ? 'selected' : ''; ?>>YAML</option>
                <option value="xml" <?php echo ($_POST['from_format'] ?? $settings['default_input_format']) === 'xml' ? 'selected' : ''; ?>>XML</option>
            </select>

            <span class="arrow">→</span>

            <select id="to_format" name="to_format">
                <option value="yaml" <?php echo ($_POST['to_format'] ?? $settings['default_output_format']) === 'yaml' ? 'selected' : ''; ?>>YAML</option>
                <option value="json" <?php echo ($_POST['to_format'] ?? $settings['default_output_format']) === 'json' ? 'selected' : ''; ?>>JSON</option>
                <option value="xml" <?php echo ($_POST['to_format'] ?? $settings['default_output_format']) === 'xml' ? 'selected' : ''; ?>>XML</option>
                <option value="csv" <?php echo ($_POST['to_format'] ?? $settings['default_output_format']) === 'csv' ? 'selected' : ''; ?>>CSV</option>
                <option value="properties" <?php echo ($_POST['to_format'] ?? $settings['default_output_format']) === 'properties' ? 'selected' : ''; ?>>.properties</option>
            </select>
        </section>

        <section class="form-transformation">
            <label for="transformation">Key transformation:</label>
            <select id="transformation" name="transformation">
                <option value="none" <?php echo ($_POST['transformation'] ?? $settings['default_transformation']) === 'none' ? 'selected' : ''; ?>>None</option>
                <option value="camel" <?php echo ($_POST['transformation'] ?? $settings['default_transformation']) === 'camel' ? 'selected' : ''; ?>>camelCase</option>
                <option value="snake" <?php echo ($_POST['transformation'] ?? $settings['default_transformation']) === 'snake' ? 'selected' : ''; ?>>snake_case</option>
                <option value="upper" <?php echo ($_POST['transformation'] ?? $settings['default_transformation']) === 'upper' ? 'selected' : ''; ?>>UPPER_CASE</option>
            </select>
        </section>

        <textarea name="input_content" rows="15" placeholder="Paste your content here..."><?php echo htmlspecialchars($_POST['input_content'] ?? ''); ?></textarea>

        <button type="submit">Convert</button>
    </form>

    <?php if ($output): ?>
    <section class="result">
        <h2>Result</h2>
        <pre><code><?php echo htmlspecialchars($output); ?></code></pre>

        <?php if (!$settings['auto_save']): ?>
        <form method="POST">
            <input type="hidden" name="input_content" value="<?php echo htmlspecialchars($input ?? ''); ?>">
            <input type="hidden" name="from_format" value="<?php echo htmlspecialchars($fromFormat ?? ''); ?>">
            <input type="hidden" name="to_format" value="<?php echo htmlspecialchars($toFormat ?? ''); ?>">
            <input type="hidden" name="transformation" value="<?php echo htmlspecialchars($transformation ?? ''); ?>">
            <input type="hidden" name="manual_save" value="1">
            <input type="hidden" name="output_content" value="<?php echo htmlspecialchars($output); ?>">
            <input type="text" name="comment" placeholder="Add a comment (optional)">
            <button type="submit">Save to history</button>
        </form>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>