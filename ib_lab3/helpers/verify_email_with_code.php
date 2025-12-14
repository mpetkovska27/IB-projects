<?php
require_once __DIR__ . '/../database/db_connection.php';
require_once __DIR__ . '/../helpers/session_helper.php';
requireGuest();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/auth/register.php');
    exit();
}

$userId = $_POST['user_id'] ?? null;
$code = trim($_POST['verification_code'] ?? '');

if (empty($userId) || empty($code)) {
    $_SESSION['error'] = 'User ID and verification code are required.';
    $_SESSION['show_verification'] = true;
    header('Location: /pages/auth/register.php');
    exit();
}

try {
    $db = connectDatabase();

    // Go proveruvame kodot
    $stmt = $db->prepare("SELECT id, email_verified FROM users WHERE id = ? AND email_verification_code = ? AND email_verification_expires > datetime('now')");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $code, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = 'Invalid or expired verification code.';
        $_SESSION['show_verification'] = true;
        header('Location: /pages/auth/register.php');
        exit();
    }

    if ($user['email_verified']) {
        $_SESSION['error'] = 'Email is already verified.';
        header('Location: /pages/auth/login.php');
        exit();
    }

    // Ako e sve ok kje go oznacime korisnikot kako verificiram i kje go izbrisheme kodot
    $stmt = $db->prepare("UPDATE users SET email_verified = 1, email_verification_code = NULL, email_verification_expires = NULL WHERE id = ?");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['success'] = 'Email verified successfully! You can now log in.';
    unset($_SESSION['show_verification']);
    header('Location: /pages/auth/login.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = 'Verification error: ' . $e->getMessage();
    $_SESSION['show_verification'] = true;
    header('Location: /pages/auth/register.php');
    exit();
}