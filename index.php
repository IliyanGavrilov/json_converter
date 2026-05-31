<?php
require_once 'includes/header.php';
require_once 'convert.php';
require_once 'transformations.php';
require_once 'db.php';

$output         = '';
$error          = '';
$input          = '';
$fromFormat     = '';
$toFormat       = '';
$transformation = 'none';
$user_id        = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$settings = $stmt->get_result()->fetch_assoc();

if (!$settings) {
    $settings = [
        'auto_save'              => 1,
        'default_input_format'   => 'json',
        'default_output_format'  => 'yaml',
        'default_transformation' => 'none',
        'default_indentation'    => 2
    ];
}

$stmt = $conn->prepare("SELECT * FROM value_mappings WHERE user_id = ?");
$stmt->execute([$user_id]);
$mappings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function mimeForFormat($format) {
    $map = [
        'json'       => ['json',       'application/json'],
        'yaml'       => ['yaml',       'application/x-yaml'],
        'xml'        => ['xml',        'application/xml'],
        'csv'        => ['csv',        'text/csv'],
        'properties' => ['properties', 'text/plain'],
        'ini'        => ['ini',        'text/plain'],
    ];
    return $map[$format] ?? ['txt', 'text/plain'];
}

function sendFile($content, $format) {
    [$ext, $mime] = mimeForFormat($format);
    $filename = 'converted_' . date('Y-m-d_H-i-s') . '.' . $ext;
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    header('Expires: 0');
    echo $content;
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $comment = trim($_POST['comment'] ?? '');

    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
        $file_content = file_get_contents($_FILES['import_file']['tmp_name']);
        if ($file_content !== false) {
            $_POST['input_content'] = $file_content;
        } else {
            $error = "Failed to read the uploaded file.";
        }
    }

    if (isset($_POST['download_output']) && !empty($_POST['output_content'])) {
        sendFile($_POST['output_content'], $_POST['to_format'] ?? 'txt');
    }

    if (isset($_POST['manual_save'])) {
        $stmt = $conn->prepare("INSERT INTO conversions (user_id, input_format, output_format, input_content, output_content) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $_POST['from_format'], $_POST['to_format'], $_POST['input_content'], $_POST['output_content']]);
        $conversion_id = $conn->insert_id;
        if ($comment !== '') {
            $stmt = $conn->prepare("INSERT INTO conversion_comments (conversion_id, user_id, comment) VALUES (?, ?, ?)");
            $stmt->execute([$conversion_id, $user_id, $comment]);
        }
        header("Location: history.php");
        exit();
    }

    if (!empty($_POST['input_content'])) {
        try {
            $input          = $_POST['input_content'];
            $fromFormat     = $_POST['from_format'];
            $toFormat       = $_POST['to_format'];
            $transformation = $_POST['transformation'] ?? 'none';
            $pretty_print   = isset($_POST['pretty_print']);

            $data   = parseInput($input, $fromFormat);
            $data   = applyValueMappings($data, $mappings);
            $data   = applyTransformation($data, $transformation);
            $output = outputFormat($data, $toFormat, [
                'indentation'  => (int)$settings['default_indentation'],
                'pretty_print' => $pretty_print
            ]);

            if ($settings['auto_save']) {
                $stmt = $conn->prepare("INSERT INTO conversions (user_id, input_format, output_format, input_content, output_content) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $fromFormat, $toFormat, $input, $output]);
                $conversion_id = $conn->insert_id;
                if ($comment !== '') {
                    $stmt = $conn->prepare("INSERT INTO conversion_comments (conversion_id, user_id, comment) VALUES (?, ?, ?)");
                    $stmt->execute([$conversion_id, $user_id, $comment]);
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$current_input  = $_POST['input_content']  ?? '';
$current_from   = $_POST['from_format']    ?? $settings['default_input_format'];
$current_to     = $_POST['to_format']      ?? $settings['default_output_format'];
$current_trans  = $_POST['transformation'] ?? $settings['default_transformation'];
$current_pretty = isset($_POST['pretty_print']);
?>

<div class="converter">
    <h1>Converter</h1>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

        <section class="file-import" style="margin-bottom: 15px; padding: 10px; background: #f0f0f0; border-radius: 5px;">
            <label for="import_file" style="font-weight: bold;">Import from file:</label>
            <input type="file" name="import_file" id="import_file" accept=".json,.yaml,.yml,.xml,.csv,.properties,.ini,.txt">
            <button type="submit" name="import_submit" style="margin-left: 10px;">Read File</button>
        </section>

        <section class="form-formats">
            <label for="from_format">From</label>
            <select id="from_format" name="from_format">
                <option value="json"       <?php echo $current_from === 'json'       ? 'selected' : ''; ?>>JSON</option>
                <option value="yaml"       <?php echo $current_from === 'yaml'       ? 'selected' : ''; ?>>YAML</option>
                <option value="xml"        <?php echo $current_from === 'xml'        ? 'selected' : ''; ?>>XML</option>
                <option value="csv"        <?php echo $current_from === 'csv'        ? 'selected' : ''; ?>>CSV</option>
                <option value="properties" <?php echo $current_from === 'properties' ? 'selected' : ''; ?>>.properties</option>
                <option value="ini"        <?php echo $current_from === 'ini'        ? 'selected' : ''; ?>>.ini</option>
            </select>

            <span class="arrow">→</span>

            <label for="to_format">To</label>
            <select id="to_format" name="to_format">
                <option value="yaml"       <?php echo $current_to === 'yaml'       ? 'selected' : ''; ?>>YAML</option>
                <option value="json"       <?php echo $current_to === 'json'       ? 'selected' : ''; ?>>JSON</option>
                <option value="xml"        <?php echo $current_to === 'xml'        ? 'selected' : ''; ?>>XML</option>
                <option value="csv"        <?php echo $current_to === 'csv'        ? 'selected' : ''; ?>>CSV</option>
                <option value="properties" <?php echo $current_to === 'properties' ? 'selected' : ''; ?>>.properties</option>
                <option value="ini"        <?php echo $current_to === 'ini'        ? 'selected' : ''; ?>>.ini</option>
            </select>
        </section>

        <section class="form-transformation">
            <label for="transformation">Key transformation:</label>
            <select id="transformation" name="transformation">
                <option value="none"       <?php echo $current_trans === 'none'       ? 'selected' : ''; ?>>None</option>
                <option value="camelCase"  <?php echo $current_trans === 'camelCase'  ? 'selected' : ''; ?>>camelCase</option>
                <option value="PascalCase" <?php echo $current_trans === 'PascalCase' ? 'selected' : ''; ?>>PascalCase</option>
                <option value="snake_case" <?php echo $current_trans === 'snake_case' ? 'selected' : ''; ?>>snake_case</option>
                <option value="kebab-case" <?php echo $current_trans === 'kebab-case' ? 'selected' : ''; ?>>kebab-case</option>
                <option value="UPPER_CASE" <?php echo $current_trans === 'UPPER_CASE' ? 'selected' : ''; ?>>UPPER_CASE</option>
            </select>
        </section>

        <section class="form-options">
            <label>
                <input type="checkbox" name="pretty_print" value="1" <?php echo $current_pretty ? 'checked' : ''; ?>>
                Pretty-print output
            </label>
        </section>

        <label for="input_content">Input</label>
        <textarea id="input_content" name="input_content" rows="15" placeholder="Paste your content here or import a file..."><?php echo htmlspecialchars($current_input); ?></textarea>

        <?php if ($settings['auto_save']): ?>
            <input type="text" name="comment" placeholder="Add a comment (optional)">
        <?php endif; ?>

        <button type="submit">Convert</button>
    </form>

<?php if ($output): ?>
<section class="result">
    <h2>Result</h2>
    <pre><code><?php echo htmlspecialchars($output); ?></code></pre>

    <form method="POST" action="" id="downloadForm">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <input type="hidden" name="download_output" value="1">
        <input type="hidden" name="output_content" value="<?php echo htmlspecialchars($output); ?>">
        <input type="hidden" name="to_format" value="<?php echo htmlspecialchars($toFormat ?? $current_to); ?>">
        <button type="submit" name="submit_download">Download Converted File</button>
    </form>

    <?php if (!$settings['auto_save']): ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <input type="hidden" name="input_content" value="<?php echo htmlspecialchars($input); ?>">
        <input type="hidden" name="from_format" value="<?php echo htmlspecialchars($fromFormat ?? $current_from); ?>">
        <input type="hidden" name="to_format" value="<?php echo htmlspecialchars($toFormat ?? $current_to); ?>">
        <input type="hidden" name="transformation" value="<?php echo htmlspecialchars($transformation ?? $current_trans); ?>">
        <input type="hidden" name="manual_save" value="1">
        <input type="hidden" name="output_content" value="<?php echo htmlspecialchars($output); ?>">
        <input type="text" name="comment" placeholder="Add a comment (optional)">
        <button type="submit">Save to history</button>
    </form>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
