<?php
require_once __DIR__ . '/../database/db_connection.php';
require_once __DIR__ . '/session_helper.php';
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $db = connectDatabase();
        $stmt = $db->prepare("SELECT id, username, email, first_name, last_name, created_at FROM users WHERE id = ?");
        $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    } catch (Exception $e) {
        return null;
    }
}
function getAllUsers() {
    try {
        $db = connectDatabase();
        $stmt = $db->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $row;
        }
        return $users;
    } catch (Exception $e) {
        return [];
    }
}
