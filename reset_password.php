<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$pageTitle = 'Reset Password';

// Redirect if already logged in
redirectIfAuth();

$token = sanitizeInput($_GET['token'] ?? '');
$error = $success = '';
$validToken = false;

// Validate token
if (!empty($token)) {
    $user = validateResetToken($token);
    if ($user) {
        $validToken = true;
    } else {
        $error = 'Invalid or expired token. Please request a new password reset link.';
    }
}

// Process reset password form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Server-side validation
    if (empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        // Reset password
        $result = resetPassword($token, $password);
        
        if ($result['status'] === 'success') {
            $success = $result['message'];
            $validToken = false; // Prevent further resets with this token
        } else {
            $error = $result['message'];
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="auth-logo">
                <i class="fas fa-microphone-alt"></i>
                <h1 class="h3 mt-3"><?php echo APP_NAME; ?></h1>
            </div>
            
            <h2 class="auth-title h4">Reset Your Password</h2>
            
            <?php if ($error): ?>
                <?php echo displayError($error); ?>
                <?php if (!$validToken): ?>
                <div class="text-center mt-4">
                    <a href="<?php echo BASE_PATH; ?>/forgot_password.php" class="btn btn-primary">Request New Reset Link</a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <?php echo displaySuccess($success); ?>
                <div class="text-center mt-4">
                    <a href="<?php echo BASE_PATH; ?>/signin.php" class="btn btn-primary">Sign In</a>
                </div>
            <?php elseif ($validToken): ?>
            
            <form id="resetPasswordForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?token=' . urlencode($token); ?>">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="mt-2">
                        <label for="passwordStrength" class="form-label small text-muted d-flex justify-content-between">
                            <span>Password Strength</span>
                            <span id="passwordStrengthText" class="text-muted">Not entered</span>
                        </label>
                        <meter id="passwordStrength" class="w-100" min="0" max="5" value="0" low="2" high="4" optimum="5"></meter>
                    </div>
                    <div class="form-text">Password must be at least 8 characters long.</div>
                </div>
                
                <div class="mb-4">
                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Reset Password</button>
                </div>
            </form>
            
            <div class="mt-4 text-center">
                <p>Remember your password? <a href="<?php echo BASE_PATH; ?>/signin.php" class="text-decoration-none">Sign In</a></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Include validation.js for client-side validation -->
<script src="<?php echo BASE_PATH; ?>/js/validation.js"></script>

<?php include 'includes/footer.php'; ?>
