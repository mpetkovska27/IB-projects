<?php
include __DIR__ . '/../db_connection.php';

try {
    $db = connectDatabase();

    $username = 'admin';
    $email = 'admin@admin';
    $password = 'admin';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);

    $stmt = $db->prepare("
        INSERT INTO users (username, email, password_hash, email_verified)
        VALUES (?, ?, ?, 1)
    ");
    $stmt->execute([$username, $email, $passwordHash]);

    $userId = $db->lastInsertId();

    $stmt = $db->prepare("SELECT id FROM roles WHERE name = 'ORG_ADMIN'");
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($role) {
        $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$userId, $role['id']]);
        echo "Admin user created successfully with ORG_ADMIN role.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}