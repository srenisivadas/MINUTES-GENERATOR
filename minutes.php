<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$pageTitle = 'Meeting Minutes';

// Require authentication
requireAuth();

$error = '';
$meeting = null;
$minutes = null;

// Get meeting ID from query parameter
$meetingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verify meeting exists and belongs to current user
if ($meetingId) {
    $meeting = getMeetingById($meetingId, $_SESSION['user_id']);
    
    if (!$meeting) {
        $error = 'Meeting not found or you do not have permission to access it.';
    } else {
        // Get meeting minutes
        $minutes = getMinutesByMeetingId($meetingId);
        
        if (!$minutes) {
            // Redirect to transcript page if minutes don't exist
            header("Location: /transcript.php?id=" . $meetingId);
            exit;
        }
    }
} else {
    $error = 'Invalid meeting ID.';
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-4">Meeting Minutes</h1>
        </div>
    </div>
    
    <?php if ($error): ?>
        <?php echo displayError($error); ?>
        <div class="text-center mt-4">
            <a href="<?php echo BASE_PATH; ?>/dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    <?php elseif ($meeting && $minutes): ?>
        <div class="minutes-controls">
            <a href="<?php echo BASE_PATH; ?>/dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
            <div>
                <a href="<?php echo BASE_PATH; ?>/download.php?id=<?php echo $meetingId; ?>&format=pdf" class="btn btn-primary">
                    <i class="fas fa-file-pdf me-2"></i> Download PDF
                </a>
                <a href="<?php echo BASE_PATH; ?>/download.php?id=<?php echo $meetingId; ?>&format=txt" class="btn btn-outline-primary ms-2">
                    <i class="fas fa-file-alt me-2"></i> Download Text
                </a>
            </div>
        </div>
        
        <div class="transcript-container">
            <div class="transcript-header">
                <h2 class="transcript-title"><?php echo htmlspecialchars($meeting['title']); ?></h2>
                <div class="transcript-meta">
                    <span><i class="fas fa-calendar"></i> <?php echo formatDate($meeting['meeting_date']); ?></span>
                    <span><i class="fas fa-clock"></i> <?php echo formatTime($meeting['meeting_time']); ?></span>
                </div>
            </div>
            
            <?php if (!empty($minutes['agenda'])): ?>
            <div class="mb-4">
                <h3 class="section-title"><i class="fas fa-list"></i> Agenda</h3>
                <div class="section-content">
                    <?php echo nl2br(htmlspecialchars($minutes['agenda'])); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="mb-4">
                <h3 class="section-title"><i class="fas fa-comment-alt"></i> Discussion</h3>
                <div class="section-content">
                    <div id="transcriptContent" class="transcript-content limited-height">
                        <?php echo nl2br(htmlspecialchars($minutes['content'])); ?>
                    </div>
                    <?php if (strlen($minutes['content']) > 1000): ?>
                    <button id="showFullTranscript" class="btn btn-sm btn-link">Show Full Transcript</button>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($minutes['decisions'])): ?>
            <div class="mb-4">
                <h3 class="section-title"><i class="fas fa-check-circle"></i> Decisions</h3>
                <div class="section-content">
                    <?php echo nl2br(htmlspecialchars($minutes['decisions'])); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($minutes['action_items'])): ?>
            <div class="mb-4">
                <h3 class="section-title"><i class="fas fa-tasks"></i> Action Items</h3>
                <div class="section-content">
                    <?php 
                    $actionItems = explode("\n", $minutes['action_items']);
                    foreach ($actionItems as $item) {
                        if (trim($item)) {
                            echo '<div class="action-item"><i class="fas fa-check-square"></i> ' . htmlspecialchars($item) . '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($minutes['conclusion'])): ?>
            <div class="mb-4">
                <h3 class="section-title"><i class="fas fa-flag-checkered"></i> Conclusion</h3>
                <div class="section-content">
                    <?php echo nl2br(htmlspecialchars($minutes['conclusion'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <style>
        .limited-height {
            max-height: 300px;
            overflow: hidden;
            position: relative;
        }
        .limited-height::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 50px;
            background: linear-gradient(transparent, white);
        }
        </style>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
