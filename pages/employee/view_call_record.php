<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is employee
if (!isLoggedIn() || !hasRole('employee')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$call_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($call_id <= 0) {
    header('Location: my_history.php?error=invalid_id');
    exit();
}

// Get call record details
$call = getCallRecordById($call_id);

if (!$call) {
    header('Location: my_history.php?error=not_found');
    exit();
}

// Security: Ensure employee can only view their own call records
if ($call['employee_id'] != $_SESSION['user_id']) {
    header('Location: my_history.php?error=unauthorized');
    exit();
}

$page_title = 'Call Details - ' . htmlspecialchars($call['user_name']);

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<style>
    /* Page Specific Styles */
    .call-detail-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    
    .call-detail-card h3 {
        margin: 0 0 20px 0;
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
        display: inline-block;
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
    }
    
    .detail-item label {
        font-size: 11px;
        font-weight: 700;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    
    .detail-item .value {
        font-size: 15px;
        color: #2c3e50;
        font-weight: 500;
    }
    
    .detail-item .value i {
        margin-right: 8px;
        color: #3498db;
        width: 20px;
    }
    
    .interest-badge-large {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 14px;
    }
    
    .interest-interested {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .interest-not_interested {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .interest-follow_up {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .notes-box {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-top: 10px;
        border-left: 4px solid #3498db;
    }
    
    .notes-box p {
        margin: 0;
        line-height: 1.6;
        color: #34495e;
        white-space: pre-wrap;
    }
    
    .recording-player {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
    }
    
    .recording-player audio {
        width: 100%;
        max-width: 400px;
        margin: 10px 0;
    }
    
    .recording-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #3498db;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        transition: background 0.3s;
    }
    
    .recording-link:hover {
        background: #2980b9;
        color: white;
        text-decoration: none;
    }
    
    .customer-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
    }
    
    .customer-card h4 {
        margin: 0 0 15px 0;
        font-size: 16px;
        color: #2c3e50;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 8px;
    }
    
    .customer-info {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .info-row {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
    }
    
    .info-row i {
        width: 20px;
        color: #3498db;
    }
    
    .info-row strong {
        min-width: 80px;
        color: #7f8c8d;
    }
    
    .action-buttons-bottom {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }
    
    .btn-back {
        background: #6c757d;
        color: white;
        padding: 10px 25px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.3s;
    }
    
    .btn-back:hover {
        background: #5a6268;
        color: white;
        text-decoration: none;
    }
    
    .btn-call-again {
        background: #ffc107;
        color: #212529;
        padding: 10px 25px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.3s;
    }
    
    .btn-call-again:hover {
        background: #e0a800;
        color: #212529;
        text-decoration: none;
    }
    
    .meta-info {
        font-size: 12px;
        color: #6c757d;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e0e0e0;
    }
    
    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        
        .call-detail-card {
            padding: 15px;
        }
        
        .action-buttons-bottom {
            flex-direction: column;
        }
        
        .btn-back, .btn-call-again {
            justify-content: center;
        }
        
        .info-row {
            flex-wrap: wrap;
        }
        
        .info-row strong {
            min-width: 70px;
        }
    }
</style>

<div class="wrapper">
    <?php include_once '../../components/sidebar_employee.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-phone-alt"></i> Call Details</h1>
            <div class="header-actions">
                <a href="my_history.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to My History
                </a>
            </div>
        </div>
        
        <!-- Call Information Card -->
        <div class="call-detail-card">
            <h3><i class="fas fa-info-circle"></i> Call Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Call ID</label>
                    <div class="value"><i class="fas fa-hashtag"></i> #<?php echo $call['id']; ?></div>
                </div>
                <div class="detail-item">
                    <label>Called Date & Time</label>
                    <div class="value"><i class="fas fa-calendar-alt"></i> <?php echo date('l, F j, Y h:i A', strtotime($call['called_at'])); ?></div>
                </div>
                <div class="detail-item">
                    <label>Interest Level</label>
                    <div class="value">
                        <span class="interest-badge-large interest-<?php echo $call['interest_level']; ?>">
                            <?php 
                            if ($call['interest_level'] == 'interested') echo '✅ Interested';
                            elseif ($call['interest_level'] == 'not_interested') echo '❌ Not Interested';
                            else echo '🔄 Follow Up Required';
                            ?>
                        </span>
                    </div>
                </div>
                <?php if ($call['follow_up_date'] && $call['follow_up_date'] != '0000-00-00'): ?>
                <div class="detail-item">
                    <label>Follow-up Date</label>
                    <div class="value">
                        <i class="fas fa-calendar-check"></i> 
                        <?php echo date('l, F j, Y', strtotime($call['follow_up_date'])); ?>
                        <?php 
                        $today = date('Y-m-d');
                        if ($call['follow_up_date'] < $today) {
                            echo '<span style="color: #dc3545; margin-left: 10px;">(Overdue)</span>';
                        } elseif ($call['follow_up_date'] == $today) {
                            echo '<span style="color: #ff9800; margin-left: 10px;">(Today!)</span>';
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Call Notes -->
            <div style="margin-top: 20px;">
                <label style="font-size: 11px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: block;">
                    <i class="fas fa-sticky-note"></i> Call Notes
                </label>
                <div class="notes-box">
                    <p><?php echo nl2br(htmlspecialchars($call['notes'] ?: 'No notes recorded for this call.')); ?></p>
                </div>
            </div>
            
            <!-- Recording Section -->
            <?php if ($call['recording_file']): ?>
            <div style="margin-top: 20px;">
                <label style="font-size: 11px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: block;">
                    <i class="fas fa-microphone"></i> Call Recording
                </label>
                <div class="recording-player">
                    <audio controls>
                        <source src="<?php echo BASE_URL; ?>uploads/recordings/<?php echo $call['recording_file']; ?>">
                        Your browser does not support the audio element.
                    </audio>
                    <div style="margin-top: 15px;">
                        <a href="<?php echo BASE_URL; ?>uploads/recordings/<?php echo $call['recording_file']; ?>" class="recording-link" download>
                            <i class="fas fa-download"></i> Download Recording
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="meta-info">
                <i class="fas fa-clock"></i> Recorded on: <?php echo date('F j, Y \a\t g:i A', strtotime($call['called_at'])); ?>
            </div>
        </div>
        
        <!-- Customer Information Section -->
        <div class="customer-card">
            <h4><i class="fas fa-user"></i> Customer Information</h4>
            <div class="customer-info">
                <div class="info-row">
                    <i class="fas fa-user-circle"></i>
                    <strong>Name:</strong>
                    <span><?php echo htmlspecialchars($call['user_name']); ?></span>
                </div>
                <?php if ($call['user_email']): ?>
                <div class="info-row">
                    <i class="fas fa-envelope"></i>
                    <strong>Email:</strong>
                    <span><?php echo htmlspecialchars($call['user_email']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($call['user_phone']): ?>
                <div class="info-row">
                    <i class="fas fa-phone"></i>
                    <strong>Phone:</strong>
                    <span><?php echo htmlspecialchars($call['user_phone']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($call['user_company']): ?>
                <div class="info-row">
                    <i class="fas fa-building"></i>
                    <strong>Company:</strong>
                    <span><?php echo htmlspecialchars($call['user_company']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($call['user_address']): ?>
                <div class="info-row">
                    <i class="fas fa-location-dot"></i>
                    <strong>Address:</strong>
                    <span><?php echo htmlspecialchars(substr($call['user_address'], 0, 100)); ?></span>
                </div>
                <?php endif; ?>
                <div class="info-row" style="margin-top: 10px;">
                    <a href="call_customer.php?id=<?php echo $call['user_id']; ?>" class="btn-call-again" style="padding: 8px 16px;">
                        <i class="fas fa-phone"></i> Call This Customer Again
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="call-detail-card">
            <div class="action-buttons-bottom">
                <a href="my_history.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to My History
                </a>
                <?php if ($call['interest_level'] == 'follow_up'): ?>
                <a href="call_customer.php?id=<?php echo $call['user_id']; ?>&followup_id=<?php echo $call['id']; ?>" class="btn-call-again">
                    <i class="fas fa-phone"></i> Make Follow-up Call
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../components/footer.php'; ?>