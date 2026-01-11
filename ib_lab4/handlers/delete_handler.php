<?php
require_once __DIR__ . '/../database/db_connection.php';
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/authorization_helper.php';

requirePermission('manage_users');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit();
}

$userId = $_POST['user_id'] ?? null;

if (!$userId) {
    $_SESSION['error'] = 'User ID is required.';
    header('Location: /index.php');
    exit();
}

if ($userId == $_SESSION['user_id']) {
    $_SESSION['error'] = 'You cannot delete your own account.';
    header('Location: /index.php');
    exit();
}

try {
    $db = connectDatabase();

    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    $_SESSION['success'] = 'User deleted successfully.';

} catch (Exception $e) {
    $_SESSION['error'] = 'Error deleting user: ' . $e->getMessage();
}

header('Location: /index.php');
exit();