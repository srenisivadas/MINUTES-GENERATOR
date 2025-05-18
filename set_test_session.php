<?php
/**
 * MeetScribe Local Setup - Step 3
 * This file creates a test user account and logs in for testing
 */

// Start or resume session
session_start();

// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>MeetScribe Local Setup - Step 3: Create Test Account</h1>";

// Include configuration
require_once 'includes/config.php';
require_once 'includes/functions.php';

$message = '';
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Try to connect to the database
    try {
        $dbOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        
        if (DB_TYPE === 'pgsql') {
            if (!empty(DB_DSN)) {
                $dsn = DB_DSN;
            } else {
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            }
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $dbOptions);
        } else if (DB_TYPE === 'sqlite') {
            $dsn = "sqlite:" . DB_PATH;
            $pdo = new PDO($dsn, null, null, $dbOptions);
        } else {
            throw new Exception("Unsupported database type: " . DB_TYPE);
        }
        
        // Check if test user already exists
        $username = 'testuser';
        $email = 'test@example.com';
        
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existingUser = $stmt->fetch();
        
        if ($existingUser) {
            // User exists, just log them in
            $_SESSION['user_id'] = $existingUser['user_id'];
            $_SESSION['username'] = $username;
            
            $message = "Logged in as existing test user.";
            $success = true;
        } else {
            // Create test user
            $password = password_hash('testpassword', PASSWORD_DEFAULT);
            
            if (DB_TYPE === 'pgsql') {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP) RETURNING user_id");
                $stmt->execute([$username, $email, $password]);
                $userId = $stmt->fetchColumn();
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, datetime('now'))");
                $stmt->execute([$username, $email, $password]);
                $userId = $pdo->lastInsertId();
            }
            
            // Set session
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            
            $message = "Created test user account and logged in.";
            $success = true;
        }
        
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeetScribe - Create Test Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f7f9fc;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>MeetScribe - Create Test Account</h1>
        
        <?php if ($message): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <p>You are now logged in as a test user. You can proceed to use the application.</p>
            <p>Test account details:</p>
            <ul>
                <li><strong>Username:</strong> testuser</li>
                <li><strong>Email:</strong> test@example.com</li>
                <li><strong>Password:</strong> testpassword</li>
            </ul>
            
            <p>What would you like to do next?</p>
            <p>
                <a href="dashboard.php" class="btn">Go to Dashboard</a>
                <a href="index.php" class="btn">Go to Homepage</a>
            </p>
        <?php else: ?>
            <p>This will create a test user account for development purposes:</p>
            <ul>
                <li><strong>Username:</strong> testuser</li>
                <li><strong>Email:</strong> test@example.com</li>
                <li><strong>Password:</strong> testpassword</li>
            </ul>
            
            <p>If the account already exists, you'll simply be logged in.</p>
            
            <form method="post" action="">
                <p>
                    <button type="submit" class="btn">Create Test Account & Login</button>
                    <a href="index.php" style="margin-left: 10px;">Cancel</a>
                </p>
            </form>
        <?php endif; ?>
        
        <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
            <h3>Need to continue setup?</h3>
            <p>
                <a href="config_part1.php">Step 1: Database Setup</a> | 
                <a href="config_part2.php">Step 2: Database Tables</a>
            </p>
        </div>
    </div>
</body>
</html>