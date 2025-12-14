<?php
require_once __DIR__ . '/../../helpers/session_helper.php';

requireGuest();

if (isset($_GET['clear_verification'])) {
    unset($_SESSION['show_verification']);
    unset($_SESSION['verification_user_id']);
    header('Location: /pages/auth/register.php');
    exit();
}

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
$showVerification = $_SESSION['show_verification'] ?? false;
$userId = $_SESSION['verification_user_id'] ?? null;

// Clear messages after displaying
unset($_SESSION['error']);
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $showVerification ? 'Verify Email' : 'Register'; ?> - User Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="background: #89BFD0; min-height: 100vh;" class="d-flex align-items-center">
<div class="container">
    <div class="card shadow" style="max-width: 450px; margin: 0 auto;">
        <div class="card-body p-4">
            <?php if ($showVerification): ?>
                <h1 class="card-title text-center mb-4" style="color: #18211E; font-size: 1.8rem;">Verify Your Email</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <p class="mb-3">Please enter the verification code sent to your email address.</p>

                <form method="POST" action="/helpers/verify_email_with_code.php">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">
                    <div class="mb-3">
                        <label for="verification_code" class="form-label fw-semibold" style="color: #18211E;">Verification Code:</label>
                        <input type="text" class="form-control" id="verification_code" name="verification_code" required
                               pattern="^\d{8}$" maxlength="8"
                               placeholder="00000000" autocomplete="off">
                    </div>

                    <button type="submit" class="btn w-100 text-white" style="background: #89BFD0;">Verify Email</button>
                </form>

                <p class="text-center mt-3 mb-0">
                    <a href="/pages/auth/register.php?clear_verification=1" style="color: #89BFD0; text-decoration: none; font-weight: 600;">Back to Registration</a>
                </p>
            <?php else: ?>
                <h1 class="card-title text-center mb-4" style="color: #18211E; font-size: 1.8rem;">Register</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="/handlers/auth/register_handler.php">
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold" style="color: #18211E;">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" required
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               minlength="3" maxlength="50">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold" style="color: #18211E;">Email Address:</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold" style="color: #18211E;">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required
                               minlength="9">
                        <small class="form-text text-muted d-block mt-1">Password must be longer than 8 characters and contain at least one uppercase letter, one lowercase letter, one number, and one special character.</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label fw-semibold" style="color: #18211E;">Confirm Password:</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                               minlength="9">
                    </div>

                    <button type="submit" class="btn w-100 text-white" style="background: #89BFD0;">Register</button>
                </form>

                <p class="text-center mt-3 mb-0" style="color: #18211E;">
                    Already have an account? <a href="/pages/auth/login.php" style="color: #89BFD0; text-decoration: none; font-weight: 600;">Log in</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>