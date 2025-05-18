<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$pageTitle = 'Processing Audio';

// Require authentication
requireAuth();

$error = '';
$meeting = null;
$transcript = '';
$minutes = null;

// Get meeting ID from query parameter
$meetingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verify meeting exists and belongs to current user
if ($meetingId) {
    $meeting = getMeetingById($meetingId, $_SESSION['user_id']);
    
    if (!$meeting) {
        $error = 'Meeting not found or you do not have permission to access it.';
    } else {
        // Check if minutes already exist
        $minutes = getMinutesByMeetingId($meetingId);
        
        if ($minutes) {
            // Redirect to minutes page if already processed
            header("Location: " . BASE_PATH . "/minutes.php?id=" . $meetingId);
            exit;
        }
        
        // Process audio file
        if (!$error) {
            $audioPath = UPLOAD_DIR . $meeting['audio_file'];
            
            if (!file_exists($audioPath)) {
                $error = 'Audio file not found. Please contact support.';
            } else {
                // Transcribe audio
                $transcript = transcribeAudio($audioPath);
                
                // Log specific errors for debugging
                error_log("Transcript response: " . print_r($transcript, true));
                
                if (!$transcript || is_string($transcript) && strpos($transcript, 'Error') === 0) {
                    $error = 'Failed to transcribe audio: ' . ($transcript ?: 'Unknown error');
                } else {
                    // Generate structured minutes
                    $structuredMinutes = generateMeetingMinutes(
                        $transcript,
                        $meeting['title'],
                        $meeting['meeting_date'],
                        $meeting['meeting_time']
                    );
                    
                    // Save minutes to database
                    $minutesId = $db->insert(
                        "INSERT INTO minutes (meeting_id, content, agenda, decisions, action_items, conclusion) VALUES (?, ?, ?, ?, ?, ?)",
                        [
                            $meetingId,
                            $structuredMinutes['content'],
                            $structuredMinutes['agenda'],
                            $structuredMinutes['decisions'],
                            $structuredMinutes['action_items'],
                            $structuredMinutes['conclusion']
                        ]
                    );
                    
                    if ($minutesId) {
                        // Redirect to minutes page
                        header("Location: " . BASE_PATH . "/minutes.php?id=" . $meetingId);
                        exit;
                    } else {
                        $error = 'Failed to save meeting minutes. Please try again.';
                    }
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-4">Processing Meeting Audio</h1>
        </div>
    </div>
    
    <?php if ($error): ?>
        <?php echo displayError($error); ?>
        <div class="text-center mt-4">
            <a href="<?php echo BASE_PATH; ?>/dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            <?php if ($meeting): ?>
                <a href="<?php echo BASE_PATH; ?>/upload.php" class="btn btn-outline-primary ms-2">Try Another Upload</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="spinner-container">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                
                <h3 class="mt-4 mb-2">Generating Meeting Minutes</h3>
                <p class="text-muted mb-4">We're processing your audio file. This may take a few minutes.</p>
                
                <div id="progressContainer" class="w-75 mx-auto mb-4" style="display: none;">
                    <div class="progress">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small>Transcribing</small>
                        <small>Analyzing</small>
                        <small>Formatting</small>
                    </div>
                </div>
                
                <p class="mb-0"><em>Please do not close this window. You will be automatically redirected when processing is complete.</em></p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
