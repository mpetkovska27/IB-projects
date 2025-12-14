<?php
require_once __DIR__ . '/../../helpers/session_helper.php';

requireGuest();

// Check if user wants to clear 2FA and go back to login
if (isset($_GET['clear_2fa'])) {
    unset($_SESSION['show_2fa']);
    unset($_SESSION['pending_login_user_id']);
    unset($_SESSION['pending_login_username']);
    header('Location: /pages/auth/login.php');
    exit();
}

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
$show2FA = $_SESSION['show_2fa'] ?? false;
$userId = $_SESSION['pending_login_user_id'] ?? null;

// Clear messages after displaying
unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $show2FA ? 'Two-Factor Authentication' : 'Login'; ?> - User Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="background: #89BFD0; min-height: 100vh;" class="d-flex align-items-center">
<div class="container">
    <div class="card shadow" style="max-width: 450px; margin: 0 auto;">
        <div class="card-body p-4">
            <?php if ($show2FA): ?>
                <h1 class="card-title text-center mb-4" style="color: #18211E; font-size: 1.8rem;">Two-Factor Authentication</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <p class="mb-2">Please enter the 2FA code sent to your email address.</p>
                <p class="small text-muted mb-3">Check your terminal for the 2FA code (if running in test mode).</p>

                <form method="POST" action="/handlers/auth/verify_2fa_handler.php">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">
                    <div class="mb-3">
                        <label for="two_factor_code" class="form-label fw-semibold" style="color: #18211E;">2FA Code:</label>
                        <input type="text" class="form-control" id="two_factor_code" name="two_factor_code" required
                               pattern="[0-9]{8}" maxlength="8"
                               placeholder="00000000" autocomplete="off">
                    </div>

                    <button type="submit" class="btn w-100 text-white" style="background: #89BFD0;">Verify & Login</button>
                </form>

                <p class="text-center mt-3 mb-0">
                    <a href="/pages/auth/login.php?clear_2fa=1" style="color: #89BFD0; text-decoration: none; font-weight: 600;">Back to Log In</a>
                </p>
            <?php else: ?>
                    <h1 class="card-title text-center mb-4" style="color: #18211E; font-size: 1.8rem;">Login</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="/handlers/auth/login_handler.php">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold" style="color: #18211E;">Username or Email:</label>
                            <input type="text" class="form-control" id="username" name="username" required 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold" style="color: #18211E;">Password:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn w-100 text-white" style="background: #89BFD0;">Log In</button>
                    </form>

                    <p class="text-center mt-3 mb-0" style="color: #18211E;">
                        Don't have an account? <a href="/pages/auth/register.php" style="color: #89BFD0; text-decoration: none; font-weight: 600;">Register</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
