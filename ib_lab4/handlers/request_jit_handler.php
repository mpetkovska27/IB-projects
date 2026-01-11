<?php
require_once __DIR__ . '/../database/db_connection.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/authorization_helper.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit();
}

$roleName = $_POST['role'] ?? '';

if (!in_array($roleName, ['USER_READER', 'USER_WRITER'])) {
    $_SESSION['error'] = 'Invalid role request.';
    header('Location: /index.php');
    exit();
}

if (assignJITRole($_SESSION['user_id'], $roleName, 10)) {
    $_SESSION['success'] = ucfirst(str_replace('_', ' ', strtolower($roleName))) . ' permission granted for 10 seconds.';
} else {
    $_SESSION['error'] = 'Failed to assign role.';
}

header('Location: /index.php');
exit();