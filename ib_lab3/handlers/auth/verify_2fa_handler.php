<?php
require_once __DIR__ . '/../../database/db_connection.php';
require_once __DIR__ . '/../../helpers/session_helper.php';

requireGuest();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/auth/login.php');
    exit();
}

$userId = $_POST['user_id'] ?? null;
$code = trim($_POST['two_factor_code'] ?? '');

if (empty($userId) || empty($code)) {
    $_SESSION['error'] = 'User ID and 2FA code are required.';
    $_SESSION['show_2fa'] = true;
    header('Location: /pages/auth/login.php');
    exit();
}

try {
    $db = connectDatabase();

    $stmt = $db->prepare("SELECT id, username, email FROM users WHERE id = ? AND two_factor_code = ? AND two_factor_code_expires > datetime('now')");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $code, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = 'Invalid or expired 2FA code.';
        $_SESSION['show_2fa'] = true;
        header('Location: /pages/auth/login.php');
        exit();
    }

    // Ako e ok da se izbrishe kodot
    $stmt = $db->prepare("UPDATE users SET two_factor_code = NULL, two_factor_code_expires = NULL WHERE id = ?");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->execute();

    cleanupExpiredRoles($userId);

    $sessionToken = generateSessionToken();

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['session_token'] = $sessionToken;

    // Kje gi otstrani pending od sesijata
    unset($_SESSION['pending_login_user_id']);
    unset($_SESSION['pending_login_username']);
    unset($_SESSION['show_2fa']);

    setSessionTokenCookie($sessionToken);

    header('Location: /index.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = '2FA verification error: ' . $e->getMessage();
    $_SESSION['show_2fa'] = true;
    header('Location: /pages/auth/login.php');
    exit();
}