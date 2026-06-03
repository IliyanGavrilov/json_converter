<section class="settings">
    <h1>Settings</h1>

    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <section>
        <h2>General</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
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
                    <option value="xml"  <?php echo $settings['default_input_format'] === 'xml'  ? 'selected' : ''; ?>>XML</option>
                </select>
            </div>

            <div class="form-group">
                <label for="default_output_format">Default output format</label>
                <select id="default_output_format" name="default_output_format">
                    <option value="yaml"       <?php echo $settings['default_output_format'] === 'yaml'       ? 'selected' : ''; ?>>YAML</option>
                    <option value="json"       <?php echo $settings['default_output_format'] === 'json'       ? 'selected' : ''; ?>>JSON</option>
                    <option value="xml"        <?php echo $settings['default_output_format'] === 'xml'        ? 'selected' : ''; ?>>XML</option>
                    <option value="csv"        <?php echo $settings['default_output_format'] === 'csv'        ? 'selected' : ''; ?>>CSV</option>
                    <option value="properties" <?php echo $settings['default_output_format'] === 'properties' ? 'selected' : ''; ?>>.properties</option>
                </select>
            </div>

            <div class="form-group">
                <label for="default_transformation">Default transformation</label>
                <select id="default_transformation" name="default_transformation">
                    <option value="none" <?php echo $settings['default_transformation'] === 'none' ? 'selected' : ''; ?>>None</option>
                    <option value="camelCase" <?php echo $settings['default_transformation'] === 'camelCase' ? 'selected' : ''; ?>>camelCase</option>
                    <option value="PascalCase" <?php echo $settings['default_transformation'] === 'PascalCase' ? 'selected' : ''; ?>>PascalCase</option>
                    <option value="snake_case" <?php echo $settings['default_transformation'] === 'snake_case' ? 'selected' : ''; ?>>snake_case</option>
                    <option value="kebab-case" <?php echo $settings['default_transformation'] === 'kebab-case' ? 'selected' : ''; ?>>kebab-case</option>
                    <option value="UPPER_CASE" <?php echo $settings['default_transformation'] === 'UPPER_CASE' ? 'selected' : ''; ?>>UPPER_CASE</option>
                </select>
            </div>

            <div class="form-group">
                <label for="default_indentation">Default indentation (spaces)</label>
                <select id="default_indentation" name="default_indentation">
                    <option value="2" <?php echo (int)$settings['default_indentation'] === 2 ? 'selected' : ''; ?>>2</option>
                    <option value="4" <?php echo (int)$settings['default_indentation'] === 4 ? 'selected' : ''; ?>>4</option>
                </select>
            </div>

            <button type="submit" name="save_settings">Save Settings</button>
        </form>
    </section>

    <section>
        <h2>Value Mappings</h2>
        <p>Configure key/value replacements applied during every conversion.</p>

        <div class="mapping-actions">
            <form method="POST" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <button type="submit" name="export_mappings">Export as JSON</button>
            </form>
            <form method="POST" enctype="multipart/form-data" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="file" name="mappings_file" accept=".json" required>
                <button type="submit" name="import_mappings">Import JSON</button>
            </form>
        </div>
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
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="mapping_id" value="<?php echo htmlspecialchars($mapping['id']); ?>">
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
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
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
</section>

<?php require_once 'includes/footer.php'; ?>
