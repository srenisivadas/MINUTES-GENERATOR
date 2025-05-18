<?php
/**
 * MeetScribe Local Configuration Fixer
 * 
 * This script helps to diagnose and fix common issues when running
 * the MeetScribe application on a localhost environment after downloading
 * from Replit.
 */

// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>MeetScribe Local Environment Setup</h1>";

// Check PHP version
echo "<h2>1. PHP Version Check</h2>";
$phpVersion = phpversion();
echo "<p>Your PHP version: <strong>{$phpVersion}</strong></p>";
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "<p style='color:green'>✓ PHP version is compatible</p>";
} else {
    echo "<p style='color:red'>✗ PHP 7.4 or higher is required. Please upgrade your PHP installation.</p>";
}

// Check required extensions
echo "<h2>2. PHP Extensions Check</h2>";
$requiredExtensions = ['pdo', 'pdo_pgsql', 'pdo_sqlite', 'curl', 'mbstring', 'json'];
$missingExtensions = [];

echo "<ul>";
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<li style='color:green'>✓ {$ext} extension is loaded</li>";
    } else {
        echo "<li style='color:red'>✗ {$ext} extension is missing</li>";
        $missingExtensions[] = $ext;
    }
}
echo "</ul>";

if (!empty($missingExtensions)) {
    echo "<p style='color:red'>Enable the missing extensions in your php.ini file and restart your web server.</p>";
}

// Create local_config.php if it doesn't exist
echo "<h2>3. Configuration File Check</h2>";
$localConfigPath = __DIR__ . '/local_config.php';

if (!file_exists($localConfigPath)) {
    $configContent = '<?php
/**
 * MeetScribe Local Configuration
 * Uncomment and modify these settings for your local environment
 */

// Database settings
define(\'DB_TYPE_OVERRIDE\', \'pgsql\'); // Options: \'pgsql\', \'sqlite\'
define(\'DB_HOST_OVERRIDE\', \'localhost\');
define(\'DB_PORT_OVERRIDE\', \'5432\');
define(\'DB_USER_OVERRIDE\', \'postgres\'); // Change to your PostgreSQL username
define(\'DB_PASS_OVERRIDE\', \'\'); // Set your PostgreSQL password
define(\'DB_NAME_OVERRIDE\', \'meetscribe\');

// Set to true to use test transcript instead of real API
define(\'USE_TEST_TRANSCRIPT_OVERRIDE\', true);

// Uncomment and add your API key if you want to use real transcription
// define(\'ASSEMBLY_API_KEY_OVERRIDE\', \'your_api_key_here\');
';
    
    if (file_put_contents($localConfigPath, $configContent)) {
        echo "<p style='color:green'>✓ Created local_config.php file with sample settings</p>";
        echo "<p>Please edit the file with your database credentials.</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to create local_config.php. Please check write permissions.</p>";
    }
} else {
    echo "<p style='color:green'>✓ local_config.php already exists</p>";
}

// Check database connection
echo "<h2>4. Database Connection Test</h2>";

// We'll include the config directly here
include_once __DIR__ . '/includes/config.php';

// Try to connect to the database
try {
    echo "<p>Attempting to connect to the database...</p>";
    
    $dbOptions = [];
    
    if (DB_TYPE === 'pgsql') {
        if (!empty(DB_DSN)) {
            // Use the full DSN string if available
            $dsn = DB_DSN;
        } else {
            // Build DSN from components
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        }
        
        $dbOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
    } else if (DB_TYPE === 'sqlite') {
        $dsn = "sqlite:" . DB_PATH;
        
        $dbOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
    } else {
        throw new Exception("Unsupported database type: " . DB_TYPE);
    }
    
    // Create the connection
    if (DB_TYPE === 'pgsql') {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $dbOptions);
    } else {
        $pdo = new PDO($dsn, null, null, $dbOptions);
    }
    
    echo "<p style='color:green'>✓ Successfully connected to the database!</p>";
    
    // Check if tables exist
    $tables = ['users', 'transcripts'];
    $missingTables = [];
    
    echo "<p>Checking database tables...</p>";
    echo "<ul>";
    
    foreach ($tables as $table) {
        try {
            if (DB_TYPE === 'pgsql') {
                $stmt = $pdo->query("SELECT to_regclass('public.{$table}')");
                $exists = $stmt->fetchColumn() !== null;
            } else {
                $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'");
                $exists = !empty($stmt->fetchColumn());
            }
            
            if ($exists) {
                echo "<li style='color:green'>✓ Table '{$table}' exists</li>";
            } else {
                echo "<li style='color:orange'>⚠ Table '{$table}' does not exist</li>";
                $missingTables[] = $table;
            }
        } catch (PDOException $e) {
            echo "<li style='color:red'>✗ Error checking table '{$table}': " . htmlspecialchars($e->getMessage()) . "</li>";
        }
    }
    
    echo "</ul>";
    
    if (!empty($missingTables)) {
        echo "<p><strong>Tables are missing. Do you want to create them?</strong></p>";
        echo "<form method='post'>";
        echo "<input type='hidden' name='create_tables' value='1'>";
        echo "<button type='submit' style='padding:5px 10px;'>Create Missing Tables</button>";
        echo "</form>";
        
        if (isset($_POST['create_tables'])) {
            echo "<h3>Creating Tables</h3>";
            
            // SQL for creating tables
            $createTablesSql = [];
            
            if (DB_TYPE === 'pgsql') {
                $createTablesSql['users'] = "CREATE TABLE users (
                    user_id SERIAL PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    reset_token VARCHAR(64),
                    reset_token_expires TIMESTAMP,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                $createTablesSql['transcripts'] = "CREATE TABLE transcripts (
                    transcript_id SERIAL PRIMARY KEY,
                    user_id INTEGER REFERENCES users(user_id),
                    title VARCHAR(255) NOT NULL,
                    original_filename VARCHAR(255) NOT NULL,
                    file_path VARCHAR(255) NOT NULL,
                    duration INTEGER DEFAULT 0,
                    status VARCHAR(50) DEFAULT 'pending',
                    transcript_text TEXT,
                    minutes_text TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
            } else {
                $createTablesSql['users'] = "CREATE TABLE users (
                    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL UNIQUE,
                    email TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    reset_token TEXT,
                    reset_token_expires DATETIME,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )";
                
                $createTablesSql['transcripts'] = "CREATE TABLE transcripts (
                    transcript_id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER REFERENCES users(user_id),
                    title TEXT NOT NULL,
                    original_filename TEXT NOT NULL,
                    file_path TEXT NOT NULL,
                    duration INTEGER DEFAULT 0,
                    status TEXT DEFAULT 'pending',
                    transcript_text TEXT,
                    minutes_text TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )";
            }
            
            foreach ($missingTables as $table) {
                if (isset($createTablesSql[$table])) {
                    try {
                        $pdo->exec($createTablesSql[$table]);
                        echo "<p style='color:green'>✓ Successfully created table '{$table}'</p>";
                    } catch (PDOException $e) {
                        echo "<p style='color:red'>✗ Error creating table '{$table}': " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
            }
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database settings in local_config.php.</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check uploads directory
echo "<h2>5. Uploads Directory Check</h2>";
$uploadsDir = __DIR__ . '/uploads';

if (!file_exists($uploadsDir)) {
    if (mkdir($uploadsDir, 0755, true)) {
        echo "<p style='color:green'>✓ Created uploads directory</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to create uploads directory. Please check permissions.</p>";
    }
} else {
    echo "<p style='color:green'>✓ Uploads directory exists</p>";
    
    // Check if it's writable
    if (is_writable($uploadsDir)) {
        echo "<p style='color:green'>✓ Uploads directory is writable</p>";
    } else {
        echo "<p style='color:red'>✗ Uploads directory is not writable. Please check permissions.</p>";
    }
}

// Check paths in PHP files
echo "<h2>6. Path Configuration Check</h2>";
echo "<p>The application should automatically detect the correct paths, but if you experience issues with links or redirects, run the <code>config_update.php</code> script.</p>";
echo "<p><a href='config_update.php' style='padding:5px 10px;display:inline-block;background:#f0f0f0;text-decoration:none;'>Run Path Configuration Update</a></p>";

// Check API configuration
echo "<h2>7. API Configuration</h2>";
if (defined('ASSEMBLY_API_KEY') && !empty(ASSEMBLY_API_KEY)) {
    echo "<p style='color:green'>✓ Assembly AI API key is configured</p>";
} else {
    echo "<p style='color:orange'>⚠ Assembly AI API key is not configured</p>";
    echo "<p>For real transcription, you'll need to get an API key from Assembly AI and add it to your local_config.php file:</p>";
    echo "<pre>define('ASSEMBLY_API_KEY_OVERRIDE', 'your_api_key_here');</pre>";
}

if (defined('USE_TEST_TRANSCRIPT') && USE_TEST_TRANSCRIPT) {
    echo "<p style='color:blue'>ℹ The application is currently using test transcripts.</p>";
    echo "<p>This is recommended for development to avoid using up your API quota.</p>";
} else {
    echo "<p style='color:blue'>ℹ The application is configured to use real API calls for transcription.</p>";
}

// Next steps
echo "<h2>8. Next Steps</h2>";
echo "<ol>";
echo "<li>Make sure your database is properly configured in local_config.php</li>";
echo "<li>Ensure your web server (Apache/Nginx/XAMPP) is running</li>";
echo "<li>Open the application in your browser</li>";
echo "<li>If you encounter any issues with links or paths, run the config_update.php script</li>";
echo "</ol>";

echo "<div style='margin-top:30px;'>";
echo "<a href='index.php' style='padding:10px 15px;background:#007bff;color:white;text-decoration:none;'>Go to MeetScribe Homepage</a>";
echo "</div>";
?>