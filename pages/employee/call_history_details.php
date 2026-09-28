<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || !hasRole('employee')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customer = getUserById($customer_id);

if (!$customer || $customer['assigned_to'] != $_SESSION['user_id']) {
    header('Location: my_customers.php');
    exit();
}

$call_history = getCallRecordsByCustomer($customer_id, $_SESSION['user_id']);

$page_title = 'Call History - ' . htmlspecialchars($customer['name']);

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<div class="wrapper">
    <?php include_once '../../components/sidebar_employee.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-history"></i> Call History: <?php echo htmlspecialchars($customer['name']); ?></h1>
            <div class="header-actions">
                <a href="my_customers.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to My Customers
                </a>
                <a href="call_customer.php?id=<?php echo $customer_id; ?>" class="btn btn-primary">
                    <i class="fas fa-phone"></i> Call Again
                </a>
            </div>
        </div>
        
        <div class="customer-info-card">
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value"><?php echo htmlspecialchars($customer['name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($customer['email']) ?: 'N/A'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value"><?php echo htmlspecialchars($customer['phone']) ?: 'N/A'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Company:</span>
                <span class="info-value"><?php echo htmlspecialchars($customer['company']) ?: 'N/A'; ?></span>
            </div>
        </div>
        
        <?php if (empty($call_history)): ?>
            <div class="empty-state">
                <i class="fas fa-phone-slash"></i>
                <p>No call history found for this customer.</p>
                <a href="call_customer.php?id=<?php echo $customer_id; ?>" class="btn btn-primary">
                    <i class="fas fa-phone"></i> Make First Call
                </a>
            </div>
        <?php else: ?>
            <div class="history-list">
                <?php foreach ($call_history as $call): ?>
                    <div class="history-card">
                        <div class="history-header">
                            <div class="history-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('F j, Y h:i A', strtotime($call['called_at'])); ?>
                            </div>
                            <div class="history-interest interest-<?php echo $call['interest_level']; ?>">
                                <?php 
                                if ($call['interest_level'] == 'interested') echo '✅ Interested';
                                elseif ($call['interest_level'] == 'not_interested') echo '❌ Not Interested';
                                else echo '🔄 Follow Up';
                                ?>
                            </div>
                        </div>
                        <div class="history-notes">
                            <strong>Notes:</strong>
                            <p><?php echo nl2br(htmlspecialchars($call['notes'])); ?></p>
                        </div>
                        <?php if ($call['follow_up_date']): ?>
                            <div class="history-followup">
                                <i class="fas fa-calendar-alt"></i>
                                Follow-up Date: <?php echo date('F j, Y', strtotime($call['follow_up_date'])); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($call['recording_file']): ?>
                            <div class="history-recording">
                                <i class="fas fa-download"></i>
                                <a href="<?php echo BASE_URL; ?>uploads/recordings/<?php echo $call['recording_file']; ?>" target="_blank">
                                    Download Recording
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.customer-info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    color: white;
}

.info-row {
    display: flex;
    margin-bottom: 10px;
}

.info-label {
    width: 100px;
    font-weight: 500;
    opacity: 0.8;
}

.info-value {
    flex: 1;
    font-weight: 500;
}

.history-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.history-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e0e0e0;
}

.history-date {
    font-size: 13px;
    color: #666;
}

.history-interest {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.interest-interested {
    background: #d4edda;
    color: #155724;
}

.interest-not_interested {
    background: #f8d7da;
    color: #721c24;
}

.interest-follow_up {
    background: #fff3cd;
    color: #856404;
}

.history-notes {
    margin-bottom: 10px;
}

.history-notes p {
    margin: 5px 0 0 0;
    color: #555;
}

.history-followup, .history-recording {
    font-size: 13px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #f0f0f0;
}

.history-followup i, .history-recording i {
    margin-right: 8px;
    color: #3498db;
}

.history-recording a {
    color: #3498db;
    text-decoration: none;
}

.history-recording a:hover {
    text-decoration: underline;
}

.empty-state {
    text-align: center;
    padding: 60px;
    background: white;
    border-radius: 12px;
}
</style>

<?php include_once '../../components/footer.php'; ?>