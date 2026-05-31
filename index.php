<?php
require_once 'includes/header.php';
require_once 'convert.php';
require_once 'transformations.php';
require_once 'db.php';

$output = '';
$error = '';
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$settings = $stmt->get_result()->fetch_assoc();

if (!$settings) {
    $settings = [
        'auto_save' => 1,
        'default_input_format' => 'json',
        'default_output_format' => 'yaml',
        'default_transformation' => 'none',
        'default_indentation' => 2
    ];
}
$stmt = $conn->prepare("SELECT * FROM value_mappings WHERE user_id = ?");
$stmt->execute([$user_id]);
$mappings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (isset($_GET['download']) && !empty($_GET['download']) && isset($_GET['to_format'])) {
    $download_output = '';
    
    if (isset($output) && !empty($output)) {
        $download_output = $output;
    } 
    elseif (isset($_GET['output_data'])) {
        $download_output = base64_decode($_GET['output_data']);
    }
    
    if (!empty($download_output)) {
        $toFormat = $_GET['to_format'];
        $file_extension = '';
        $mime_type = '';
        
        switch ($toFormat) {
            case 'json':
                $file_extension = 'json';
                $mime_type = 'application/json';
                break;
            case 'yaml':
                $file_extension = 'yaml';
                $mime_type = 'application/x-yaml';
                break;
            case 'xml':
                $file_extension = 'xml';
                $mime_type = 'application/xml';
                break;
            case 'csv':
                $file_extension = 'csv';
                $mime_type = 'text/csv';
                break;
            case 'properties':
                $file_extension = 'properties';
                $mime_type = 'text/plain';
                break;
            case 'ini':
                $file_extension = 'ini';
                $mime_type = 'text/plain';
                break;
            default:
                $file_extension = 'txt';
                $mime_type = 'text/plain';
        }
        
        $filename = 'converted_' . date('Y-m-d_H-i-s') . '.' . $file_extension;
        
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($download_output));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        
        echo $download_output;
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = trim($_POST['comment'] ?? '');
    
    if (isset($_POST['download_output']) && isset($_POST['output_content']) && !empty($_POST['output_content'])) {
        $download_output = $_POST['output_content'];
        $toFormat = $_POST['to_format'];
        $file_extension = '';
        $mime_type = '';
        
        switch ($toFormat) {
            case 'json':
                $file_extension = 'json';
                $mime_type = 'application/json';
                break;
            case 'yaml':
                $file_extension = 'yaml';
                $mime_type = 'application/x-yaml';
                break;
            case 'xml':
                $file_extension = 'xml';
                $mime_type = 'application/xml';
                break;
            case 'csv':
                $file_extension = 'csv';
                $mime_type = 'text/csv';
                break;
            case 'properties':
                $file_extension = 'properties';
                $mime_type = 'text/plain';
                break;
            case 'ini':
                $file_extension = 'ini';
                $mime_type = 'text/plain';
                break;
            default:
                $file_extension = 'txt';
                $mime_type = 'text/plain';
        }
        
        $filename = 'converted_' . date('Y-m-d_H-i-s') . '.' . $file_extension;
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($download_output));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        
        echo $download_output;
        exit();
    }
    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
        $file_content = file_get_contents($_FILES['import_file']['tmp_name']);
        if ($file_content !== false) {
            $_POST['input_content'] = $file_content;
        } else {
            $error = "Failed to read the uploaded file.";
        }
    }

    if (isset($_POST['manual_save'])) {

    }

    if (isset($_POST['input_content']) && !empty($_POST['input_content'])) {
    }
}

    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
        $file_content = file_get_contents($_FILES['import_file']['tmp_name']);
        if ($file_content !== false) {
            $_POST['input_content'] = $file_content;
        } else {
            $error = "Failed to read the uploaded file.";
        }
    }
    
if (isset($_POST['manual_save'])) {

    $stmt = $conn->prepare("
        INSERT INTO conversions 
        (user_id, input_format, output_format, input_content, output_content)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $_POST['from_format'],
        $_POST['to_format'],
        $_POST['input_content'],
        $_POST['output_content']
    ]);

    $conversion_id = $conn->insert_id;

    if ($comment !== '') {
        $stmt = $conn->prepare("
            INSERT INTO conversion_comments (conversion_id, user_id, comment)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $conversion_id,
            $user_id,
            $comment
        ]);
    }

    header("Location: history.php");
    exit();
}

    if (isset($_POST['input_content']) && !empty($_POST['input_content'])) {
        try {
            $input = $_POST['input_content'];
            $fromFormat = $_POST['from_format'];
            $toFormat = $_POST['to_format'];
            $transformation = $_POST['transformation'] ?? 'none';

            $data = parseInput($input, $fromFormat);
            $data = applyValueMappings($data, $mappings);
            $data = applyTransformation($data, $transformation);
            $output = outputFormat($data, $toFormat, ['indentation' => (int)$settings['default_indentation']]);

    if ($settings['auto_save']) {

    $stmt = $conn->prepare("
        INSERT INTO conversions 
        (user_id, input_format, output_format, input_content, output_content)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $fromFormat,
        $toFormat,
        $input,
        $output
    ]);

    $conversion_id = $conn->insert_id;

    if ($comment !== '') {
        $stmt = $conn->prepare("
            INSERT INTO conversion_comments (conversion_id, user_id, comment)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $conversion_id,
            $user_id,
            $comment
            ]);
        }
    }

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

$current_input = isset($_POST['input_content']) ? $_POST['input_content'] : '';
$current_from = isset($_POST['from_format']) ? $_POST['from_format'] : $settings['default_input_format'];
$current_to = isset($_POST['to_format']) ? $_POST['to_format'] : $settings['default_output_format'];
$current_trans = isset($_POST['transformation']) ? $_POST['transformation'] : $settings['default_transformation'];
?>

<div class="converter">
    <h1>Converter</h1>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <section class="file-import" style="margin-bottom: 15px; padding: 10px; background: #f0f0f0; border-radius: 5px;">
            <label for="import_file" style="font-weight: bold;">Import from file:</label>
            <input type="file" name="import_file" id="import_file" accept=".json,.yaml,.yml,.xml,.csv,.properties,.ini,.txt">
            <button type="submit" name="import_submit" style="margin-left: 10px;">Read File</button>
        </section>

        <section class="form-formats">
            <select id="from_format" name="from_format">
                <option value="json" <?php echo $current_from === 'json' ? 'selected' : ''; ?>>JSON</option>
                <option value="yaml" <?php echo $current_from === 'yaml' ? 'selected' : ''; ?>>YAML</option>
                <option value="xml" <?php echo $current_from === 'xml' ? 'selected' : ''; ?>>XML</option>
                <option value="csv" <?php echo $current_from === 'csv' ? 'selected' : ''; ?>>CSV</option>
                <option value="properties" <?php echo $current_from === 'properties' ? 'selected' : ''; ?>>.properties</option>
                <option value="ini" <?php echo $current_from === 'ini' ? 'selected' : ''; ?>>.ini</option>
            </select>

            <span class="arrow">→</span>

            <select id="to_format" name="to_format">
                <option value="yaml" <?php echo $current_to === 'yaml' ? 'selected' : ''; ?>>YAML</option>
                <option value="json" <?php echo $current_to === 'json' ? 'selected' : ''; ?>>JSON</option>
                <option value="xml" <?php echo $current_to === 'xml' ? 'selected' : ''; ?>>XML</option>
                <option value="csv" <?php echo $current_to === 'csv' ? 'selected' : ''; ?>>CSV</option>
                <option value="properties" <?php echo $current_to === 'properties' ? 'selected' : ''; ?>>.properties</option>
                <option value="ini" <?php echo $current_to === 'ini' ? 'selected' : ''; ?>>.ini</option>
            </select>
        </section>

        <section class="form-transformation">
            <label for="transformation">Key transformation:</label>
            <select id="transformation" name="transformation">
                <option value="none" <?php echo $current_trans === 'none' ? 'selected' : ''; ?>>None</option>
                <option value="camel" <?php echo $current_trans === 'camel' ? 'selected' : ''; ?>>camelCase</option>
                <option value="snake" <?php echo $current_trans === 'snake' ? 'selected' : ''; ?>>snake_case</option>
                <option value="upper" <?php echo $current_trans === 'upper' ? 'selected' : ''; ?>>UPPER_CASE</option>
            </select>
        </section>

        
        <textarea name="input_content" rows="15" placeholder="Paste your content here or import a file..."><?php echo htmlspecialchars($current_input); ?></textarea>
        
        <?php if ($settings['auto_save']): ?>
            <input type="text" name="comment" placeholder="Add a comment (optional)">
        <?php endif; ?>
        
        <button type="submit">Convert</button>
    </form>

<?php if ($output): ?>
<section class="result">
    <h2>Result</h2>
    <pre><code><?php echo htmlspecialchars($output); ?></code></pre>
    <div style="margin: 15px 0;">
        <form method="POST" action="" id="downloadForm">
            <input type="hidden" name="download_output" value="1">
            <input type="hidden" name="output_content" value="<?php echo htmlspecialchars($output); ?>">
            <input type="hidden" name="to_format" value="<?php echo htmlspecialchars($toFormat ?? $current_to); ?>">
            <button type="submit" name="submit_download">
                Download Converted File
            </button>
        </form>
    </div>

    <?php if (!$settings['auto_save']): ?>
    <form method="POST">
        <input type="hidden" name="input_content" value="<?php echo htmlspecialchars($input ?? ''); ?>">
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