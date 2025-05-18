<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$pageTitle = 'Upload Audio';

// Require authentication
requireAuth();

$error = $success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate title
    $title = sanitizeInput($_POST['title'] ?? '');
    $meetingDate = sanitizeInput($_POST['meeting_date'] ?? '');
    $meetingTime = sanitizeInput($_POST['meeting_time'] ?? '');
    
    if (empty($title)) {
        $error = 'Meeting title is required';
    } elseif (empty($meetingDate)) {
        $error = 'Meeting date is required';
    } elseif (empty($meetingTime)) {
        $error = 'Meeting time is required';
    } elseif (!isset($_FILES['audio_file']) || $_FILES['audio_file']['error'] != UPLOAD_ERR_OK) {
        // Check file upload errors
        switch ($_FILES['audio_file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error = 'The uploaded file exceeds the maximum file size limit';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error = 'The file was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error = 'No file was uploaded';
                break;
            default:
                $error = 'An error occurred during file upload';
        }
    } else {
        $file = $_FILES['audio_file'];
        
        // Validate file type using finfo instead of trusting the browser-provided MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // Check if detected type is an audio type
        $isAudioFile = false;
        
        // First check if the detected type begins with 'audio/'
        if (strpos($detectedType, 'audio/') === 0) {
            $isAudioFile = true;
        } 
        // Some audio files might be detected as application/octet-stream, so check extension too
        else {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $validExtensions = ['mp3', 'wav', 'ogg', 'm4a', 'mp4', 'aac', 'flac'];
            if (in_array($extension, $validExtensions)) {
                $isAudioFile = true;
            }
        }
        
        if (!$isAudioFile) {
            $error = 'Invalid file type. Please upload an audio file (MP3, WAV, OGG, M4A). Detected type: ' . $detectedType;
        }
        // Check file size
        elseif ($file['size'] > MAX_UPLOAD_SIZE) {
            $error = 'File size exceeds the maximum limit of ' . (MAX_UPLOAD_SIZE / (1024 * 1024)) . 'MB';
        } else {
            // Generate unique filename
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFilename = uniqid('audio_') . '.' . $fileExtension;
            $uploadPath = UPLOAD_DIR . $newFilename;
            
            // Move file to upload directory
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Save meeting info to database
                try {
                    $meetingId = $db->insert(
                        "INSERT INTO meetings (user_id, title, meeting_date, meeting_time, audio_file) VALUES (?, ?, ?, ?, ?)",
                        [$_SESSION['user_id'], $title, $meetingDate, $meetingTime, $newFilename]
                    );
                    
                    if ($meetingId) {
                        // Redirect to transcript page for processing
                        header("Location: /MeetScribeHub/transcript.php?id=" . $meetingId);
                        exit;
                    } else {
                        $error = 'Failed to save meeting information';
                        // Delete the uploaded file
                        unlink($uploadPath);
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                    // Delete the uploaded file
                    unlink($uploadPath);
                }
            } else {
                $error = 'Failed to save uploaded file';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-4">Upload Meeting Audio</h1>
        </div>
    </div>
    
    <?php if ($error): ?>
        <?php echo displayError($error); ?>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <?php echo displaySuccess($success); ?>
    <?php else: ?>
    
    <div class="card mb-4">
        <div class="card-body">
            <form id="uploadForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
                <div class="row mb-4">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="title" class="form-label">Meeting Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="meeting_date" class="form-label">Meeting Date</label>
                        <input type="date" class="form-control" id="meeting_date" name="meeting_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="meeting_time" class="form-label">Meeting Time</label>
                        <input type="time" class="form-control" id="meeting_time" name="meeting_time" value="<?php echo date('H:i'); ?>" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Audio File</label>
                    <div id="uploadArea" class="upload-area">
                        <input type="file" id="fileInput" name="audio_file" class="d-none" accept="audio/*">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h3 class="upload-title">Drag & Drop or Click to Upload</h3>
                        <p class="upload-subtitle">Support for MP3, WAV, OGG, M4A (Max 100MB)</p>
                    </div>
                    
                    <div id="fileInfo" class="file-info" style="display: none;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-music me-3 fs-4"></i>
                            <div>
                                <div id="fileName" class="file-name"></div>
                                <div id="fileSize" class="file-size"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <button type="submit" id="uploadButton" class="btn btn-primary" disabled>
                        <i class="fas fa-upload me-2"></i> Upload & Generate Minutes
                    </button>
                    <a href="/dashboard.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Tips for Best Results</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h6><i class="fas fa-check-circle text-success me-2"></i> Audio Quality</h6>
                    <ul class="text-muted small mb-3">
                        <li>Use a good quality microphone during recording</li>
                        <li>Minimize background noise and interruptions</li>
                        <li>Ask participants to speak clearly and one at a time</li>
                    </ul>
                    
                    <h6><i class="fas fa-check-circle text-success me-2"></i> Meeting Structure</h6>
                    <ul class="text-muted small">
                        <li>Start with clear introductions and agenda</li>
                        <li>Clearly state decisions and action items</li>
                        <li>Summarize key points at the end of the meeting</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-check-circle text-success me-2"></i> Supported Formats</h6>
                    <ul class="text-muted small mb-3">
                        <li>MP3: Best for most recordings, good balance of size and quality</li>
                        <li>WAV: Highest quality, but larger file size</li>
                        <li>OGG/M4A: Also supported, check your recording software</li>
                    </ul>
                    
                    <h6><i class="fas fa-exclamation-circle text-warning me-2"></i> Important Notes</h6>
                    <ul class="text-muted small">
                        <li>Maximum file size: 100MB</li>
                        <li>For longer meetings, consider splitting the recording</li>
                        <li>Processing time varies based on recording length</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
