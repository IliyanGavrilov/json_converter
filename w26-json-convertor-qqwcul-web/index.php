<?php
require_once 'auth_guard.php';
require_login();
require_once 'convert.php';
require_once 'transformations.php';
require_once 'db.php';

$output = '';
$error = '';
$input = '';
$fromFormat = '';
$toFormat = '';
$transformation = 'none';
$user_id = $_SESSION['user_id'];

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
    if ($format === 'json') return ['json', 'application/json'];
    if ($format === 'yaml') return ['yaml', 'application/x-yaml'];
    if ($format === 'xml')  return ['xml',  'application/xml'];
    if ($format === 'csv')  return ['csv',  'text/csv'];
    return [$format, 'text/plain'];
}

function sendFile($content, $format) {
    [$ext, $mime] = mimeForFormat($format);
    $filename = 'converted_' . date('Y-m-d_H-i-s') . '.' . $ext;
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
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

    if (isset($_POST['reload_conversion']) && !empty($_POST['input_content'])) {
        try {
            $input          = $_POST['input_content'];
            $fromFormat     = $_POST['from_format'];
            $toFormat       = $_POST['to_format'];
            $transformation = 'none';
            $pretty_print   = false;

            $data   = parseInput($input, $fromFormat);
            $data   = applyValueMappings($data, $mappings);
            $data   = applyTransformation($data, $transformation);
            $output = outputFormat($data, $toFormat, [
                'indentation'  => (int)$settings['default_indentation'],
                'pretty_print' => $pretty_print
            ]);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
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

require_once 'includes/header.php';
require 'views/converter.php';
