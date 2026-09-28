<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is employee
if (!isLoggedIn() || !hasRole('employee')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$page_title = 'My Customers';

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<div class="wrapper">
    <?php include_once '../../components/sidebar_employee.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-users"></i> My Customers</h1>
            <div class="header-actions">
                <div class="btn-group">
                    <button onclick="setView('datewise')" id="btnDatewiseView" class="btn btn-info active-view">
                        <i class="fas fa-calendar-alt"></i> Date-wise View
                    </button>
                    <button onclick="setView('simple')" id="btnSimpleView" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Simple List View
                    </button>
                </div>
                <button onclick="exportToExcel()" class="btn btn-info">
                    <i class="fas fa-file-excel"></i> Export
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
        
        <!-- Legend / Status Guide -->
        <div class="legend-card">
            <div class="legend-header">
                <i class="fas fa-info-circle"></i> Status Guide
            </div>
            <div class="legend-items">
                <div class="legend-item">
                    <span class="status-dot pending-dot"></span>
                    <span>⚪ Not Called Yet - Need to call</span>
                </div>
                <div class="legend-item">
                    <span class="status-dot success-dot"></span>
                    <span>✅ Interested - Customer interested</span>
                </div>
                <div class="legend-item">
                    <span class="status-dot danger-dot"></span>
                    <span>❌ Not Interested - Customer rejected</span>
                </div>
                <div class="legend-item">
                    <span class="status-dot warning-dot"></span>
                    <span>🔄 Follow Up - Call back on date</span>
                </div>
                <div class="legend-item">
                    <span class="status-dot overdue-dot"></span>
                    <span>⚠️ Overdue - Follow up date passed</span>
                </div>
            </div>
        </div>
        
        <!-- Filters Row -->
        <div class="filters-card">
            <h3><i class="fas fa-filter"></i> Filter Customers</h3>
            <div class="filters-row">
                <div class="filter-group">
                    <label>Status Filter</label>
                    <select id="statusFilter" class="form-control">
                        <option value="all">All Customers</option>
                        <option value="pending">Not Called Yet</option>
                        <option value="interested">Interested</option>
                        <option value="not_interested">Not Interested</option>
                        <option value="follow_up">Follow Up</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" id="dateFrom" class="form-control">
                </div>
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" id="dateTo" class="form-control">
                </div>
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Name, email, phone...">
                </div>
                <div class="filter-group filter-actions">
                    <label>&nbsp;</label>
                    <button onclick="applyFilters()" class="btn btn-primary">Apply Filters</button>
                    <button onclick="resetFilters()" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </div>
        
        <!-- Summary Stats -->
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-value" id="totalCount">0</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="pendingCount">0</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="interestedCount">0</div>
                <div class="stat-label">Interested</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="notInterestedCount">0</div>
                <div class="stat-label">Not Interested</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="followUpCount">0</div>
                <div class="stat-label">Follow Up</div>
            </div>
        </div>
        
        <!-- Customers Container -->
        <div id="customersContainer">
            <div class="loading-text">Loading customers...</div>
        </div>
    </div>
</div>

<style>
.btn-group {
    display: flex;
    gap: 5px;
    margin-right: 10px;
}

.active-view {
    background: #3498db !important;
    color: white !important;
    border: none;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
    border: none;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.loading-text {
    text-align: center;
    padding: 40px;
    background: white;
    border-radius: 12px;
    color: #666;
}

.legend-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 12px 20px;
    margin-bottom: 20px;
    border: 1px solid #e0e0e0;
}

.legend-header {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.legend-items {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #555;
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.pending-dot { background: #6c757d; }
.success-dot { background: #28a745; }
.danger-dot { background: #dc3545; }
.warning-dot { background: #f39c12; }
.overdue-dot { background: #e74c3c; }

.filters-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.filters-card h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #333;
}

.filters-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 140px;
}

.filter-group label {
    font-size: 12px;
    font-weight: 500;
    color: #666;
    margin-bottom: 5px;
}

.filter-actions {
    min-width: auto;
    flex: 0 0 auto;
    display: flex;
    gap: 8px;
}

.form-control {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.stats-summary {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 12px 10px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #3498db;
}

.stat-label {
    font-size: 11px;
    color: #666;
    margin-top: 5px;
}

.date-group {
    background: white;
    border-radius: 12px;
    margin-bottom: 15px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.date-header {
    background: #f8f9fa;
    padding: 12px 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #e0e0e0;
}

.date-header i {
    transition: transform 0.3s;
    color: #3498db;
}

.date-header.collapsed i {
    transform: rotate(-90deg);
}

.date-header strong {
    font-size: 14px;
    color: #333;
}

.customer-count {
    margin-left: auto;
    font-size: 12px;
    color: #666;
}

.date-content {
    padding: 15px;
    display: block;
}

.date-content.hide {
    display: none;
}

.simple-list, .flat-list {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.simple-header, .flat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
}

.simple-header h3, .flat-header h3 {
    margin: 0;
    font-size: 16px;
    color: #333;
}

.customers-table {
    width: 100%;
    border-collapse: collapse;
}

.customers-table th,
.customers-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
    vertical-align: middle;
}

.customers-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    font-size: 13px;
}

.customers-table tr:hover {
    background: #f5f5f5;
}

.serial-number {
    color: #666;
    text-align: center;
    width: 50px;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
}

.badge-info {
    background: #e3f2fd;
    color: #1976d2;
}

.badge-warning {
    background: #fff3e0;
    color: #e65100;
}

.badge-success {
    background: #e8f5e9;
    color: #2e7d32;
}

.badge-danger {
    background: #ffebee;
    color: #c62828;
}

.badge-secondary {
    background: #6c757d;
    color: white;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 11px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-warning {
    background: #f39c12;
    color: white;
}

.btn-warning:hover {
    background: #e67e22;
}

.btn-info {
    background: #1abc9c;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 60px;
    background: white;
    border-radius: 12px;
}

.empty-state i {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 15px;
}

@media (max-width: 992px) {
    .filters-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .stats-summary {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .legend-items {
        flex-direction: column;
        gap: 8px;
    }
    
    .btn-group {
        width: 100%;
        justify-content: center;
    }
    
    .header-actions {
        flex-wrap: wrap;
    }
}

.toast-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 8px;
    z-index: 9999;
    animation: slideIn 0.3s ease;
    color: white;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>

<script>
var BASE_URL = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/crm_system/';
var allCustomers = [];
var currentView = 'datewise';

function showLoading() {
    document.getElementById('customersContainer').innerHTML = '<div class="loading-text">Loading customers...</div>';
}

function setView(view) {
    currentView = view;
    
    var btnDatewise = document.getElementById('btnDatewiseView');
    var btnSimple = document.getElementById('btnSimpleView');
    
    if (view === 'datewise') {
        btnDatewise.className = 'btn btn-info active-view';
        btnSimple.className = 'btn btn-secondary';
    } else {
        btnDatewise.className = 'btn btn-secondary';
        btnSimple.className = 'btn btn-info active-view';
    }
    
    loadAndFilterCustomers();
}

function applyFilters() {
    loadAndFilterCustomers();
}

function resetFilters() {
    document.getElementById('statusFilter').value = 'all';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('searchInput').value = '';
    loadAndFilterCustomers();
}

function checkIfFiltersApplied() {
    var status = document.getElementById('statusFilter').value;
    var dateFrom = document.getElementById('dateFrom').value;
    var dateTo = document.getElementById('dateTo').value;
    var search = document.getElementById('searchInput').value;
    
    return (status !== 'all' || dateFrom !== '' || dateTo !== '' || search !== '');
}

function loadAndFilterCustomers() {
    showLoading();
    
    var employeeId = <?php echo $_SESSION['user_id']; ?>;
    var hasFilters = checkIfFiltersApplied();
    
    fetch(BASE_URL + 'api/employee_customers.php?action=list&employee_id=' + employeeId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                var customers = [];
                for (var date in data.data) {
                    if (data.data.hasOwnProperty(date)) {
                        for (var i = 0; i < data.data[date].length; i++) {
                            customers.push(data.data[date][i]);
                        }
                    }
                }
                allCustomers = customers;
                var filtered = filterCustomers(customers);
                
                if (currentView === 'simple') {
                    displaySimpleList(filtered);
                } else if (hasFilters) {
                    displayFlatList(filtered);
                } else {
                    var grouped = groupByDate(filtered);
                    displayGroupedList(grouped);
                }
                
                updateStats(customers, filtered);
            } else {
                document.getElementById('customersContainer').innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><p>No customers assigned to you</p><p class="text-muted">Contact your admin to assign customers.</p></div>';
            }
        })
        .catch(function(error) {
            document.getElementById('customersContainer').innerHTML = '<div class="empty-state">Error loading customers</div>';
        });
}

function filterCustomers(customers) {
    var statusFilter = document.getElementById('statusFilter').value;
    var dateFrom = document.getElementById('dateFrom').value;
    var dateTo = document.getElementById('dateTo').value;
    var searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    var result = [];
    for (var i = 0; i < customers.length; i++) {
        var customer = customers[i];
        var match = true;
        
        if (statusFilter !== 'all') {
            var callStatus = customer.call_status || 'pending';
            if (statusFilter === 'pending' && callStatus !== 'pending') match = false;
            if (statusFilter === 'interested' && callStatus !== 'interested') match = false;
            if (statusFilter === 'not_interested' && callStatus !== 'not_interested') match = false;
            if (statusFilter === 'follow_up' && callStatus !== 'follow_up' && callStatus !== 'follow_up_overdue' && callStatus !== 'follow_up_today') match = false;
        }
        
        if (dateFrom && customer.assignment_date && customer.assignment_date < dateFrom) match = false;
        if (dateTo && customer.assignment_date && customer.assignment_date > dateTo) match = false;
        
        if (searchTerm && match) {
            var matchName = customer.name.toLowerCase().indexOf(searchTerm) !== -1;
            var matchEmail = (customer.email || '').toLowerCase().indexOf(searchTerm) !== -1;
            var matchPhone = (customer.phone || '').toLowerCase().indexOf(searchTerm) !== -1;
            var matchCompany = (customer.company || '').toLowerCase().indexOf(searchTerm) !== -1;
            if (!matchName && !matchEmail && !matchPhone && !matchCompany) match = false;
        }
        
        if (match) result.push(customer);
    }
    return result;
}

function groupByDate(customers) {
    var grouped = {};
    for (var i = 0; i < customers.length; i++) {
        var customer = customers[i];
        var date = customer.assignment_date || customer.created_at;
        if (!grouped[date]) grouped[date] = [];
        grouped[date].push(customer);
    }
    var sortedDates = Object.keys(grouped).sort(function(a, b) { return new Date(b) - new Date(a); });
    var sortedGrouped = {};
    for (var d = 0; d < sortedDates.length; d++) {
        sortedGrouped[sortedDates[d]] = grouped[sortedDates[d]];
    }
    return sortedGrouped;
}

function displaySimpleList(customers) {
    var container = document.getElementById('customersContainer');
    
    if (customers.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><p>No customers found</p><button onclick="resetFilters()" class="btn btn-primary mt-3">Reset Filters</button></div>';
        return;
    }
    
    var html = '<div class="simple-list"><div class="simple-header"><h3><i class="fas fa-list"></i> My Customers (' + customers.length + ')</h3></div><div class="table-responsive"><table class="customers-table"><thead>';
    html += '<tr><th width="50">#</th><th>Name</th><th>Email</th><th>Phone</th><th>Company</th><th>Status</th><th>Assigned Date</th><th width="100">Action</th></tr>';
    html += '</thead><tbody>';
    
    for (var i = 0; i < customers.length; i++) {
        var c = customers[i];
        var statusClass = getStatusClass(c.call_status);
        var statusLabel = getStatusLabel(c);
        var buttonHtml = getButtonHtml(c);
        
        html += '<tr>';
        html += '<td class="serial-number">' + (i + 1) + '</td>';
        html += '<td><strong>' + escapeHtml(c.name) + '</strong><br><small class="company-name">' + escapeHtml(c.company) + '</small></td>';
        html += '<td>' + (escapeHtml(c.email) || '—') + '</td>';
        html += '<td>' + (escapeHtml(c.phone) || '—') + '<td>';
        html += '<td>' + (escapeHtml(c.company) || '—') + '</td>';
        html += '<td><span class="badge ' + statusClass + '">' + statusLabel + '</span></td>';
        html += '<td>' + formatDate(c.assignment_date || c.created_at) + '<td>';
        html += '<td class="action-buttons">' + buttonHtml + '</td>';
        html += '</tr>';
    }
    
    html += '</tbody></tr></div></div>';
    container.innerHTML = html;
}

function displayFlatList(customers) {
    var container = document.getElementById('customersContainer');
    
    if (customers.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><p>No customers match your filters</p><button onclick="resetFilters()" class="btn btn-primary mt-3">Reset Filters</button></div>';
        return;
    }
    
    var html = '<div class="flat-list"><div class="flat-header"><h3><i class="fas fa-list"></i> Filtered Results (' + customers.length + ' customers)</h3><button onclick="resetFilters()" class="btn btn-sm btn-secondary">Clear Filters</button></div><div class="table-responsive"><table class="customers-table"><thead>';
    html += '<tr><th width="50">#</th><th>Name</th><th>Email</th><th>Phone</th><th>Company</th><th>Status</th><th>Assigned Date</th><th width="100">Action</th></tr>';
    html += '</thead><tbody>';
    
    for (var i = 0; i < customers.length; i++) {
        var c = customers[i];
        var statusClass = getStatusClass(c.call_status);
        var statusLabel = getStatusLabel(c);
        var buttonHtml = getButtonHtml(c);
        
        html += '<tr>';
        html += '<td class="serial-number">' + (i + 1) + '</td>';
        html += '<td><strong>' + escapeHtml(c.name) + '</strong><br><small class="company-name">' + escapeHtml(c.company) + '</small></td>';
        html += '<td>' + (escapeHtml(c.email) || '—') + '</td>';
        html += '<td>' + (escapeHtml(c.phone) || '—') + '</td>';
        html += '<td>' + (escapeHtml(c.company) || '—') + '</td>';
        html += '<td><span class="badge ' + statusClass + '">' + statusLabel + '</span></td>';
        html += '<td>' + formatDate(c.assignment_date || c.created_at) + '</td>';
        html += '<td class="action-buttons">' + buttonHtml + '</td>';
        html += '</tr>';
    }
    
    html += '</tbody></table></div></div>';
    container.innerHTML = html;
}

function displayGroupedList(grouped) {
    var container = document.getElementById('customersContainer');
    var dates = Object.keys(grouped);
    
    if (dates.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><p>No customers assigned to you</p></div>';
        return;
    }
    
    var html = '';
    var globalSerial = 1;
    
    for (var d = 0; d < dates.length; d++) {
        var date = dates[d];
        var customers = grouped[date];
        
        html += '<div class="date-group">';
        html += '<div class="date-header">';
        html += '<i class="fas fa-chevron-down"></i>';
        html += '<strong>📅 Assigned: ' + formatDate(date) + '</strong>';
        html += '<span class="customer-count">' + customers.length + ' customers</span>';
        html += '</div>';
        
        html += '<div class="date-content">';
        html += '<div class="table-responsive">';
        html += '<table class="customers-table">';
        html += '<thead><tr>';
        html += '<th width="50">#</th>';
        html += '<th>Name</th>';
        html += '<th>Email</th>';
        html += '<th>Phone</th>';
        html += '<th>Company</th>';
        html += '<th>Status</th>';
        html += '<th width="100">Action</th>';
        html += '</tr></thead>';
        html += '<tbody>';
        
        for (var i = 0; i < customers.length; i++) {
            var c = customers[i];
            var statusClass = getStatusClass(c.call_status);
            var statusLabel = getStatusLabel(c);
            var buttonHtml = getButtonHtml(c);
            
            html += '<td>';
            html += '<td class="serial-number">' + (globalSerial++) + '</td>';
            html += '<td><strong>' + escapeHtml(c.name) + '</strong><br><small class="company-name">' + escapeHtml(c.company) + '</small></td>';
            html += '<td>' + (escapeHtml(c.email) || '—') + '</td>';
            html += '<td>' + (escapeHtml(c.phone) || '—') + '</td>';
            html += '<td>' + (escapeHtml(c.company) || '—') + '</td>';
            html += '<td><span class="badge ' + statusClass + '">' + statusLabel + '</span></td>';
            html += '<td class="action-buttons">' + buttonHtml + '</td>';
            html += '</tr>';
        }
        
        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
    }
    
    container.innerHTML = html;
    
    var headers = document.querySelectorAll('.date-header');
    for (var h = 0; h < headers.length; h++) {
        headers[h].addEventListener('click', function(e) {
            e.stopPropagation();
            var dateGroup = this.closest('.date-group');
            var content = dateGroup.querySelector('.date-content');
            var icon = this.querySelector('i');
            content.classList.toggle('hide');
            this.classList.toggle('collapsed');
        });
    }
}

function getStatusClass(status) {
    switch(status) {
        case 'interested': return 'badge-success';
        case 'not_interested': return 'badge-danger';
        case 'follow_up':
        case 'follow_up_today':
        case 'follow_up_overdue': return 'badge-warning';
        default: return 'badge-secondary';
    }
}

function getStatusLabel(customer) {
    var status = customer.call_status;
    var label = customer.status_label;
    if (label) return label;
    
    switch(status) {
        case 'interested': return '✅ Interested';
        case 'not_interested': return '❌ Not Interested';
        case 'follow_up_today': return '🔴 Follow Up (Today!)';
        case 'follow_up_overdue': return '⚠️ Follow Up (Overdue)';
        case 'follow_up': return '🔄 Follow Up';
        default: return '⚪ Not Called Yet';
    }
}

function getButtonHtml(customer) {
    var action = customer.button_action;
    var text = customer.button_text;
    var btnClass = customer.button_class;
    var id = customer.id;
    
    if (action === 'call') {
        return '<a href="call_customer.php?id=' + id + '" class="btn-sm ' + btnClass + '"><i class="fas fa-phone"></i> ' + text + '</a>';
    } else {
        return '<button onclick="viewDetails(' + id + ')" class="btn-sm ' + btnClass + '"><i class="fas fa-eye"></i> ' + text + '</button>';
    }
}

function updateStats(allCustomers, filteredCustomers) {
    var total = allCustomers.length;
    var pending = 0, interested = 0, notInterested = 0, followUp = 0;
    
    for (var i = 0; i < allCustomers.length; i++) {
        var status = allCustomers[i].call_status;
        if (status === 'pending' || !status) pending++;
        else if (status === 'interested') interested++;
        else if (status === 'not_interested') notInterested++;
        else if (status === 'follow_up' || status === 'follow_up_today' || status === 'follow_up_overdue') followUp++;
    }
    
    document.getElementById('totalCount').innerHTML = total;
    document.getElementById('pendingCount').innerHTML = pending;
    document.getElementById('interestedCount').innerHTML = interested;
    document.getElementById('notInterestedCount').innerHTML = notInterested;
    document.getElementById('followUpCount').innerHTML = followUp;
}

function viewDetails(id) {
    window.location.href = 'call_history_details.php?id=' + id;
}

function exportToExcel() {
    window.location.href = BASE_URL + 'api/export_employee_customers.php';
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function formatDate(dateString) {
    if (!dateString) return '';
    var date = new Date(dateString);
    return date.toLocaleDateString('en-IN', { year: 'numeric', month: 'long', day: 'numeric' });
}

document.addEventListener('DOMContentLoaded', function() {
    loadAndFilterCustomers();
});
</script>

<?php include_once '../../components/footer.php'; ?>