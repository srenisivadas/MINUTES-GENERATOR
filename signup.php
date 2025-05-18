<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$pageTitle = 'Sign Up';

// Check if it's an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    header('Content-Type: application/json');
}

// Redirect if already logged in
redirectIfAuth();

$username = $email = '';
$error = $success = '';
$redirect = false; // For normal redirection after success

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // Server-side validation
        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $error = 'All fields are required';
        } elseif (!isValidEmail($email)) {
            $error = 'Invalid email format';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } else {
            $result = registerUser($username, $email, $password);

            if ($result['status'] === 'success') {
                $success = $result['message'];
                $username = $email = '';
                $redirect = true;

                if ($isAjax) {
                    echo json_encode([
                        'success' => true,
                        'message' => $success,
                        'redirect' => BASE_PATH . '/signin.php'
                    ]);
                    exit;
                }
            } else {
                $error = $result['message'];
                if ($isAjax) {
                    echo json_encode([
                        'success' => false,
                        'error' => $error
                    ]);
                    exit;
                }
            }
        }

        if ($error && $isAjax) {
            echo json_encode([
                'success' => false,
                'error' => $error
            ]);
            exit;
        }
    } catch (Exception $e) {
        $error = 'An unexpected error occurred. Please try again.';
        error_log("Error in signup: " . $e->getMessage());

        if ($isAjax) {
            echo json_encode([
                'success' => false,
                'error' => $error
            ]);
            exit;
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

            <h2 class="auth-title h4">Create Your Account</h2>

            <?php if ($error): ?>
                <?php echo displayError($error); ?>
            <?php endif; ?>

            <?php if ($success): ?>
                <?php echo displaySuccess($success); ?>
                <div class="text-center mt-4">
                    <a href="<?php echo BASE_PATH; ?>/signin.php" class="btn btn-primary">Sign In</a>
                </div>
                <script>
                    setTimeout(() => {
                        window.location.href = "<?php echo BASE_PATH; ?>/signin.php";
                    }, 3000);
                </script>
            <?php else: ?>

            <form id="signupForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                        value="<?php echo htmlspecialchars($username); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                        value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="mt-2">
                        <label for="passwordStrength" class="form-label small text-muted d-flex justify-content-between">
                            <span>Password Strength</span>
                            <span id="passwordStrengthText" class="text-muted">Not entered</span>
                        </label>
                        <meter id="passwordStrength" class="w-100" min="0" max="5" value="0" 
                               low="2" high="4" optimum="5"></meter>
                    </div>
                    <div class="form-text">Password must be at least 8 characters long.</div>
                </div>

                <div class="mb-4">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Create Account</button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <p>Already have an account? 
                    <a href="<?php echo BASE_PATH; ?>/signin.php" class="text-decoration-none">Sign In</a>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?php echo BASE_PATH; ?>/js/validation.js"></script>

<?php include 'includes/footer.php'; ?>
