<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$page_title = 'Dashboard';
$page_css = 'dashboard.css';
$stats = getAdminStats();

// Get recent activities (last 5 calls)
$recent_calls = getAllCallRecords();
$recent_calls = array_slice($recent_calls, 0, 5);

// Get call trends for last 7 days
$call_trends = [];
$trend_labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $trend_labels[] = date('D, M j', strtotime($date));
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM call_records WHERE DATE(called_at) = ?");
    $stmt->execute([$date]);
    $call_trends[] = (int)$stmt->fetchColumn();
}

// Get interest distribution
$stmt = $pdo->query("SELECT interest_level, COUNT(*) as count FROM call_records GROUP BY interest_level");
$interest_data = $stmt->fetchAll();
$interest_labels = [];
$interest_values = [];
$labelMap = ['interested' => 'Interested', 'not_interested' => 'Not Interested', 'follow_up' => 'Follow Up'];

foreach ($interest_data as $row) {
    $interest_labels[] = $labelMap[$row['interest_level']] ?? ucfirst($row['interest_level']);
    $interest_values[] = (int)$row['count'];
}

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<div class="wrapper">
    <?php include_once '../../components/sidebar_admin.php'; ?>
    
    <div class="main-content">
        <div class="dashboard-container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <div class="welcome-text">
                    <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h2>
                    <p>Here's what's happening with your CRM today.</p>
                </div>
                <div class="welcome-date">
                    <div class="date" id="currentDate"></div>
                    <div class="time" id="currentTime"></div>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card-modern">
                    <div class="stat-icon-modern customers">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info-modern">
                        <h3><?php echo number_format($stats['total_customers']); ?></h3>
                        <p>Total Customers</p>
                    </div>
                </div>
                
                <div class="stat-card-modern">
                    <div class="stat-icon-modern employees">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-info-modern">
                        <h3><?php echo number_format($stats['total_employees']); ?></h3>
                        <p>Active Employees</p>
                    </div>
                </div>
                
                <div class="stat-card-modern">
                    <div class="stat-icon-modern calls">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="stat-info-modern">
                        <h3><?php echo number_format($stats['total_calls']); ?></h3>
                        <p>Total Calls</p>
                    </div>
                </div>
                
                <div class="stat-card-modern">
                    <div class="stat-icon-modern followups">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info-modern">
                        <h3><?php echo number_format($stats['pending_follow_ups']); ?></h3>
                        <p>Pending Follow-ups</p>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="charts-row">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-line"></i> Call Trends (Last 7 Days)</h3>
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="chart-container">
                        <canvas id="callsChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-pie"></i> Call Interest Distribution</h3>
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="chart-container">
                        <canvas id="interestChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="activity-section">
                <div class="activity-header">
                    <h3><i class="fas fa-clock"></i> Recent Activity</h3>
                    <a href="call_records.php" class="btn-sm btn-info">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="activity-list">
                    <?php if (empty($recent_calls)): ?>
                        <div class="activity-item">
                            <div class="activity-icon call">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-title">No recent calls</div>
                                <div class="activity-time">No activity to display</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_calls as $call): ?>
                            <div class="activity-item">
                                <div class="activity-icon call">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-title">
                                        <?php echo htmlspecialchars($call['employee_name']); ?> called 
                                        <?php echo htmlspecialchars($call['user_name']); ?>
                                        <?php if ($call['interest_level'] == 'interested'): ?>
                                            <span class="badge badge-success">Interested</span>
                                        <?php elseif ($call['interest_level'] == 'not_interested'): ?>
                                            <span class="badge badge-danger">Not Interested</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Follow Up</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-time">
                                        <i class="far fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($call['called_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions-section">
                <div class="quick-actions-header">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="quick-actions-grid">
                    <a href="add_customer.php" class="quick-action-card">
                        <i class="fas fa-user-plus"></i>
                        <span>Add Customer</span>
                    </a>
                    <a href="bulk_import.php" class="quick-action-card">
                        <i class="fas fa-file-import"></i>
                        <span>Bulk Import</span>
                    </a>
                    <a href="add_employee.php" class="quick-action-card">
                        <i class="fas fa-user-tie"></i>
                        <span>Add Employee</span>
                    </a>
                    <a href="assign.php" class="quick-action-card">
                        <i class="fas fa-user-check"></i>
                        <span>Assign Customers</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Define BASE_URL for JavaScript
var BASE_URL = '<?php echo BASE_URL; ?>';

// Live Date and Time
function updateDateTime() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').innerHTML = now.toLocaleDateString('en-US', options);
    document.getElementById('currentTime').innerHTML = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}
updateDateTime();
setInterval(updateDateTime, 1000);

// Calls Chart (Last 7 Days)
const callsCtx = document.getElementById('callsChart').getContext('2d');
const callsChart = new Chart(callsCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($trend_labels); ?>,
        datasets: [{
            label: 'Number of Calls',
            data: <?php echo json_encode($call_trends); ?>,
            borderColor: '#4481eb',
            backgroundColor: '#4481eb21',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#4481eb',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { 
                beginAtZero: true, 
                grid: { color: '#e0e0e0' },
                title: { display: true, text: 'Number of Calls' }
            },
            x: { 
                grid: { display: false },
                title: { display: true, text: 'Date' }
            }
        }
    }
});

// Interest Distribution Chart
const interestCtx = document.getElementById('interestChart').getContext('2d');
const interestChart = new Chart(interestCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($interest_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($interest_values); ?>,
            backgroundColor: ['#27ae60', '#e74c3c', '#f39c12'],
            borderWidth: 0,
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { 
                callbacks: { 
                    label: function(context) { 
                        return context.label + ': ' + context.raw + ' calls'; 
                    } 
                } 
            }
        }
    }
});
</script>

<?php include_once '../../components/footer.php'; ?>