<?php
include __DIR__ . '/../db_connection.php';

try{
    $db = connectDatabase();

    $db->exec("
CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    type TEXT NOT NULL CHECK(type IN ('organization', 'resource')),
    hierarchy_level INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");


    $roles = [

        ['name' => 'ORG_ADMIN', 'type' => 'organization', 'hierarchy_level' => 1],
        ['name' => 'ORG_USER', 'type' => 'organization', 'hierarchy_level' => 2],
        ['name' => 'ORG_GUEST', 'type' => 'organization', 'hierarchy_level' => 3],

        ['name' => 'USER_READER', 'type' => 'resource', 'hierarchy_level' => 99],
        ['name' => 'USER_WRITER', 'type' => 'resource', 'hierarchy_level' => 99]
    ];
    $stmt = $db->prepare("INSERT OR IGNORE INTO roles (name, type, hierarchy_level) VALUES (:name, :type, :hierarchy_level)");

    foreach ($roles as $role) {
        $stmt->execute([
            'name' => $role['name'],
            'type' => $role['type'],
            'hierarchy_level' => $role['hierarchy_level']
        ]);
    }

    $db->exec("
CREATE TABLE IF NOT EXISTS permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");

    $permissions = [
        ['name' => 'view_public', 'description' => 'View public sections (hero, about, navbar)'],
        ['name' => 'view_dashboard', 'description' => 'Access dashboard index.php'],
        ['name' => 'view_account', 'description' => 'View own account details'],
        ['name' => 'view_all_users', 'description' => 'View registered users table'],
        ['name' => 'manage_users', 'description' => 'Create, edit, or delete users']
    ];
    $stmt = $db->prepare("INSERT OR IGNORE INTO permissions (name, description) VALUES (:name, :description)");
    foreach ($permissions as $perm) {
        $stmt->execute([
            ':name' => $perm['name'],
            ':description' => $perm['description']
        ]);
    }

    $db->exec("
CREATE TABLE IF NOT EXISTS role_permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    role_id INTEGER NOT NULL,
    permission_id INTEGER NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE(role_id, permission_id)
)
");

    $db->exec("
CREATE TABLE IF NOT EXISTS user_roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    role_id INTEGER NOT NULL,
    expires_at DATETIME DEFAULT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
)
");

    function getId($db, $table, $name) {
        $stmt = $db->prepare("SELECT id FROM $table WHERE name = :name");
        $stmt->execute([':name' => $name]);
        return $stmt->fetchColumn();
    }
    $rolePermissions = [
        'ORG_GUEST' => ['view_public'],
        'ORG_USER' => ['view_public', 'view_dashboard', 'view_account'],
        'ORG_ADMIN' => ['view_public', 'view_dashboard', 'view_account', 'view_all_users', 'manage_users'],
        'USER_READER' => ['view_all_users'],
        'USER_WRITER' => ['manage_users']
    ];

    $stmt = $db->prepare("INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");

    foreach ($rolePermissions as $roleName => $perms) {
        $roleId = getId($db, 'roles', $roleName);
        foreach ($perms as $permName) {
            $permId = getId($db, 'permissions', $permName);
            $stmt->execute([
                ':role_id' => $roleId,
                ':permission_id' => $permId
            ]);
        }
    }

} catch (PDOException $e) {
    echo $e->getMessage();
}

