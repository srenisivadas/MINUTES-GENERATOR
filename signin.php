<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$pageTitle = 'Sign In';

// Redirect if already logged in
redirectIfAuth();

$usernameOrEmail = '';
$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usernameOrEmail = sanitizeInput($_POST['usernameOrEmail'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Server-side validation
    if (empty($usernameOrEmail) || empty($password)) {
        $error = 'All fields are required';
    } else {
        // Authenticate user
        $result = loginUser($usernameOrEmail, $password);
        
        if ($result['status'] === 'success') {
            // Redirect to dashboard
            redirect('/dashboard.php');
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
            
            <h2 class="auth-title h4">Sign In to Your Account</h2>
            
            <?php if ($error): ?>
                <?php echo displayError($error); ?>
            <?php endif; ?>
            
            <form id="signinForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="mb-3">
                    <label for="usernameOrEmail" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="usernameOrEmail" name="usernameOrEmail" value="<?php echo htmlspecialchars($usernameOrEmail); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="/MeetScribeHub/forgot_password.php" class="text-decoration-none small">Forgot Password?</a>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
                </div>
            </form>
            
            <div class="mt-4 text-center">
                <p>Don't have an account? <a href="<?php echo BASE_PATH; ?>/signup.php" class="text-decoration-none">Create Account</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Include validation.js for client-side validation -->
<script src="<?php echo BASE_PATH; ?>/js/validation.js"></script>

<?php include 'includes/footer.php'; ?>
