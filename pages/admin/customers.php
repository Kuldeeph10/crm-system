<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$page_title = 'Customer Management';
$page_css = 'customers.css';
$employees = getAllEmployees();

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<div class="wrapper">
    <?php include_once '../../components/sidebar_admin.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-users"></i> Customer Management</h1>
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
                <a href="add_customer.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Customer
                </a>
                <a href="bulk_import.php" class="btn btn-success">
                    <i class="fas fa-file-import"></i> Bulk Import
                </a>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="filters-card">
            <h3><i class="fas fa-filter"></i> Advanced Filters</h3>
            <div class="filters-row">
                <div class="filter-group">
                    <label>Assignment Status</label>
                    <select id="filterAssignment" class="form-control">
                        <option value="all">All Customers</option>
                        <option value="assigned">Assigned</option>
                        <option value="unassigned">Unassigned</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Customer Status</label>
                    <select id="filterStatus" class="form-control">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Assigned To</label>
                    <select id="filterEmployee" class="form-control">
                        <option value="all">All Employees</option>
                        <option value="none">None (Unassigned)</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp['id']; ?>"><?php echo htmlspecialchars($emp['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" id="filterDateFrom" class="form-control">
                </div>

                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" id="filterDateTo" class="form-control">
                </div>
            </div>

            <div class="filters-row second-row">
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" id="filterSearch" class="form-control"
                        placeholder="Name, email, phone, company...">
                </div>

                <div class="filter-group">
                    <label>Sort By</label>
                    <select id="filterSort" class="form-control">
                        <option value="created_desc">Newest First</option>
                        <option value="created_asc">Oldest First</option>
                        <option value="name_asc">Name (A-Z)</option>
                        <option value="name_desc">Name (Z-A)</option>
                        <option value="assigned_asc">Assigned Employee (A-Z)</option>
                    </select>
                </div>

                <div class="filter-group filter-actions">
                    <label>&nbsp;</label>
                    <div class="action-buttons">
                        <button onclick="applyFilters()" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <button onclick="resetFilters()" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-value" id="totalCount">0</div>
                <div class="stat-label">Total Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="assignedCount">0</div>
                <div class="stat-label">Assigned</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="unassignedCount">0</div>
                <div class="stat-label">Unassigned</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="activeCount">0</div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="inactiveCount">0</div>
                <div class="stat-label">Inactive</div>
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

    .filters-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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
        margin-bottom: 15px;
    }

    .filters-row.second-row {
        margin-bottom: 0;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 150px;
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
    }

    .action-buttons {
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
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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

    .simple-list,
    .flat-list {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .simple-header,
    .flat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e0e0e0;
    }

    .simple-header h3,
    .flat-header h3 {
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

    .company-name {
        color: #999;
        font-size: 11px;
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
        background: #e9ecef;
        color: #6c757d;
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

    .btn-warning {
        background: #f39c12;
        color: white;
    }

    .btn-danger {
        background: #e74c3c;
        color: white;
    }

    .btn-info {
        background: #1abc9c;
        color: white;
    }

    .btn-primary {
        background: #3498db;
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
    // BASE_URL - use from main.js or set fallback
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
        document.getElementById('filterAssignment').value = 'all';
        document.getElementById('filterStatus').value = 'all';
        document.getElementById('filterEmployee').value = 'all';
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value = '';
        document.getElementById('filterSearch').value = '';
        document.getElementById('filterSort').value = 'created_desc';
        loadAndFilterCustomers();
    }

    function checkIfFiltersApplied() {
        var assignment = document.getElementById('filterAssignment').value;
        var status = document.getElementById('filterStatus').value;
        var employee = document.getElementById('filterEmployee').value;
        var dateFrom = document.getElementById('filterDateFrom').value;
        var dateTo = document.getElementById('filterDateTo').value;
        var search = document.getElementById('filterSearch').value;
        var sort = document.getElementById('filterSort').value;

        return (assignment !== 'all' ||
            status !== 'all' ||
            employee !== 'all' ||
            dateFrom !== '' ||
            dateTo !== '' ||
            search !== '' ||
            sort !== 'created_desc');
    }

    function loadAndFilterCustomers() {
        showLoading();

        var hasFilters = checkIfFiltersApplied();

        fetch(BASE_URL + 'api/customers.php?action=list')
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
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
                    var sorted = sortCustomers(filtered);

                    // If filters are applied, always show flat list for better usability
                    if (hasFilters) {
                        displayFlatList(sorted);
                    } else if (currentView === 'simple') {
                        displaySimpleList(sorted);
                    } else {
                        var grouped = groupByDate(sorted);
                        displayGroupedList(grouped);
                    }

                    updateStats(customers, filtered);
                } else {
                    document.getElementById('customersContainer').innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><p>No customers found</p></div>';
                }
            })
            .catch(function (error) {
                document.getElementById('customersContainer').innerHTML = '<div class="empty-state">Error loading customers</div>';
            });
    }

    function filterCustomers(customers) {
        var assignmentFilter = document.getElementById('filterAssignment').value;
        var statusFilter = document.getElementById('filterStatus').value;
        var employeeFilter = document.getElementById('filterEmployee').value;
        var dateFrom = document.getElementById('filterDateFrom').value;
        var dateTo = document.getElementById('filterDateTo').value;
        var searchTerm = document.getElementById('filterSearch').value.toLowerCase();

        var result = [];
        for (var i = 0; i < customers.length; i++) {
            var customer = customers[i];
            var match = true;

            if (assignmentFilter === 'assigned' && !customer.assigned_to) { match = false; }
            if (assignmentFilter === 'unassigned' && customer.assigned_to) { match = false; }

            if (statusFilter !== 'all' && customer.status !== statusFilter) { match = false; }

            if (employeeFilter === 'none' && customer.assigned_to) { match = false; }
            if (employeeFilter !== 'all' && employeeFilter !== 'none' && customer.assigned_to != employeeFilter) { match = false; }

            if (dateFrom && customer.created_at < dateFrom) { match = false; }
            if (dateTo && customer.created_at > dateTo) { match = false; }

            if (searchTerm && match) {
                var matchName = customer.name.toLowerCase().indexOf(searchTerm) !== -1;
                var matchEmail = (customer.email || '').toLowerCase().indexOf(searchTerm) !== -1;
                var matchPhone = (customer.phone || '').toLowerCase().indexOf(searchTerm) !== -1;
                var matchCompany = (customer.company || '').toLowerCase().indexOf(searchTerm) !== -1;
                if (!matchName && !matchEmail && !matchPhone && !matchCompany) { match = false; }
            }

            if (match) {
                result.push(customer);
            }
        }
        return result;
    }

    function sortCustomers(customers) {
        var sortBy = document.getElementById('filterSort').value;
        var sorted = customers.slice();

        if (sortBy === 'created_desc') {
            sorted.sort(function (a, b) {
                return new Date(b.created_at) - new Date(a.created_at);
            });
        } else if (sortBy === 'created_asc') {
            sorted.sort(function (a, b) {
                return new Date(a.created_at) - new Date(b.created_at);
            });
        } else if (sortBy === 'name_asc') {
            sorted.sort(function (a, b) {
                return a.name.localeCompare(b.name);
            });
        } else if (sortBy === 'name_desc') {
            sorted.sort(function (a, b) {
                return b.name.localeCompare(a.name);
            });
        } else if (sortBy === 'assigned_asc') {
            sorted.sort(function (a, b) {
                var nameA = a.assigned_employee_name || 'ZZZ';
                var nameB = b.assigned_employee_name || 'ZZZ';
                return nameA.localeCompare(nameB);
            });
        }
        return sorted;
    }

    function groupByDate(customers) {
        var grouped = {};
        for (var i = 0; i < customers.length; i++) {
            var customer = customers[i];
            var date = customer.created_at;
            if (!grouped[date]) {
                grouped[date] = [];
            }
            grouped[date].push(customer);
        }
        var sortedDates = Object.keys(grouped).sort(function (a, b) {
            return new Date(b) - new Date(a);
        });
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

        var html = '<div class="simple-list"><div class="simple-header"><h3><i class="fas fa-list"></i> Customer List (' + customers.length + ' customers)</h3></div><div class="table-responsive"><table class="customers-table"><thead>';
        html += '<tr>';
        html += '<th width="50">#</th>';
        html += '<th>Name</th>';
        html += '<th>Email</th>';
        html += '<th>Phone</th>';
        html += '<th>Assigned To</th>';
        html += '<th>Status</th>';
        html += '<th width="120">Actions</th>';
        html += '</tr>';
        html += '</thead><tbody>';

        for (var i = 0; i < customers.length; i++) {
            var c = customers[i];
            var statusClass = c.status_class || (c.status === 'active' ? 'badge-success' : 'badge-danger');
            var displayStatus = c.display_status || (c.status === 'active' ? 'Active' : 'Inactive');

            html += '<tr>';
            html += '<td class="serial-number">' + (i + 1) + '</td>';
            html += '<td><strong>' + escapeHtml(c.name) + '</strong><br><small class="company-name">' + escapeHtml(c.company) + '</small></td>';
            html += '<td>' + (escapeHtml(c.email) || '—') + '</td>';
            html += '<td>' + (escapeHtml(c.phone) || '—') + '</td>';
            html += '<td>' + (c.assigned_to ? '<span class="badge badge-info">' + escapeHtml(c.assigned_employee_name) + '</span>' : '<span class="badge badge-warning">Unassigned</span>') + '</td>';
            html += '<td><span class="badge ' + statusClass + '">' + displayStatus + '</span></td>';
            html += '<td class="action-buttons">';
            html += '<a href="view_customer.php?id=' + c.id + '" class="btn-sm btn-info" title="View Details"><i class="fas fa-eye"></i></a>';
            html += '<a href="edit_customer.php?id=' + c.id + '" class="btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
            html += '<button onclick="deleteCustomer(' + c.id + ')" class="btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>';
            html += '</td>';
            html += '</td>';
        }

        html += '</tbody></table></div></div>';
        container.innerHTML = html;
    }
    
    function displayFlatList(customers) {
        var container = document.getElementById('customersContainer');

        if (customers.length === 0) {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><p>No customers match your filters</p><button onclick="resetFilters()" class="btn btn-primary mt-3">Reset Filters</button></div>';
            return;
        }

        var html = '<div class="flat-list"><div class="flat-header"><h3><i class="fas fa-list"></i> Filtered Results (' + customers.length + ' customers)</h3><button onclick="resetFilters()" class="btn btn-sm btn-secondary">Clear Filters</button></div><div class="table-responsive"><table class="customers-table"><thead>';
        html += '<tr>';
        html += '<th width="50">#</th>';
        html += '<th>Name</th>';
        html += '<th>Email</th>';
        html += '<th>Phone</th>';
        html += '<th>Assigned To</th>';
        html += '<th>Status</th>';
        html += '<th width="120">Actions</th>';
        html += '</tr>';
        html += '</thead><tbody>';

        for (var i = 0; i < customers.length; i++) {
            var c = customers[i];
            var statusClass = c.status_class || (c.status === 'active' ? 'badge-success' : 'badge-danger');
            var displayStatus = c.display_status || (c.status === 'active' ? 'Active' : 'Inactive');

            html += '<tr>';
            html += '<td class="serial-number">' + (i + 1) + '</td>';
            html += '<td><strong>' + escapeHtml(c.name) + '</strong><br><small class="company-name">' + escapeHtml(c.company) + '</small></td>';
            html += '<td>' + (escapeHtml(c.email) || '—') + '</td>';
            html += '<td>' + (escapeHtml(c.phone) || '—') + '</td>';
            html += '<td>' + (c.assigned_to ? '<span class="badge badge-info">' + escapeHtml(c.assigned_employee_name) + '</span>' : '<span class="badge badge-warning">Unassigned</span>') + '</td>';
            html += '<td><span class="badge ' + statusClass + '">' + displayStatus + '</span></td>';
            html += '<td class="action-buttons">';
            html += '<a href="view_customer.php?id=' + c.id + '" class="btn-sm btn-info" title="View Details"><i class="fas fa-eye"></i></a>';
            html += '<a href="edit_customer.php?id=' + c.id + '" class="btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
            html += '<button onclick="deleteCustomer(' + c.id + ')" class="btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>';
            html += '</td>';
            html += '</tr>';
        }

        html += '</tbody></table></div></div>';
        container.innerHTML = html;
    }

    function displayGroupedList(grouped) {
        var container = document.getElementById('customersContainer');
        var dates = Object.keys(grouped);

        if (dates.length === 0) {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><p>No customers found</p></div>';
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
            html += '<strong>📅 ' + formatDate(date) + '</strong>';
            html += '<span class="customer-count">' + customers.length + ' customers</span>';
            html += '</div>';

            html += '<div class="date-content">';
            html += '<div class="table-responsive">';
            html += '<table class="customers-table">';
            html += '<thead>';
            html += '<tr>';
            html += '<th width="50">#</th>';
            html += '<th>Name</th>';
            html += '<th>Email</th>';
            html += '<th>Phone</th>';
            html += '<th>Assigned To</th>';
            html += '<th>Status</th>';
            html += '<th width="120">Actions</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            for (var i = 0; i < customers.length; i++) {
                var c = customers[i];
                var statusClass = c.status_class || (c.status === 'active' ? 'badge-success' : 'badge-danger');
                var displayStatus = c.display_status || (c.status === 'active' ? 'Active' : 'Inactive');

                html += '<tr>';
                html += '<td class="serial-number">' + (globalSerial++) + '</td>';
                html += '<td><strong>' + escapeHtml(c.name) + '</strong><br><small class="company-name">' + escapeHtml(c.company) + '</small></td>';
                html += '<td>' + (escapeHtml(c.email) || '—') + '</td>';
                html += '<td>' + (escapeHtml(c.phone) || '—') + '</td>';
                html += '<td>' + (c.assigned_to ? '<span class="badge badge-info">' + escapeHtml(c.assigned_employee_name) + '</span>' : '<span class="badge badge-warning">Unassigned</span>') + '</td>';
                html += '<td><span class="badge ' + statusClass + '">' + displayStatus + '</span></td>';
                html += '<td class="action-buttons">';
                html += '<a href="view_customer.php?id=' + c.id + '" class="btn-sm btn-info" title="View Details"><i class="fas fa-eye"></i></a>';
                html += '<a href="edit_customer.php?id=' + c.id + '" class="btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                html += '<button onclick="deleteCustomer(' + c.id + ')" class="btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>';
                html += '</td>';
                html += '</tr>';
            }

            html += '</tbody>';
            html += '</table>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
        }

        container.innerHTML = html;

        // Attach toggle listeners
        var headers = document.querySelectorAll('.date-header');
        for (var h = 0; h < headers.length; h++) {
            headers[h].addEventListener('click', function (e) {
                e.stopPropagation();
                var dateGroup = this.closest('.date-group');
                var content = dateGroup.querySelector('.date-content');
                var icon = this.querySelector('i');
                content.classList.toggle('hide');
                this.classList.toggle('collapsed');
                if (icon) {
                    if (this.classList.contains('collapsed')) {
                        icon.style.transform = 'rotate(-90deg)';
                    } else {
                        icon.style.transform = 'rotate(0deg)';
                    }
                }
            });
        }
    }

    function updateStats(allCustomers, filteredCustomers) {
        var total = allCustomers.length;
        var assigned = 0;
        var active = 0;

        for (var i = 0; i < allCustomers.length; i++) {
            if (allCustomers[i].assigned_to) { assigned++; }
            if (allCustomers[i].status === 'active') { active++; }
        }
        var unassigned = total - assigned;
        var inactive = total - active;

        document.getElementById('totalCount').innerHTML = total;
        document.getElementById('assignedCount').innerHTML = assigned;
        document.getElementById('unassignedCount').innerHTML = unassigned;
        document.getElementById('activeCount').innerHTML = active;
        document.getElementById('inactiveCount').innerHTML = inactive;
    }

    function deleteCustomer(id) {
        if (confirm('⚠️ Are you sure you want to delete this customer?\n\nThis action cannot be undone.')) {
            fetch(BASE_URL + 'api/customers.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        showToast('✅ Customer deleted successfully', 'success');
                        loadAndFilterCustomers();
                    } else {
                        showToast('❌ Error: ' + data.message, 'error');
                    }
                })
                .catch(function (error) {
                    showToast('❌ Network error. Please try again.', 'error');
                });
        }
    }

    function exportToExcel() {
        var assignment = document.getElementById('filterAssignment').value;
        var status = document.getElementById('filterStatus').value;
        var employee = document.getElementById('filterEmployee').value;
        var dateFrom = document.getElementById('filterDateFrom').value;
        var dateTo = document.getElementById('filterDateTo').value;
        var search = document.getElementById('filterSearch').value;
        var sort = document.getElementById('filterSort').value;

        var url = BASE_URL + 'api/export_customers_filtered.php?';
        var params = [];
        if (assignment !== 'all') { params.push('assignment=' + assignment); }
        if (status !== 'all') { params.push('status=' + status); }
        if (employee !== 'all') { params.push('employee=' + employee); }
        if (dateFrom) { params.push('date_from=' + dateFrom); }
        if (dateTo) { params.push('date_to=' + dateTo); }
        if (search) { params.push('search=' + encodeURIComponent(search)); }
        if (sort) { params.push('sort=' + sort); }

        window.location.href = url + params.join('&');
    }

    function showToast(message, type) {
        var existingToast = document.querySelector('.toast-notification');
        if (existingToast) { existingToast.remove(); }

        var toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';
        toast.innerHTML = message;
        document.body.appendChild(toast);

        setTimeout(function () {
            toast.remove();
        }, 3000);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>]/g, function (m) {
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

    // Load on page load
    document.addEventListener('DOMContentLoaded', function () {
        loadAndFilterCustomers();
    });
</script>

<?php include_once '../../components/footer.php'; ?>