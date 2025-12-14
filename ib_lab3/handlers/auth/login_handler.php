<?php
require_once __DIR__ . '/../../database/db_connection.php';
require_once __DIR__ . '/../../helpers/session_helper.php';
require_once __DIR__ . '/../../helpers/two_factor_helper.php';

requireGuest();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/auth/login.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validacija
if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'Please enter username and password.';
    header('Location: /pages/auth/login.php');
    exit();
}

try {
    $db = connectDatabase();

    $stmt = $db->prepare("SELECT id, username, email, password_hash, email_verified FROM users WHERE username = ? OR email = ?");
    $stmt->bindValue(1, $username, PDO::PARAM_STR);
    $stmt->bindValue(2, $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = 'Invalid username or password.';
        header('Location: /pages/auth/login.php');
        exit();
    }

    if (!$user['email_verified']) {
        $_SESSION['error'] = 'Please verify your email before logging in. <a href="/pages/auth/register.php">Click here to verify your email</a>.';
        header('Location: /pages/auth/login.php');
        exit();
    }

    if (!password_verify($password, $user['password_hash'])) {
        $_SESSION['error'] = 'Invalid username or password.';
        header('Location: /pages/auth/login.php');
        exit();
    }

    // Generirame 2FA kod za najava na korisnik
    $twoFactorCode = generateVerificationCode(8);
    $expiresAt = date('Y-m-d H:i:s', time() + (10 * 60)); // 10 minuti

    // Go zacuvuva kodot vo db
    $stmt = $db->prepare("UPDATE users SET two_factor_code = ?, two_factor_code_expires = ? WHERE id = ?");
    $stmt->bindValue(1, $twoFactorCode, PDO::PARAM_STR);
    $stmt->bindValue(2, $expiresAt, PDO::PARAM_STR);
    $stmt->bindValue(3, $user['id'], PDO::PARAM_INT);
    $stmt->execute();

    sendVerificationCode($user['email'], $twoFactorCode, '2fa');

    // Go zacuvuva user_id vo sesion
    $_SESSION['pending_login_user_id'] = $user['id'];
    $_SESSION['pending_login_username'] = $user['username'];
    $_SESSION['show_2fa'] = true;
    $_SESSION['success'] = 'Please check your email for the 2FA code.';

    header('Location: /pages/auth/login.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = 'Login error: ' . $e->getMessage();
    header('Location: /pages/auth/login.php');
    exit();
}