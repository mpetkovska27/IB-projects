<?php
require_once __DIR__ . '/helpers/session_helper.php';
require_once __DIR__ . '/helpers/user_helper.php';
require_once __DIR__ . '/helpers/authorization_helper.php';

if (isLoggedIn()) {
    cleanupExpiredRoles($_SESSION['user_id']);
}

$user = null;
$allUsers = [];

if (isLoggedIn()) {
    $user = getCurrentUser();
    if (hasPermission($_SESSION['user_id'], 'view_all_users')) {
        $allUsers = getAllUsers();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role-Based Access Control and Authorization System - Lab Exercise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="d-flex flex-column" style="background: #E0E9EF;">
<nav class="navbar navbar-expand-lg navbar-light bg-white ">
    <div class="container-fluid px-4">
        <div>
            <h2 class="mb-0" style="color: #89BFD0;">Auth System</h2>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?php if ($user): ?>
                <span class="text-dark">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</span>
                <a href="/handlers/auth/logout_handler.php" class="btn btn-secondary text-white">Logout</a>
            <?php else: ?>
                <a href="/pages/auth/login.php" class="btn btn-primary text-white">Login</a>
                <a href="/pages/auth/register.php" class="btn btn-outline-primary">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if (!$user || hasPermission($_SESSION['user_id'], 'view_public')): ?>
    <section class="text-white py-5" style="background: linear-gradient(135deg, #89BFD0 0%, #CBD0D6 100%);">
        <div class="container text-center">
            <h1 class="display-4 mb-3">Role-Based Access Control and Authorization System</h1>
            <p class="lead">Lab Exercise 3, made by Martina Petkovska - 223313, that implements a complete role-based access control (RBAC) system with hierarchical organization roles, resource-specific roles, and just-in-time (JIT) access management.</p>
        </div>
    </section>
<?php endif; ?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <?php if (!$user || hasPermission($_SESSION['user_id'], 'view_public')): ?>
            <section class="card mb-4 border">
                <div class="card-body p-4">
                    <h2 class="card-title mb-3 pb-2 border-bottom" style="color: #18211E; border-color: #89BFD0 !important;">About This Lab Exercise</h2>
                    <p class="card-text" style="color: #18211E; line-height: 1.6;">
                        This application demonstrates a comprehensive authorization system implemented as part of Laboratory Exercise 3. The system provides:
                        <br/>- secure user authentication with two-factor authentication (2FA) and email verification,
                        <br/>- role-based access control (RBAC) with hierarchical organization-level roles (ORG_ADMIN, ORG_USER, ORG_GUEST),
                        <br/>- resource-specific roles (USER_READER, USER_WRITER) with least privilege principle,
                        <br/>- just-in-time (JIT) access management with automatic expiration and revocation,
                        <br/>- permission-based access control for different sections of the application,
                        <br/>- and secure user management with CRUD operations protected by role permissions.
                    </p>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($user && hasPermission($_SESSION['user_id'], 'view_account')): ?>
            <section class="card mb-4 border">
                <div class="card-body p-4">
                    <h2 class="card-title mb-3 pb-2 border-bottom" style="color: #18211E; border-color: #89BFD0 !important;">Your Account Information</h2>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex py-2">
                            <span class="fw-semibold me-3" style="min-width: 120px; color: #18211E;">Username:</span>
                            <span style="color: #18211E;"><?php echo htmlspecialchars($user['username'] ?? ''); ?></span>
                        </div>
                        <div class="d-flex py-2">
                            <span class="fw-semibold me-3" style="min-width: 120px; color: #18211E;">Email:</span>
                            <span style="color: #18211E;"><?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
                        </div>
                        <div class="d-flex py-2">
                            <span class="fw-semibold me-3" style="min-width: 120px; color: #18211E;">Registered on:</span>
                            <span style="color: #18211E;"><?php echo isset($user['created_at']) ? date('F j, Y \a\t g:i A', strtotime($user['created_at'])) : ''; ?></span>
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <form method="POST" action="/handlers/request_jit_handler.php" class="d-inline">
                            <input type="hidden" name="role" value="USER_READER">
                            <button type="submit" class="btn btn-sm btn-outline-primary">Request Reader Permission</button>
                        </form>
                        <form method="POST" action="/handlers/request_jit_handler.php" class="d-inline">
                            <input type="hidden" name="role" value="USER_WRITER">
                            <button type="submit" class="btn btn-sm btn-outline-success">Request Writer Permission</button>
                        </form>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($user && hasPermission($_SESSION['user_id'], 'view_all_users')): ?>
            <section class="card mb-4 border">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="card-title mb-0 pb-2 border-bottom" style="color: #18211E; border-color: #89BFD0 !important;">Registered Users</h2>
                        <?php if (hasPermission($_SESSION['user_id'], 'manage_users')): ?>
                            <a href="/pages/form.php" class="btn btn-sm btn-primary">Create User</a>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($allUsers)): ?>
                        <p class="text-center py-4" style="color: #18211E;">No users registered yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead style="background: #89BFD0; color: white;">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Registered</th>
                                    <?php if (hasPermission($_SESSION['user_id'], 'manage_users')): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($allUsers as $u): ?>
                                    <tr <?php echo ($user && $u['id'] == $user['id']) ? 'class="table-light fw-bold"' : ''; ?>>
                                        <td><?php echo htmlspecialchars($u['id']); ?></td>
                                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                                        <?php if (hasPermission($_SESSION['user_id'], 'manage_users')): ?>
                                            <td>
                                                <a href="/pages/form.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <form method="POST" action="/handlers/delete_handler.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-center mt-2 mb-0 small" style="color: #18211E;">Total users: <?php echo count($allUsers); ?></p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<footer class="bg-dark text-white text-center py-3 mt-auto">
    <div class="container">
        <p class="mb-0 text-white-50">&copy; 2025 User Authentication System - Laboratory Exercise 3</p>
    </div>
</footer>
</body>
</html>