<?php
/*
===============================================================
MeetScribe - Local Deployment Guide
===============================================================

RUNNING THIS SITE ON YOUR LOCAL MACHINE (XAMPP, etc.)
------------------------------------------------------

1. DATABASE SETUP
----------------
- Create a PostgreSQL database named "meetscribe"
- Configure your database credentials in includes/config.php
- The application will automatically create tables on first run

2. XAMPP/LOCAL SERVER SETUP
--------------------------
- Copy all project files to your web server directory:
  Example: C:\xampp\htdocs\MeetScribeHub\
- Ensure PHP and PostgreSQL are running

3. PATH CONFIGURATION
-------------------
- The application now handles paths automatically
- It detects if it's running in a subfolder like /MeetScribeHub/
- All links should work correctly without manual changes

4. FOR API TRANSCRIPTION
----------------------
- Get an Assembly AI API key (https://www.assemblyai.com/)
- Add your key to includes/config.php:
  define('ASSEMBLY_API_KEY', 'your_key_here');
- For testing, you can keep USE_TEST_TRANSCRIPT = true

5. TROUBLESHOOTING
----------------
- Check database connection/credentials if you see DB errors
- Clear browser cache if links redirect incorrectly
- For issues with file uploads, check folder permissions

For full documentation, refer to the project repository.
*/

require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'Welcome';

// Redirect to dashboard if logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isLoggedIn()) {
    header("Location: " . BASE_PATH . "/dashboard.php");
    exit;
}
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0">
            <h1 class="display-4 fw-bold mb-4">Transform Your Meetings with MeetScribe</h1>
            <p class="lead mb-4">Automatically convert your meeting audio recordings into well-structured minutes. Save time and never miss important details again.</p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                <a href="signup.php" class="btn btn-primary btn-lg px-4 me-md-2">Get Started</a>
                <a href="signin.php" class="btn btn-outline-secondary btn-lg px-4">Sign In</a>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="bg-primary text-white p-5 text-center">
                        <i class="fas fa-microphone fa-5x mb-4"></i>
                        <h2 class="h3 mb-3">How It Works</h2>
                        <p class="mb-0">Upload your meeting recording and let MeetScribe do the rest. Our advanced AI technology will transcribe, analyze, and structure your meeting minutes.</p>
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-upload fa-2x text-primary"></i>
                                    </div>
                                    <h5 class="mt-3">Upload</h5>
                                    <p class="small text-muted">Upload your audio recording</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-robot fa-2x text-primary"></i>
                                    </div>
                                    <h5 class="mt-3">Process</h5>
                                    <p class="small text-muted">AI transcribes and structures</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-file-alt fa-2x text-primary"></i>
                                    </div>
                                    <h5 class="mt-3">Download</h5>
                                    <p class="small text-muted">Get formatted minutes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5 pt-5">
        <div class="col-12 text-center mb-4">
            <h2 class="display-6 fw-bold">Features</h2>
            <p class="lead">Everything you need for perfect meeting minutes</p>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary-lighter mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas fa-headphones text-primary fa-2x"></i>
                    </div>
                    <h4 class="card-title mb-3">Audio Transcription</h4>
                    <p class="card-text text-muted">Accurate transcription of your meeting recordings with high quality speech recognition.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary-lighter mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas fa-list-ul text-primary fa-2x"></i>
                    </div>
                    <h4 class="card-title mb-3">Structured Format</h4>
                    <p class="card-text text-muted">Automatically organizes content into sections including agenda, decisions, and action items.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary-lighter mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas fa-download text-primary fa-2x"></i>
                    </div>
                    <h4 class="card-title mb-3">Easy Download</h4>
                    <p class="card-text text-muted">Download your meeting minutes in multiple formats for easy sharing and distribution.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary-lighter mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas fa-clock text-primary fa-2x"></i>
                    </div>
                    <h4 class="card-title mb-3">Time Saving</h4>
                    <p class="card-text text-muted">Save hours of manual transcription and focus on what matters most in your meetings.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary-lighter mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas fa-archive text-primary fa-2x"></i>
                    </div>
                    <h4 class="card-title mb-3">Meeting History</h4>
                    <p class="card-text text-muted">Access all your past meeting minutes in one place with organized storage.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary-lighter mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas fa-lock text-primary fa-2x"></i>
                    </div>
                    <h4 class="card-title mb-3">Secure Storage</h4>
                    <p class="card-text text-muted">Your meeting data is securely stored with encryption and user-level access controls.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5 py-5 align-items-center">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <img src="https://w.wallhaven.cc/full/4g/wallhaven-4g589l.jpg" class="img-fluid rounded-4 shadow" alt="Meeting Image">
        </div>
        <div class="col-lg-6">
            <h2 class="display-6 fw-bold mb-4">Ready to transform your meeting workflow?</h2>
            <p class="lead mb-4">Join thousands of professionals who are saving time and improving meeting productivity with MeetScribe.</p>
            <div class="d-grid d-md-flex gap-2">
                <a href="<?php echo BASE_PATH; ?>/signup.php" class="btn btn-primary btn-lg px-4">Get Started Today</a>
            </div>
        </div>
    </div>
</div>

<style>
.bg-primary-lighter {
    background-color: rgba(67, 97, 238, 0.1);
}
</style>

<?php include 'includes/footer.php'; ?>
