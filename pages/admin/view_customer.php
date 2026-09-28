<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customer = getUserById($customer_id);

if (!$customer) {
    header('Location: customers.php');
    exit();
}

// Get assigned employee name if assigned
$assigned_employee_name = 'Not Assigned';
if ($customer['assigned_to']) {
    $employee = getEmployeeById($customer['assigned_to']);
    $assigned_employee_name = $employee ? $employee['name'] : 'Unknown';
}

// Get customer with status
global $pdo;
$stmt = $pdo->prepare("SELECT * FROM call_records WHERE user_id = ? ORDER BY called_at DESC");
$stmt->execute([$customer_id]);
$call_history = $stmt->fetchAll();

// Get assignment history
$stmt = $pdo->prepare("SELECT ah.*, e.name as employee_name, a.name as assigned_by_name 
                       FROM assignments_history ah 
                       JOIN employees e ON ah.employee_id = e.id 
                       JOIN employees a ON ah.assigned_by = a.id 
                       WHERE ah.user_id = ? 
                       ORDER BY ah.assigned_at DESC");
$stmt->execute([$customer_id]);
$assignment_history = $stmt->fetchAll();

$page_title = 'View Customer - ' . htmlspecialchars($customer['name']);

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<div class="wrapper">
    <?php include_once '../../components/sidebar_admin.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-user-circle"></i> Customer Details</h1>
            <div class="header-actions">
                <a href="customers.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Customers
                </a>
                <a href="edit_customer.php?id=<?php echo $customer_id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Customer
                </a>
            </div>
        </div>
        
        <!-- Customer Information Card -->
        <div class="info-card">
            <h3><i class="fas fa-info-circle"></i> Customer Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Customer ID:</label>
                    <span>#<?php echo $customer['id']; ?></span>
                </div>
                <div class="info-item">
                    <label>Full Name:</label>
                    <span><?php echo htmlspecialchars($customer['name']); ?></span>
                </div>
                <div class="info-item">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($customer['email']) ?: '—'; ?></span>
                </div>
                <div class="info-item">
                    <label>Phone:</label>
                    <span><?php echo htmlspecialchars($customer['phone']) ?: '—'; ?></span>
                </div>
                <div class="info-item">
                    <label>Company:</label>
                    <span><?php echo htmlspecialchars($customer['company']) ?: '—'; ?></span>
                </div>
                <div class="info-item">
                    <label>Address:</label>
                    <span><?php echo nl2br(htmlspecialchars($customer['address'])) ?: '—'; ?></span>
                </div>
                <div class="info-item">
                    <label>Status:</label>
                    <span><?php echo $customer['status'] === 'active' ? '🟢 Active' : '🔴 Inactive'; ?></span>
                </div>
                <div class="info-item">
                    <label>Created Date:</label>
                    <span><?php echo date('F j, Y', strtotime($customer['created_at'])); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Assignment Information -->
        <div class="info-card">
            <h3><i class="fas fa-user-tie"></i> Assignment Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Assigned To:</label>
                    <span>
                        <?php if ($customer['assigned_to']): ?>
                            <?php echo htmlspecialchars($assigned_employee_name); ?>
                        <?php else: ?>
                            <span class="text-muted">Not Assigned</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-item">
                    <label>Assignment Date:</label>
                    <span><?php echo $customer['assignment_date'] ? date('F j, Y', strtotime($customer['assignment_date'])) : '—'; ?></span>
                </div>
            </div>
            
            <?php if (!empty($assignment_history)): ?>
            <div class="assignment-history">
                <h4>Assignment History</h4>
                <table class="history-table">
                    <thead>
                        <tr><th>Date</th><th>Assigned To</th><th>Assigned By</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignment_history as $history): ?>
                        <tr>
                            <td><?php echo date('d M Y h:i A', strtotime($history['assigned_at'])); ?></td>
                            <td><?php echo htmlspecialchars($history['employee_name']); ?></td>
                            <td><?php echo htmlspecialchars($history['assigned_by_name']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Call History -->
        <div class="info-card">
            <h3><i class="fas fa-phone-alt"></i> Call History</h3>
            <?php if (empty($call_history)): ?>
                <div class="empty-calls">
                    <i class="fas fa-phone-slash"></i>
                    <p>No call records found for this customer.</p>
                </div>
            <?php else: ?>
                <div class="call-history-list">
                    <?php foreach ($call_history as $call): ?>
                        <div class="call-record">
                            <div class="call-header">
                                <div class="call-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('F j, Y h:i A', strtotime($call['called_at'])); ?>
                                </div>
                                <div class="call-interest interest-<?php echo $call['interest_level']; ?>">
                                    <?php 
                                    if ($call['interest_level'] == 'interested') echo '✅ Interested';
                                    elseif ($call['interest_level'] == 'not_interested') echo '❌ Not Interested';
                                    else echo '🔄 Follow Up';
                                    ?>
                                </div>
                            </div>
                            <div class="call-notes">
                                <strong>Notes:</strong>
                                <p><?php echo nl2br(htmlspecialchars($call['notes'])); ?></p>
                            </div>
                            <?php if ($call['follow_up_date']): ?>
                                <div class="call-followup">
                                    <i class="fas fa-calendar-alt"></i>
                                    Follow-up Date: <?php echo date('F j, Y', strtotime($call['follow_up_date'])); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($call['recording_file']): ?>
                                <div class="call-recording">
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
</div>

<style>
.info-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.info-card h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #333;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 10px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-item label {
    font-size: 11px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.info-item span {
    font-size: 14px;
    color: #333;
}

.assignment-history {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
}

.assignment-history h4 {
    font-size: 13px;
    margin: 0 0 10px 0;
    color: #666;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.history-table th,
.history-table td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.history-table th {
    background: #f8f9fa;
    font-weight: 600;
}

.empty-calls {
    text-align: center;
    padding: 30px;
    color: #999;
}

.empty-calls i {
    font-size: 36px;
    margin-bottom: 10px;
    display: block;
}

.call-history-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.call-record {
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 15px;
    transition: box-shadow 0.3s;
}

.call-record:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.call-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
}

.call-date {
    font-size: 12px;
    color: #666;
}

.call-interest {
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
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

.call-notes {
    margin-bottom: 8px;
}

.call-notes p {
    margin: 5px 0 0 0;
    font-size: 13px;
    color: #555;
}

.call-followup, .call-recording {
    font-size: 12px;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #f0f0f0;
}

.call-followup i, .call-recording i {
    margin-right: 6px;
    color: #3498db;
}

.call-recording a {
    color: #3498db;
    text-decoration: none;
}

.call-recording a:hover {
    text-decoration: underline;
}

.text-muted {
    color: #999;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .call-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>

<?php include_once '../../components/footer.php'; ?>