<?php
require_once 'includes/header.php';
require_once 'db.php';

$user_id = $_SESSION['user_id'];
$error_messages = [
    'missing_fields' => 'From key and To key are required.'
];
$error = isset($_GET['error']) ? ($error_messages[$_GET['error']] ?? '') : '';

// Handle settings save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $auto_save = isset($_POST['auto_save']) ? 1 : 0;
    $default_input = $_POST['default_input_format'];
    $default_output = $_POST['default_output_format'];
    $default_transformation = $_POST['default_transformation'];
    $default_indentation = $_POST['default_indentation'];
    
    $stmt = $conn->prepare("UPDATE settings SET auto_save=?, default_input_format=?, default_output_format=?, default_transformation=?, default_indentation=? WHERE user_id=?");
    $stmt->execute([$auto_save, $default_input, $default_output, $default_transformation, $default_indentation, $user_id]);
    
    header("Location: settings.php?success=settings");
    exit();
}

// Handle add mapping
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_mapping'])) {
    $from_key = trim($_POST['from_key']);
    $to_key = trim($_POST['to_key']);
    $from_value = trim($_POST['from_value']);
    $to_value = trim($_POST['to_value']);
    
    if ($from_key && $to_key) {
        $stmt = $conn->prepare("INSERT INTO value_mappings (user_id, from_key, to_key, from_value, to_value) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $from_key, $to_key, $from_value ?: null, $to_value ?: null]);
        header("Location: settings.php?success=mapping_added");
        exit();
    } else {
        header("Location: settings.php?error=missing_fields");
        exit();
    }
}

// Handle delete mapping
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_mapping'])) {
    $stmt = $conn->prepare("DELETE FROM value_mappings WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['mapping_id'], $user_id]);
    header("Location: settings.php?success=mapping_deleted");
    exit();
}

// Load settings
$stmt = $conn->prepare("SELECT * FROM settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$settings = $stmt->get_result()->fetch_assoc();

// Create default settings if none exist
if (!$settings) {
    $conn->prepare("INSERT INTO settings (user_id) VALUES (?)")->execute([$user_id]);
    $settings = [
        'auto_save' => 1,
        'default_input_format' => 'json',
        'default_output_format' => 'yaml',
        'default_transformation' => 'none',
        'default_indentation' => 2
    ];
}

// Load value mappings
$stmt = $conn->prepare("SELECT * FROM value_mappings WHERE user_id = ?");
$stmt->execute([$user_id]);
$mappings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Success message from redirect
$success_messages = [
    'settings' => 'Settings saved.',
    'mapping_added' => 'Mapping added.',
    'mapping_deleted' => 'Mapping deleted.'
];
$success = isset($_GET['success']) ? ($success_messages[$_GET['success']] ?? '') : '';
?>

<div class="settings">
    <h1>Settings</h1>

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <section>
        <h2>General</h2>
        <form method="POST">
            <div class="form-group">
                <label for="auto_save">
                    <input type="checkbox" id="auto_save" name="auto_save" <?php echo $settings['auto_save'] ? 'checked' : ''; ?>>
                    Auto-save every conversion to history
                </label>
            </div>

            <div class="form-group">
                <label for="default_input_format">Default input format</label>
                <select id="default_input_format" name="default_input_format">
                    <option value="json" <?php echo $settings['default_input_format'] === 'json' ? 'selected' : ''; ?>>JSON</option>
                    <option value="yaml" <?php echo $settings['default_input_format'] === 'yaml' ? 'selected' : ''; ?>>YAML</option>
                    <option value="xml" <?php echo $settings['default_input_format'] === 'xml' ? 'selected' : ''; ?>>XML</option>
                </select>
            </div>

            <div class="form-group">
                <label for="default_output_format">Default output format</label>
                <select id="default_output_format" name="default_output_format">
                    <option value="yaml" <?php echo $settings['default_output_format'] === 'yaml' ? 'selected' : ''; ?>>YAML</option>
                    <option value="json" <?php echo $settings['default_output_format'] === 'json' ? 'selected' : ''; ?>>JSON</option>
                    <option value="xml" <?php echo $settings['default_output_format'] === 'xml' ? 'selected' : ''; ?>>XML</option>
                    <option value="csv" <?php echo $settings['default_output_format'] === 'csv' ? 'selected' : ''; ?>>CSV</option>
                    <option value="properties" <?php echo $settings['default_output_format'] === 'properties' ? 'selected' : ''; ?>>.properties</option>
                </select>
            </div>

            <div class="form-group">
                <label for="default_transformation">Default transformation</label>
                <select id="default_transformation" name="default_transformation">
                    <option value="none" <?php echo $settings['default_transformation'] === 'none' ? 'selected' : ''; ?>>None</option>
                    <option value="camel" <?php echo $settings['default_transformation'] === 'camel' ? 'selected' : ''; ?>>camelCase</option>
                    <option value="snake" <?php echo $settings['default_transformation'] === 'snake' ? 'selected' : ''; ?>>snake_case</option>
                    <option value="upper" <?php echo $settings['default_transformation'] === 'upper' ? 'selected' : ''; ?>>UPPER_CASE</option>
                </select>
            </div>

            <div class="form-group">
                <label for="default_indentation">Default indentation (spaces)</label>
                <select id="default_indentation" name="default_indentation">
                    <option value="2" <?php echo $settings['default_indentation'] == 2 ? 'selected' : ''; ?>>2</option>
                    <option value="4" <?php echo $settings['default_indentation'] == 4 ? 'selected' : ''; ?>>4</option>
                </select>
            </div>

            <button type="submit" name="save_settings">Save Settings</button>
        </form>
    </section>

    <section>
        <h2>Value Mappings</h2>
        <p>Configure key/value replacements applied during every conversion.</p>
        
        <table>
            <thead>
                <tr>
                    <th>From Key</th>
                    <th>To Key</th>
                    <th>From Value</th>
                    <th>To Value</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mappings as $mapping): ?>
                <tr>
                    <td><?php echo htmlspecialchars($mapping['from_key']); ?></td>
                    <td><?php echo htmlspecialchars($mapping['to_key']); ?></td>
                    <td><?php echo htmlspecialchars($mapping['from_value'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($mapping['to_value'] ?? ''); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="mapping_id" value="<?php echo $mapping['id']; ?>">
                            <button type="submit" name="delete_mapping">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Add Mapping</h2>
        <form method="POST">
            <div class="form-group">
                <label for="from_key">From key</label>
                <input type="text" id="from_key" name="from_key" placeholder="e.g. ver">
            </div>
            <div class="form-group">
                <label for="to_key">To key</label>
                <input type="text" id="to_key" name="to_key" placeholder="e.g. version">
            </div>
            <div class="form-group">
                <label for="from_value">From value <span>(optional)</span></label>
                <input type="text" id="from_value" name="from_value" placeholder="e.g. 1.0">
            </div>
            <div class="form-group">
                <label for="to_value">To value <span>(optional)</span></label>
                <input type="text" id="to_value" name="to_value" placeholder="e.g. latest">
            </div>
            <button type="submit" name="add_mapping">Add Mapping</button>
        </form>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>