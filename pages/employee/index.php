<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is employee
if (!isLoggedIn() || !hasRole('employee')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$page_title = 'Employee Dashboard';
$page_css = 'dashboard.css';
$employee_id = $_SESSION['user_id'];
$stats = getEmployeeStats($employee_id);

// Get recent calls by this employee
$recent_calls = getCallRecordsByEmployee($employee_id);
$recent_calls = array_slice($recent_calls, 0, 5);

// Get call trends for last 7 days
$call_trends = [];
$trend_labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $trend_labels[] = date('D, M j', strtotime($date));
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM call_records WHERE employee_id = ? AND DATE(called_at) = ?");
    $stmt->execute([$employee_id, $date]);
    $call_trends[] = (int)$stmt->fetchColumn();
}

// Get interest distribution for this employee
$stmt = $pdo->prepare("SELECT interest_level, COUNT(*) as count FROM call_records WHERE employee_id = ? GROUP BY interest_level");
$stmt->execute([$employee_id]);
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
    <?php include_once '../../components/sidebar_employee.php'; ?>
    
    <div class="main-content">
        <div class="dashboard-container">
            <!-- Welcome Section -->
            <div class="welcome-section employee-welcome-section">
                <div class="welcome-text">
                    <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h2>
                    <p>Here's your performance overview and pending tasks.</p>
                </div>
                <div class="welcome-date">
                    <div class="date" id="currentDate"></div>
                    <div class="time" id="currentTime"></div>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card-modern">
                    <div class="stat-icon-modern employee-stat-icon customers">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info-modern">
                        <h3><?php echo number_format($stats['assigned_customers']); ?></h3>
                        <p>My Customers</p>
                    </div>
                </div>
                
                <div class="stat-card-modern">
                    <div class="stat-icon-modern employee-stat-icon calls">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="stat-info-modern">
                        <h3><?php echo number_format($stats['total_calls']); ?></h3>
                        <p>Total Calls Made</p>
                    </div>
                </div>
                
                <div class="stat-card-modern">
                    <div class="stat-icon-modern employee-stat-icon followups">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info-modern">
                        <h3><?php echo number_format($stats['pending_follow_ups']); ?></h3>
                        <p>Pending Follow-ups</p>
                    </div>
                </div>
                
                <div class="stat-card-modern">
                    <div class="stat-icon-modern employee-stat-icon today">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info-modern">
                        <h3><?php echo number_format($stats['today_calls']); ?></h3>
                        <p>Today's Calls</p>
                    </div>
                </div>
            </div>
            
            <!-- Performance Stats -->
            <div class="performance-section">
                <div class="performance-header">
                    <h3><i class="fas fa-chart-line"></i> Performance Overview</h3>
                    <i class="fas fa-chart-line" style="color: #1abc9c;"></i>
                </div>
                <div class="performance-stats">
                    <div class="perf-card">
                        <div class="perf-value"><?php echo $stats['total_calls'] > 0 ? round(($stats['interested_calls'] ?? 0) / $stats['total_calls'] * 100) : 0; ?>%</div>
                        <div class="perf-label">Interest Rate</div>
                    </div>
                    <div class="perf-card">
                        <div class="perf-value"><?php echo $stats['assigned_customers'] > 0 ? round(($stats['total_calls'] ?? 0) / $stats['assigned_customers'] * 100) : 0; ?>%</div>
                        <div class="perf-label">Call Coverage</div>
                    </div>
                    <div class="perf-card">
                        <div class="perf-value"><?php echo $stats['pending_follow_ups']; ?></div>
                        <div class="perf-label">Needs Attention</div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="charts-row">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-line"></i> My Call Trends (Last 7 Days)</h3>
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="chart-container">
                        <canvas id="callsChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-pie"></i> My Call Interest Distribution</h3>
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
                    <h3><i class="fas fa-clock"></i> My Recent Calls</h3>
                    <a href="my_history.php" class="btn-sm btn-info">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="activity-list">
                    <?php if (empty($recent_calls)): ?>
                        <div class="activity-item">
                            <div class="activity-icon call">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-title">No recent calls</div>
                                <div class="activity-time">Start calling your customers</div>
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
                                        Called <?php echo htmlspecialchars($call['user_name']); ?>
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
                    <a href="my_customers.php" class="quick-action-card">
                        <i class="fas fa-users"></i>
                        <span>My Customers</span>
                    </a>
                    <a href="follow_ups.php" class="quick-action-card">
                        <i class="fas fa-calendar-alt"></i>
                        <span>View Follow-ups</span>
                    </a>
                    <a href="my_history.php" class="quick-action-card">
                        <i class="fas fa-history"></i>
                        <span>Call History</span>
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
            label: 'My Calls',
            data: <?php echo json_encode($call_trends); ?>,
            borderColor: '#0acffe',
            backgroundColor: '#0acdfe29',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#0acffe',
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