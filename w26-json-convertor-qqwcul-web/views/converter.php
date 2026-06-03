<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<style>
    .CodeMirror { border: 1px solid #ccc; font-size: 14px; }
</style>

<section class="converter">
    <h1>Converter</h1>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="converterForm">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

        <div class="file-import">
            <label for="import_file">Import from file:</label>
            <input type="file" name="import_file" id="import_file" accept=".json,.yaml,.yml,.xml,.csv,.properties,.ini,.txt">
            <button type="submit" name="import_submit">Read File</button>
        </div>

        <div class="form-formats">
            <label for="from_format">From</label>
            <select id="from_format" name="from_format">
                <option value="json" <?php echo $current_from === 'json' ? 'selected' : ''; ?>>JSON</option>
                <option value="yaml" <?php echo $current_from === 'yaml' ? 'selected' : ''; ?>>YAML</option>
                <option value="xml" <?php echo $current_from === 'xml' ? 'selected' : ''; ?>>XML</option>
                <option value="csv" <?php echo $current_from === 'csv' ? 'selected' : ''; ?>>CSV</option>
                <option value="properties" <?php echo $current_from === 'properties' ? 'selected' : ''; ?>>.properties</option>
                <option value="ini" <?php echo $current_from === 'ini' ? 'selected' : ''; ?>>.ini</option>
            </select>

            <button type="button" id="swap-formats" title="Swap formats">⇄</button>

            <label for="to_format">To</label>
            <select id="to_format" name="to_format">
                <option value="yaml" <?php echo $current_to === 'yaml' ? 'selected' : ''; ?>>YAML</option>
                <option value="json" <?php echo $current_to === 'json' ? 'selected' : ''; ?>>JSON</option>
                <option value="xml" <?php echo $current_to === 'xml' ? 'selected' : ''; ?>>XML</option>
                <option value="csv" <?php echo $current_to === 'csv' ? 'selected' : ''; ?>>CSV</option>
                <option value="properties" <?php echo $current_to === 'properties' ? 'selected' : ''; ?>>.properties</option>
                <option value="ini" <?php echo $current_to === 'ini' ? 'selected' : ''; ?>>.ini</option>
            </select>
        </div>

        <div class="form-transformation">
            <label for="transformation">Key transformation:</label>
            <select id="transformation" name="transformation">
                <option value="none" <?php echo $current_trans === 'none' ? 'selected' : ''; ?>>None</option>
                <option value="camelCase" <?php echo $current_trans === 'camelCase' ? 'selected' : ''; ?>>camelCase</option>
                <option value="PascalCase" <?php echo $current_trans === 'PascalCase' ? 'selected' : ''; ?>>PascalCase</option>
                <option value="snake_case" <?php echo $current_trans === 'snake_case' ? 'selected' : ''; ?>>snake_case</option>
                <option value="kebab-case" <?php echo $current_trans === 'kebab-case' ? 'selected' : ''; ?>>kebab-case</option>
                <option value="UPPER_CASE" <?php echo $current_trans === 'UPPER_CASE' ? 'selected' : ''; ?>>UPPER_CASE</option>
            </select>
        </div>

        <div class="form-options">
            <label>
                <input type="checkbox" name="pretty_print" value="1" <?php echo $current_pretty ? 'checked' : ''; ?>>
                Pretty-print output
            </label>
            <label>
                Line numbers:
                <select id="line-number-style">
                    <option value="arabic">Arabic (1, 2, 3)</option>
                    <option value="roman">Roman (I, II, III)</option>
                    <option value="none">None</option>
                </select>
            </label>
        </div>

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
    <div id="output-editor"></div>
    <button type="button" id="use-as-input">↑ Use as input</button>

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

</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/yaml/yaml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/properties/properties.min.js"></script>
<script>
    var outputContent = <?php echo json_encode($output ?? ''); ?>;
    var outputFormat  = <?php echo json_encode($toFormat ?? $current_to); ?>;
</script>
<script src="public/script.js"></script>
<?php require_once 'includes/footer.php'; ?>
