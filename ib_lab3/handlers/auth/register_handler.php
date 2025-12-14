<?php
require_once __DIR__ . '/../../database/db_connection.php';
require_once __DIR__ . '/../../helpers/session_helper.php';
require_once __DIR__ . '/../../helpers/two_factor_helper.php';
require_once __DIR__ . '/../../helpers/password_validator.php';

requireGuest();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/auth/register.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Passwords do not match.';
    header('Location: /pages/auth/register.php');
    exit();
}

// Validacija
if (empty($username) || empty($email) || empty($password)) {
    $_SESSION['error'] = 'All fields are required.';
    header('Location: /pages/auth/register.php');
    exit();
}

// Validacija za email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format.';
    header('Location: /pages/auth/register.php');
    exit();
}

// Validacija na lozinka
$passwordValidation = validatePassword($password);
if (!$passwordValidation['valid']) {
    $_SESSION['error'] = $passwordValidation['message'];
    header('Location: /pages/auth/register.php');
    exit();
}

try {
    $db = connectDatabase();

    // Dali postoi userot
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bindValue(1, $username, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Username is already taken.';
        header('Location: /pages/auth/register.php');
        exit();
    }

    // Dali postoi emailot
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bindValue(1, $email, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Email address is already registered.';
        header('Location: /pages/auth/register.php');
        exit();
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Verifikaciski kod generira tuka
    $verificationCode = generateVerificationCode(8);
    $expiresAt = date('Y-m-d H:i:s', time() + (15 * 60)); // istekuva za 15 min

    // Vnesuvanje nov korisnik
    $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, email_verified, email_verification_code, email_verification_expires) VALUES (?, ?, ?, 0, ?, ?)");
    $stmt->bindValue(1, $username, PDO::PARAM_STR);
    $stmt->bindValue(2, $email, PDO::PARAM_STR);
    $stmt->bindValue(3, $passwordHash, PDO::PARAM_STR);
    $stmt->bindValue(4, $verificationCode, PDO::PARAM_STR);
    $stmt->bindValue(5, $expiresAt, PDO::PARAM_STR);
    $stmt->execute();

    $userId = $db->lastInsertId();
    //avtomatsko da dodade uloga na ORG_USER pri registracija
    $stmt = $db->prepare("SELECT id FROM roles WHERE name = 'ORG_USER'");
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($role) {
        $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$userId, $role['id']]);
    }

    sendVerificationCode($email, $verificationCode, 'registration');

    $_SESSION['success'] = 'Registration successful! Please check your email for verification code.';
    $_SESSION['show_verification'] = true;
    $_SESSION['verification_user_id'] = $userId;
    header('Location: /pages/auth/register.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = 'Registration error: ' . $e->getMessage();
    header('Location: /pages/auth/register.php');
    exit();
}