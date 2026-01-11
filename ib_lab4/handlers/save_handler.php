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
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($email)) {
    $_SESSION['error'] = 'Username and email are required.';
    header('Location: /pages/form.php' . ($userId ? '?id=' . $userId : ''));
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format.';
    header('Location: /pages/form.php' . ($userId ? '?id=' . $userId : ''));
    exit();
}

try {
    $db = connectDatabase();

    if ($userId) {
        $stmt = $db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $userId]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Username or email already exists.';
            header('Location: /pages/form.php?id=' . $userId);
            exit();
        }

        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, password_hash = ? WHERE id = ?");
            $stmt->execute([$username, $email, $passwordHash, $userId]);
        } else {
            $stmt = $db->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $userId]);
        }

        $_SESSION['success'] = 'User updated successfully.';
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Username or email already exists.';
            header('Location: /pages/form.php');
            exit();
        }

        if (empty($password)) {
            $_SESSION['error'] = 'Password is required for new users.';
            header('Location: /pages/form.php');
            exit();
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, email_verified) VALUES (?, ?, ?, 1)");
        $stmt->execute([$username, $email, $passwordHash]);

        $newUserId = $db->lastInsertId();

        $stmt = $db->prepare("SELECT id FROM roles WHERE name = 'ORG_USER'");
        $stmt->execute();
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($role) {
            $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$newUserId, $role['id']]);
        }

        $_SESSION['success'] = 'User created successfully.';
    }

    header('Location: /index.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
    header('Location: /pages/form.php' . ($userId ? '?id=' . $userId : ''));
    exit();
}