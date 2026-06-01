<?php
require_once 'auth_guard.php';
require_login();
require_once 'db.php';

$user_id = $_SESSION['user_id'];
$error_messages = [
    'missing_fields' => 'From key and To key are required.'
];
$error = isset($_GET['error']) ? ($error_messages[$_GET['error']] ?? '') : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $auto_save            = isset($_POST['auto_save']) ? 1 : 0;
    $default_input        = $_POST['default_input_format'];
    $default_output       = $_POST['default_output_format'];
    $default_trans        = $_POST['default_transformation'];
    $default_indentation  = $_POST['default_indentation'];

    $stmt = $conn->prepare("UPDATE settings SET auto_save=?, default_input_format=?, default_output_format=?, default_transformation=?, default_indentation=? WHERE user_id=?");
    $stmt->execute([$auto_save, $default_input, $default_output, $default_trans, $default_indentation, $user_id]);
    header("Location: settings.php?success=settings");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_mapping'])) {
    $from_key   = trim($_POST['from_key']);
    $to_key     = trim($_POST['to_key']);
    $from_value = trim($_POST['from_value']);
    $to_value   = trim($_POST['to_value']);

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_mapping'])) {
    $stmt = $conn->prepare("DELETE FROM value_mappings WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['mapping_id'], $user_id]);
    header("Location: settings.php?success=mapping_deleted");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$settings = $stmt->get_result()->fetch_assoc();

if (!$settings) {
    $conn->prepare("INSERT INTO settings (user_id) VALUES (?)")->execute([$user_id]);
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

$success_messages = [
    'settings'        => 'Settings saved.',
    'mapping_added'   => 'Mapping added.',
    'mapping_deleted' => 'Mapping deleted.'
];
$success = isset($_GET['success']) ? ($success_messages[$_GET['success']] ?? '') : '';

require_once 'includes/header.php';
require 'views/settings.php';
