<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$pageTitle = 'Dashboard';

// Require authentication
requireAuth();

// Get user meetings
$meetings = getUserMeetings($_SESSION['user_id']);

// Count statistics
$totalMeetings = count($meetings);
$totalMinutes = 0;
$recentMeetings = 0;

// Get current date
$currentDate = date('Y-m-d');
$oneMonthAgo = date('Y-m-d', strtotime('-30 days'));

foreach ($meetings as $meeting) {
    if ($meeting['minutes_id']) {
        $totalMinutes++;
    }
    
    if ($meeting['meeting_date'] >= $oneMonthAgo) {
        $recentMeetings++;
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-4">Dashboard</h1>
        </div>
        <div class="col-auto">
            <a href="<?php echo BASE_PATH; ?>/upload.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> New Meeting
            </a>
        </div>
    </div>
    
    <div class="row dashboard-stats">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="stat-card">
                        <div class="stat-icon stat-primary">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <div class="h2 mb-0"><?php echo $totalMeetings; ?></div>
                            <div class="text-muted">Total Meetings</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="stat-card">
                        <div class="stat-icon stat-success">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div>
                            <div class="h2 mb-0"><?php echo $totalMinutes; ?></div>
                            <div class="text-muted">Meeting Minutes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="stat-card">
                        <div class="stat-icon stat-primary">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <div class="h2 mb-0"><?php echo $recentMeetings; ?></div>
                            <div class="text-muted">Recent Meetings</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Your Meetings</h5>
            <div>
                <input type="text" id="meetingSearch" class="form-control form-control-sm" placeholder="Search meetings...">
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($meetings)): ?>
                <div class="no-meetings">
                    <div class="no-meetings-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3 class="no-meetings-title">No Meetings Yet</h3>
                    <p class="no-meetings-subtitle">Upload your first meeting recording to get started.</p>
                    <a href="<?php echo BASE_PATH; ?>/upload.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Upload Audio
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($meetings as $meeting): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($meeting['title']); ?></div>
                                    </td>
                                    <td><?php echo formatDate($meeting['meeting_date']); ?></td>
                                    <td><?php echo formatTime($meeting['meeting_time']); ?></td>
                                    <td>
                                        <?php if ($meeting['minutes_id']): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Processing</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($meeting['minutes_id']): ?>
                                            <a href="<?php echo BASE_PATH; ?>/minutes.php?id=<?php echo $meeting['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-file-alt me-1"></i> View Minutes
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo BASE_PATH; ?>/transcript.php?id=<?php echo $meeting['id']; ?>" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-sync me-1"></i> Check Status
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('meetingSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(function(row) {
                const title = row.querySelector('td:first-child').textContent.toLowerCase();
                const date = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || date.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
