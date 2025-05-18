<?php
/**
 * MeetScribe Local Setup - Part 2
 * This file creates the database tables needed for the application
 */

// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>MeetScribe Local Setup - Step 2: Database Tables</h1>";

// Include configuration files
require_once 'includes/config.php';

echo "<h2>Database Connection Test</h2>";

// Try to connect to the database
try {
    echo "<p>Connecting to database...</p>";
    
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
    
    echo "<p style='color:green'>✓ Connected to the database successfully!</p>";
    
    // Check if tables already exist
    echo "<h2>Checking Tables</h2>";
    
    $tables = ['users', 'transcripts'];
    $tablesExist = [];
    
    foreach ($tables as $table) {
        try {
            if (DB_TYPE === 'pgsql') {
                $stmt = $pdo->query("SELECT to_regclass('public.{$table}')");
                $exists = $stmt->fetchColumn() !== null;
            } else {
                $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'");
                $exists = !empty($stmt->fetchColumn());
            }
            
            $tablesExist[$table] = $exists;
            
            if ($exists) {
                echo "<p>Table '{$table}' already exists.</p>";
            } else {
                echo "<p>Table '{$table}' does not exist.</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color:red'>Error checking table '{$table}': " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Create tables if they don't exist
    echo "<h2>Creating Tables</h2>";
    
    // User table
    if (!$tablesExist['users']) {
        try {
            if (DB_TYPE === 'pgsql') {
                $sql = "CREATE TABLE users (
                    user_id SERIAL PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    reset_token VARCHAR(64),
                    reset_token_expires TIMESTAMP,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
            } else {
                $sql = "CREATE TABLE users (
                    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL UNIQUE,
                    email TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    reset_token TEXT,
                    reset_token_expires DATETIME,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )";
            }
            
            $pdo->exec($sql);
            echo "<p style='color:green'>✓ Created users table</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>Error creating users table: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Transcripts table
    if (!$tablesExist['transcripts']) {
        try {
            if (DB_TYPE === 'pgsql') {
                $sql = "CREATE TABLE transcripts (
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
                $sql = "CREATE TABLE transcripts (
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
            
            $pdo->exec($sql);
            echo "<p style='color:green'>✓ Created transcripts table</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>Error creating transcripts table: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please make sure:</p>";
    echo "<ul>";
    echo "<li>Your database server is running</li>";
    echo "<li>You've created a database named '" . DB_NAME . "'</li>";
    echo "<li>Your username and password are correct in local_config.php</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Check if all tables were created successfully</li>";
echo "<li>Run <a href='set_test_session.php'>Step 3: Create Test Account</a> (optional)</li>";
echo "<li>Go to <a href='index.php'>MeetScribe Homepage</a></li>";
echo "</ol>";
?>