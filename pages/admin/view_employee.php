<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$employee_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$employee = getEmployeeById($employee_id);

if (!$employee) {
    header('Location: employees.php');
    exit();
}

$page_title = 'View Employee - ' . htmlspecialchars($employee['name']);

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<style>
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .info-card h3 {
        margin: 0 0 15px 0;
        font-size: 16px;
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

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    /* Search & Filter Styles */
    .search-section {
        margin-bottom: 20px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    
    .search-group {
        flex: 1;
        min-width: 200px;
    }
    
    .search-group label {
        font-size: 11px;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 5px;
        display: block;
    }
    
    .search-group input,
    .search-group select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .search-group input:focus,
    .search-group select:focus {
        outline: none;
        border-color: #3498db;
    }
    
    .search-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-primary {
        background: #3498db;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        cursor: pointer;
    }
    
    .btn-primary:hover {
        background: #2980b9;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        cursor: pointer;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
    }
    
    /* Table Styles */
    .customers-table {
        width: 100%;
        border-collapse: collapse;
    }

    .customers-table th,
    .customers-table td {
        padding: 12px 10px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }

    .customers-table th {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 13px;
    }
    
    .customers-table tbody tr:hover {
        background: #f8f9fa;
    }

    /* Pagination Styles */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e0e0e0;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .pagination-info {
        font-size: 13px;
        color: #666;
    }
    
    .pagination-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .pagination-btn {
        padding: 6px 12px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.2s;
    }
    
    .pagination-btn:hover:not(:disabled) {
        background: #3498db;
        border-color: #3498db;
        color: white;
    }
    
    .pagination-btn.active {
        background: #3498db;
        border-color: #3498db;
        color: white;
    }
    
    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .empty-calls {
        text-align: center;
        padding: 40px;
        color: #999;
    }

    .empty-calls i {
        font-size: 48px;
        margin-bottom: 15px;
        display: block;
    }
    
    .loading-text {
        text-align: center;
        padding: 40px;
        color: #666;
    }
    
    .customer-link {
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
    }
    
    .customer-link:hover {
        text-decoration: underline;
    }
    
    @media (max-width: 768px) {
        .search-section {
            flex-direction: column;
        }
        
        .search-group {
            width: 100%;
        }
        
        .pagination-container {
            flex-direction: column;
            text-align: center;
        }
        
        .customers-table {
            font-size: 12px;
        }
        
        .customers-table th,
        .customers-table td {
            padding: 8px 5px;
        }
    }
</style>

<div class="wrapper">
    <?php include_once '../../components/sidebar_admin.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-user-circle"></i> Employee Details</h1>
            <div class="header-actions">
                <a href="employees.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Employees
                </a>
                <a href="edit_employee.php?id=<?php echo $employee_id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Employee
                </a>
            </div>
        </div>

        <!-- Employee Information Card -->
        <div class="info-card">
            <h3><i class="fas fa-info-circle"></i> Employee Information</h3>
            <div class="info-grid">
                <div class="info-item"><label>Employee ID:</label><span>#<?php echo $employee['id']; ?></span></div>
                <div class="info-item"><label>Full Name:</label><span><?php echo htmlspecialchars($employee['name']); ?></span></div>
                <div class="info-item"><label>Email:</label><span><?php echo htmlspecialchars($employee['email']); ?></span></div>
                <div class="info-item"><label>Phone:</label><span><?php echo htmlspecialchars($employee['phone']) ?: '—'; ?></span></div>
                <div class="info-item"><label>Role:</label><span><?php echo ucfirst($employee['role']); ?></span></div>
                <div class="info-item"><label>Status:</label><span class="status-badge status-<?php echo $employee['status']; ?>"><?php echo ucfirst($employee['status']); ?></span></div>
                <div class="info-item"><label>Joined Date:</label><span><?php echo date('F j, Y', strtotime($employee['created_at'])); ?></span></div>
            </div>
        </div>

        <!-- Assigned Customers Section -->
        <div class="info-card">
            <h3><i class="fas fa-users"></i> Assigned Customers <span id="totalCustomersBadge"></span></h3>
            
            <!-- Search Section -->
            <div class="search-section">
                <div class="search-group">
                    <label><i class="fas fa-search"></i> Search Customers</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name, email, phone, or company..." autocomplete="off">
                </div>
                <div class="search-actions">
                    <button onclick="searchCustomers()" class="btn-primary"><i class="fas fa-search"></i> Search</button>
                    <button onclick="resetSearch()" class="btn-secondary"><i class="fas fa-undo"></i> Reset</button>
                </div>
            </div>
            
            <!-- Customers Table Container -->
            <div id="customersContainer">
                <div class="loading-text"><i class="fas fa-spinner fa-spin"></i> Loading customers...</div>
            </div>
            
            <!-- Pagination Container -->
            <div id="paginationContainer"></div>
        </div>
    </div>
</div>

<script>
var BASE_URL = '<?php echo BASE_URL; ?>';
var employee_id = <?php echo $employee_id; ?>;
var currentPage = 1;
var currentSearch = '';
var totalPages = 1;

function loadCustomers() {
    showLoading();
    
    var url = BASE_URL + 'api/employees.php?action=assigned_customers&employee_id=' + employee_id + '&page=' + currentPage;
    if (currentSearch) {
        url += '&search=' + encodeURIComponent(currentSearch);
    }
    
    fetch(url)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                displayCustomers(data.data.customers);
                updatePagination(data.data);
                document.getElementById('totalCustomersBadge').innerHTML = ' (' + data.data.total + ')';
            } else {
                showError(data.message);
            }
        })
        .catch(function(error) {
            showError('Error loading customers: ' + error);
        });
}

function displayCustomers(customers) {
    var container = document.getElementById('customersContainer');
    
    if (!customers || customers.length === 0) {
        container.innerHTML = '<div class="empty-calls"><i class="fas fa-users-slash"></i><p>No customers found</p><p class="text-muted">Try changing your search criteria.</p></div>';
        return;
    }
    
    var html = '<div class="table-responsive">';
    html += '<table class="customers-table">';
    html += '<thead>';
    html += '<tr>';
    html += '<th width="50">#</th>';
    html += '<th>Customer Name</th>';
    html += '<th>Email</th>';
    html += '<th>Phone</th>';
    html += '<th>Company</th>';
    html += '<th>Assigned Date</th>';
    html += '<th width="80">Action</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    
    for (var i = 0; i < customers.length; i++) {
        var c = customers[i];
        var serial = ((currentPage - 1) * 10) + i + 1;
        
        html += '<tr>';
        html += '<td>' + serial + '</td>';
        html += '<td><strong><a href="view_customer.php?id=' + c.id + '" class="customer-link">' + escapeHtml(c.name) + '</a></strong></td>';
        html += '<td>' + (escapeHtml(c.email) || '—') + '</td>';
        html += '<td>' + (escapeHtml(c.phone) || '—') + '</td>';
        html += '<td>' + (escapeHtml(c.company) || '—') + '</td>';
        html += '<td>' + formatDate(c.assignment_date || c.created_at) + '</td>';
        html += '<td><a href="view_customer.php?id=' + c.id + '" class="btn-sm btn-info" title="View Customer"><i class="fas fa-eye"></i></a></td>';
        html += '</tr>';
    }
    
    html += '</tbody>';
    html += '</table>';
    html += '</div>';
    
    container.innerHTML = html;
}

function updatePagination(data) {
    var container = document.getElementById('paginationContainer');
    totalPages = data.total_pages;
    
    if (totalPages <= 1 && data.total <= 10) {
        container.innerHTML = '';
        return;
    }
    
    var startRecord = ((data.page - 1) * data.limit) + 1;
    var endRecord = Math.min(data.page * data.limit, data.total);
    
    var html = '<div class="pagination-container">';
    html += '<div class="pagination-info">';
    html += '<i class="fas fa-info-circle"></i> Showing ' + startRecord + ' - ' + endRecord + ' of ' + data.total + ' customers';
    html += '</div>';
    html += '<div class="pagination-buttons">';
    
    // Previous button
    html += '<button class="pagination-btn" onclick="goToPage(' + (data.page - 1) + ')" ' + (data.page <= 1 ? 'disabled' : '') + '>';
    html += '<i class="fas fa-chevron-left"></i> Previous';
    html += '</button>';
    
    // Page numbers
    var startPage = Math.max(1, data.page - 2);
    var endPage = Math.min(totalPages, data.page + 2);
    
    if (startPage > 1) {
        html += '<button class="pagination-btn" onclick="goToPage(1)">1</button>';
        if (startPage > 2) html += '<button class="pagination-btn" disabled>...</button>';
    }
    
    for (var p = startPage; p <= endPage; p++) {
        html += '<button class="pagination-btn ' + (p === data.page ? 'active' : '') + '" onclick="goToPage(' + p + ')">' + p + '</button>';
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<button class="pagination-btn" disabled>...</button>';
        html += '<button class="pagination-btn" onclick="goToPage(' + totalPages + ')">' + totalPages + '</button>';
    }
    
    // Next button
    html += '<button class="pagination-btn" onclick="goToPage(' + (data.page + 1) + ')" ' + (data.page >= totalPages ? 'disabled' : '') + '>';
    html += 'Next <i class="fas fa-chevron-right"></i>';
    html += '</button>';
    
    html += '</div>';
    html += '</div>';
    
    container.innerHTML = html;
}

function goToPage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadCustomers();
}

function searchCustomers() {
    currentSearch = document.getElementById('searchInput').value.trim();
    currentPage = 1;
    loadCustomers();
}

function resetSearch() {
    document.getElementById('searchInput').value = '';
    currentSearch = '';
    currentPage = 1;
    loadCustomers();
}

function showLoading() {
    document.getElementById('customersContainer').innerHTML = '<div class="loading-text"><i class="fas fa-spinner fa-spin"></i> Loading customers...</div>';
    document.getElementById('paginationContainer').innerHTML = '';
}

function showError(message) {
    document.getElementById('customersContainer').innerHTML = '<div class="empty-calls"><i class="fas fa-exclamation-triangle"></i><p>' + message + '</p></div>';
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function formatDate(dateString) {
    if (!dateString) return '—';
    var date = new Date(dateString);
    if (isNaN(date.getTime())) return '—';
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

// Handle Enter key in search input
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchCustomers();
    }
});

// Load on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCustomers();
});
</script>

<?php include_once '../../components/footer.php'; ?>