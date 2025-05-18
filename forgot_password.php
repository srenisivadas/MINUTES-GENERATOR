<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$pageTitle = 'Forgot Password';

// Redirect if already logged in
redirectIfAuth();

$email = '';
$error = $success = '';

// Process forgot password form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    
    // Server-side validation
    if (empty($email)) {
        $error = 'Email address is required';
    } elseif (!isValidEmail($email)) {
        $error = 'Invalid email format';
    } else {
        // Start password reset process
        $result = startPasswordReset($email);
        
        // Always show success message to prevent email enumeration
        $success = $result['message'];
        
        // Clear email field
        $email = '';
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
            
            <h2 class="auth-title h4">Forgot Your Password?</h2>
            <p class="text-muted text-center mb-4">Enter your email address and we'll send you a link to reset your password.</p>
            
            <?php if ($error): ?>
                <?php echo displayError($error); ?>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <?php echo displaySuccess($success); ?>
                <div class="text-center mt-4">
                    <a href="<?php echo BASE_PATH; ?>/signin.php" class="btn btn-primary">Back to Sign In</a>
                </div>
            <?php else: ?>
            
            <form id="forgotPasswordForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Send Reset Link</button>
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
