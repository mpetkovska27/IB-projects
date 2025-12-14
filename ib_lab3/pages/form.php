<?php
require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/user_helper.php';
require_once __DIR__ . '/../helpers/authorization_helper.php';

requirePermission('manage_users');

$userId = $_GET['id'] ?? null;
$userData = null;

if ($userId) {
    try {
        $db = require_once __DIR__ . '/../database/db_connection.php';
        $db = connectDatabase();
        $stmt = $db->prepare("SELECT id, username, email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /index.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error loading user data.';
        header('Location: /index.php');
        exit();
    }
}

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error']);
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $userId ? 'Edit' : 'Create'; ?> User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="background: #E0E9EF; min-height: 100vh;" class="d-flex align-items-center">
<div class="container">
    <div class="card shadow" style="max-width: 500px; margin: 0 auto;">
        <div class="card-body p-4">
            <h2 class="card-title mb-4 text-center" style="color: #18211E;"><?php echo $userId ? 'Edit User' : 'Create User'; ?></h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="/handlers/save_handler.php">
                <?php if ($userId): ?>
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                           value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                </div>

                <?php if (!$userId): ?>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                <?php endif; ?>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><?php echo $userId ? 'Update' : 'Create'; ?></button>
                    <a href="/index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>