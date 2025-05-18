<?php
/**
 * MeetScribe Local Setup - Part 1
 * This file contains the first part of the configuration fixes
 */

// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>MeetScribe Local Setup - Step 1: Database Setup</h1>";

// Create local_config.php if it doesn't exist
$localConfigPath = __DIR__ . '/local_config.php';

if (!file_exists($localConfigPath)) {
    $configContent = '<?php
/**
 * MeetScribe Local Configuration
 * This file contains local database settings
 */

// Database settings - EDIT THESE VALUES
define(\'DB_TYPE_OVERRIDE\', \'pgsql\'); // Options: \'pgsql\' or \'sqlite\'
define(\'DB_HOST_OVERRIDE\', \'localhost\');
define(\'DB_PORT_OVERRIDE\', \'5432\');
define(\'DB_USER_OVERRIDE\', \'postgres\'); // Change to your database username
define(\'DB_PASS_OVERRIDE\', \'\'); // Add your database password here
define(\'DB_NAME_OVERRIDE\', \'meetscribe\');

// Testing mode - Set to true to use fake data, false to use real API
define(\'USE_TEST_TRANSCRIPT_OVERRIDE\', true);

// Add your API key here if you want to use real transcription
// define(\'ASSEMBLY_API_KEY_OVERRIDE\', \'your_api_key_here\');
';
    
    if (file_put_contents($localConfigPath, $configContent)) {
        echo "<p style='color:green'>✓ Created local_config.php file</p>";
        echo "<p>Please edit this file with your database credentials.</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to create local_config.php</p>";
        echo "<p>Your web server might not have write permissions. Please create this file manually with the following content:</p>";
        echo "<pre>" . htmlspecialchars($configContent) . "</pre>";
    }
} else {
    echo "<p>local_config.php already exists.</p>";
}

// Create database directory if it doesn't exist
$dbDir = __DIR__ . '/database';
if (!file_exists($dbDir)) {
    if (mkdir($dbDir, 0755, true)) {
        echo "<p style='color:green'>✓ Created database directory</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to create database directory</p>";
    }
} else {
    echo "<p>Database directory already exists.</p>";
}

// Create uploads directory if it doesn't exist
$uploadsDir = __DIR__ . '/uploads';
if (!file_exists($uploadsDir)) {
    if (mkdir($uploadsDir, 0755, true)) {
        echo "<p style='color:green'>✓ Created uploads directory</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to create uploads directory</p>";
    }
} else {
    echo "<p>Uploads directory already exists.</p>";
}

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Edit local_config.php with your database credentials</li>";
echo "<li>Make sure PostgreSQL is installed and running</li>";
echo "<li>Create a database named 'meetscribe'</li>";
echo "<li>Run <a href='config_part2.php'>Step 2: Database Tables Setup</a></li>";
echo "</ol>";
?>