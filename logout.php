<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Log out the user
logoutUser();

// Set a flash message
$_SESSION['flash_message'] = 'You have been successfully logged out.';
$_SESSION['flash_type'] = 'success';

// Redirect to the sign in page
redirect('/signin.php');
?>
