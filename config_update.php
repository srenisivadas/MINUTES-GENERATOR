<?php
/**
 * Configuration Update Helper Script
 * 
 * This script updates the paths in all PHP files to use BASE_PATH
 * where hardcoded paths are found.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the path to the project root
define('ROOT_PATH', __DIR__);

// Files to exclude from processing
$exclusions = [
    __FILE__,
    ROOT_PATH . '/config_update.php',
    ROOT_PATH . '/local_config.php',
    ROOT_PATH . '/database',
    ROOT_PATH . '/uploads'
];

echo "<h1>MeetScribe Path Configuration Update</h1>";

// Function to recursively scan directory and find PHP files
function findPhpFiles($dir, $exclusions) {
    $files = [];
    
    $objects = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($objects as $name => $object) {
        if (is_file($name) && pathinfo($name, PATHINFO_EXTENSION) === 'php') {
            $exclude = false;
            foreach ($exclusions as $exclusion) {
                if (strpos($name, $exclusion) === 0) {
                    $exclude = true;
                    break;
                }
            }
            
            if (!$exclude) {
                $files[] = $name;
            }
        }
    }
    
    return $files;
}

// Function to update file content
function updateFileContent($file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Pattern to find hardcoded paths in href, src, and action attributes
    $content = preg_replace(
        '/(href|src|action)="\/([^"]*)"/', 
        '$1="<?php echo BASE_PATH; ?>/$2"', 
        $content
    );
    
    // Pattern to find JavaScript and CSS includes with hardcoded paths
    $content = preg_replace(
        '/src="\/js/', 
        'src="<?php echo BASE_PATH; ?>/js', 
        $content
    );
    
    $content = preg_replace(
        '/href="\/css/', 
        'href="<?php echo BASE_PATH; ?>/css', 
        $content
    );
    
    // Pattern to find redirects with hardcoded paths
    $content = preg_replace(
        '/redirect\("\/([^"]*)"/', 
        'redirect("$1"', 
        $content
    );
    
    // Update hardcoded links
    $content = str_replace(
        '<a href="/signin.php"', 
        '<a href="<?php echo BASE_PATH; ?>/signin.php"', 
        $content
    );
    
    $content = str_replace(
        '<a href="/signup.php"', 
        '<a href="<?php echo BASE_PATH; ?>/signup.php"', 
        $content
    );
    
    $content = str_replace(
        '<a href="/forgot_password.php"', 
        '<a href="<?php echo BASE_PATH; ?>/forgot_password.php"', 
        $content
    );
    
    $content = str_replace(
        '<a href="/dashboard.php"', 
        '<a href="<?php echo BASE_PATH; ?>/dashboard.php"', 
        $content
    );
    
    // Fix JavaScript/CSS references
    $content = str_replace(
        '<script src="/js', 
        '<script src="<?php echo BASE_PATH; ?>/js', 
        $content
    );
    
    $content = str_replace(
        '<link href="/css', 
        '<link href="<?php echo BASE_PATH; ?>/css', 
        $content
    );
    
    // Save the file if changes were made
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        return true;
    }
    
    return false;
}

// Process files
$phpFiles = findPhpFiles(ROOT_PATH, $exclusions);
$updatedCount = 0;

echo "<h2>Processing PHP files...</h2>";
echo "<ul>";

foreach ($phpFiles as $file) {
    $relativePath = str_replace(ROOT_PATH . '/', '', $file);
    
    if (updateFileContent($file)) {
        echo "<li>Updated: " . htmlspecialchars($relativePath) . "</li>";
        $updatedCount++;
    } else {
        echo "<li>No changes needed: " . htmlspecialchars($relativePath) . "</li>";
    }
}

echo "</ul>";

echo "<h2>Summary</h2>";
echo "<p>Total files processed: " . count($phpFiles) . "</p>";
echo "<p>Files updated: " . $updatedCount . "</p>";

echo "<h2>Next Steps</h2>";
echo "<p>1. Update your <code>includes/config.php</code> file to ensure the BASE_PATH is correctly defined.</p>";
echo "<p>2. Test the application in your localhost environment.</p>";
echo "<p>3. If any issues persist, manually check the files that were updated.</p>";

echo "<p><a href='index.php'>Return to homepage</a></p>";
?>