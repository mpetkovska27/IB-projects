<?php
require_once __DIR__ . '/../database/db_connection.php';
require_once __DIR__ . '/session_helper.php';

function getUserRoles($userId) {
    try {
        cleanupExpiredRoles($userId);
        $db = connectDatabase();
        $stmt = $db->prepare("
            SELECT r.id, r.name, r.type, r.hierarchy_level, ur.expires_at
            FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            WHERE ur.user_id = ? AND (ur.expires_at IS NULL OR datetime(ur.expires_at) > datetime('now'))
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getUserPermissions($userId) {
    try {
        cleanupExpiredRoles($userId);
        $db = connectDatabase();
        $stmt = $db->prepare("
            SELECT DISTINCT p.id, p.name, p.description
            FROM user_roles ur
            JOIN role_permissions rp ON ur.role_id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE ur.user_id = ? AND (ur.expires_at IS NULL OR datetime(ur.expires_at) > datetime('now'))
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function hasRole($userId, $roleName) {
    try {
        cleanupExpiredRoles($userId);
        $db = connectDatabase();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            WHERE ur.user_id = ? AND r.name = ? AND (ur.expires_at IS NULL OR datetime(ur.expires_at) > datetime('now'))
        ");
        $stmt->execute([$userId, $roleName]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function hasPermission($userId, $permissionName) {
    try {
        cleanupExpiredRoles($userId);
        $db = connectDatabase();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM user_roles ur
            JOIN role_permissions rp ON ur.role_id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE ur.user_id = ? AND p.name = ? AND (ur.expires_at IS NULL OR datetime(ur.expires_at) > datetime('now'))
        ");
        $stmt->execute([$userId, $permissionName]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function assignJITRole($userId, $roleName, $expiresInSeconds = 10) {
    $db = connectDatabase();

    $stmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt->execute([$roleName]);
    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$role) return false;

    $expiresAt = gmdate('Y-m-d H:i:s', time() + $expiresInSeconds);

    // Remove any previous JIT role for this user
    $stmt = $db->prepare("DELETE FROM user_roles WHERE user_id = ? AND role_id = ? AND expires_at IS NOT NULL");
    $stmt->execute([$userId, $role['id']]);

    // Assign new role
    $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $role['id'], $expiresAt]);

    return true;
}

function requirePermission($permissionName) {
    if (!isLoggedIn()) {
        header('Location: /pages/auth/login.php');
        exit();
    }

    if (!hasPermission($_SESSION['user_id'], $permissionName)) {
        header('Location: /index.php');
        exit();
    }
}