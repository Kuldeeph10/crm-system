<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$page_title = 'Employee Management';
$page_css = 'employees.css';

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<div class="wrapper">
    <?php include_once '../../components/sidebar_admin.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-user-tie"></i> Employee Management</h1>
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
                <a href="add_employee.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Employee
                </a>
            </div>
        </div>
        
        <!-- Filters Card -->
        <div class="filters-card">
            <h3><i class="fas fa-filter"></i> Filter Employees</h3>
            <div class="filters-row">
                <div class="filter-group">
                    <label>Status</label>
                    <select id="filterStatus" class="form-control">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
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
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" id="filterSearch" class="form-control" placeholder="Name, email, phone...">
                </div>
                <div class="filter-group filter-actions">
                    <label>&nbsp;</label>
                    <div class="action-buttons-group">
                        <button onclick="applyFilters()" class="btn btn-primary">Apply Filters</button>
                        <button onclick="resetFilters()" class="btn btn-secondary">Reset</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Summary Stats -->
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-value" id="totalCount">0</div>
                <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="activeCount">0</div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="inactiveCount">0</div>
                <div class="stat-label">Inactive</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="assignedCount">0</div>
                <div class="stat-label">Has Customers</div>
            </div>
        </div>
        
        <!-- Employees Container -->
        <div id="employeesContainer">
            <div class="loading-text">Loading employees...</div>
        </div>
    </div>
</div>

<script>
var BASE_URL = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/crm_system/';
var allEmployees = [];
var currentView = 'datewise';

function showLoading() {
    document.getElementById('employeesContainer').innerHTML = '<div class="loading-text">Loading employees...</div>';
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
    
    loadAndFilterEmployees();
}

function applyFilters() {
    loadAndFilterEmployees();
}

function resetFilters() {
    document.getElementById('filterStatus').value = 'all';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterSearch').value = '';
    loadAndFilterEmployees();
}

function checkIfFiltersApplied() {
    var status = document.getElementById('filterStatus').value;
    var dateFrom = document.getElementById('filterDateFrom').value;
    var dateTo = document.getElementById('filterDateTo').value;
    var search = document.getElementById('filterSearch').value;
    
    return (status !== 'all' || dateFrom !== '' || dateTo !== '' || search !== '');
}

function loadAndFilterEmployees() {
    showLoading();
    
    var hasFilters = checkIfFiltersApplied();
    
    fetch(BASE_URL + 'api/employees.php?action=list')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                var employees = data.data;
                allEmployees = employees;
                var filtered = filterEmployees(employees);
                
                if (hasFilters) {
                    displayFlatList(filtered);
                } else if (currentView === 'simple') {
                    displaySimpleList(filtered);
                } else {
                    var grouped = groupByDate(filtered);
                    displayGroupedList(grouped);
                }
                
                updateStats(employees, filtered);
            } else {
                document.getElementById('employeesContainer').innerHTML = '<div class="empty-state"><i class="fas fa-user-tie"></i><p>No employees found</p><a href="add_employee.php" class="btn btn-primary">Add Your First Employee</a></div>';
            }
        })
        .catch(function(error) {
            document.getElementById('employeesContainer').innerHTML = '<div class="empty-state">Error loading employees</div>';
        });
}

function filterEmployees(employees) {
    var statusFilter = document.getElementById('filterStatus').value;
    var dateFrom = document.getElementById('filterDateFrom').value;
    var dateTo = document.getElementById('filterDateTo').value;
    var searchTerm = document.getElementById('filterSearch').value.toLowerCase();
    
    var result = [];
    for (var i = 0; i < employees.length; i++) {
        var emp = employees[i];
        var match = true;
        
        if (statusFilter !== 'all' && emp.status !== statusFilter) match = false;
        
        if (dateFrom && emp.created_at && emp.created_at.split(' ')[0] < dateFrom) match = false;
        if (dateTo && emp.created_at && emp.created_at.split(' ')[0] > dateTo) match = false;
        
        if (searchTerm && match) {
            var matchName = emp.name.toLowerCase().indexOf(searchTerm) !== -1;
            var matchEmail = (emp.email || '').toLowerCase().indexOf(searchTerm) !== -1;
            var matchPhone = (emp.phone || '').toLowerCase().indexOf(searchTerm) !== -1;
            if (!matchName && !matchEmail && !matchPhone) match = false;
        }
        
        if (match) result.push(emp);
    }
    return result;
}

function groupByDate(employees) {
    var grouped = {};
    for (var i = 0; i < employees.length; i++) {
        var emp = employees[i];
        var date = emp.created_at ? emp.created_at.split(' ')[0] : 'Unknown';
        if (!grouped[date]) grouped[date] = [];
        grouped[date].push(emp);
    }
    var sortedDates = Object.keys(grouped).sort(function(a, b) { return new Date(b) - new Date(a); });
    var sortedGrouped = {};
    for (var d = 0; d < sortedDates.length; d++) {
        sortedGrouped[sortedDates[d]] = grouped[sortedDates[d]];
    }
    return sortedGrouped;
}

function displaySimpleList(employees) {
    var container = document.getElementById('employeesContainer');
    
    if (employees.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-user-tie"></i><p>No employees found</p><button onclick="resetFilters()" class="btn btn-primary mt-3">Reset Filters</button></div>';
        return;
    }
    
    var html = '<div class="simple-list"><div class="simple-header"><h3><i class="fas fa-list"></i> Employee List (' + employees.length + ')</h3></div><div class="table-responsive"><table class="employees-table"><thead>';
    html += '<tr><th width="50">#</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Created Date</th><th width="160">Actions</th></tr>';
    html += '</thead><tbody>';
    
    for (var i = 0; i < employees.length; i++) {
        var emp = employees[i];
        html += '<tr>';
        html += '<td class="serial-number">' + (i + 1) + '</td>';
        html += '<td><strong>' + escapeHtml(emp.name) + '</strong></td>';
        html += '<td>' + (escapeHtml(emp.email) || '—') + '</td>';
        html += '<td>' + (escapeHtml(emp.phone) || '—') + '</td>';
        html += '<td><span class="status-badge status-' + emp.status + '">' + (emp.status === 'active' ? 'Active' : 'Inactive') + '</span></td>';
        html += '<td>' + formatDate(emp.created_at) + '</td>';
        html += '<td class="action-buttons">';
        html += '<a href="view_employee.php?id=' + emp.id + '" class="btn-sm btn-view"><i class="fas fa-eye"></i> View</a>';
        html += '<a href="edit_employee.php?id=' + emp.id + '" class="btn-sm btn-edit"><i class="fas fa-edit"></i> Edit</a>';
        html += '<button onclick="changePassword(' + emp.id + ', \'' + escapeHtml(emp.name) + '\', event)" class="btn-sm btn-password"><i class="fas fa-key"></i> Password</button>';
        html += '<button onclick="deleteEmployee(' + emp.id + ', \'' + escapeHtml(emp.name) + '\')" class="btn-sm btn-delete"><i class="fas fa-trash"></i> Delete</button>';
        html += '</td>';
        html += '</tr>';
    }
    
    html += '</tbody></table></div></div>';
    container.innerHTML = html;
}

function displayFlatList(employees) {
    var container = document.getElementById('employeesContainer');
    
    if (employees.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-user-tie"></i><p>No employees match your filters</p><button onclick="resetFilters()" class="btn btn-primary mt-3">Reset Filters</button></div>';
        return;
    }
    
    var html = '<div class="flat-list"><div class="flat-header"><h3><i class="fas fa-list"></i> Filtered Results (' + employees.length + ' employees)</h3><button onclick="resetFilters()" class="btn btn-sm btn-secondary">Clear Filters</button></div><div class="table-responsive"><table class="employees-table"><thead>';
    html += '<tr><th width="50">#</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Created Date</th><th width="160">Actions</th></tr>';
    html += '</thead><tbody>';
    
    for (var i = 0; i < employees.length; i++) {
        var emp = employees[i];
        html += '<tr>';
        html += '<td class="serial-number">' + (i + 1) + '</td>';
        html += '<td><strong>' + escapeHtml(emp.name) + '</strong></td>';
        html += '<td>' + (escapeHtml(emp.email) || '—') + '</td>';
        html += '<td>' + (escapeHtml(emp.phone) || '—') + '</td>';
        html += '<td><span class="status-badge status-' + emp.status + '">' + (emp.status === 'active' ? 'Active' : 'Inactive') + '</span></td>';
        html += '<td>' + formatDate(emp.created_at) + '</td>';
        html += '<td class="action-buttons">';
        html += '<a href="view_employee.php?id=' + emp.id + '" class="btn-sm btn-view"><i class="fas fa-eye"></i> View</a>';
        html += '<a href="edit_employee.php?id=' + emp.id + '" class="btn-sm btn-edit"><i class="fas fa-edit"></i> Edit</a>';
        html += '<button onclick="changePassword(' + emp.id + ', \'' + escapeHtml(emp.name) + '\', event)" class="btn-sm btn-password"><i class="fas fa-key"></i> Password</button>';
        html += '<button onclick="deleteEmployee(' + emp.id + ', \'' + escapeHtml(emp.name) + '\')" class="btn-sm btn-delete"><i class="fas fa-trash"></i> Delete</button>';
        html += '</td>';
        html += '</tr>';
    }
    
    html += '</tbody></table></div></div>';
    container.innerHTML = html;
}

function displayGroupedList(grouped) {
    var container = document.getElementById('employeesContainer');
    var dates = Object.keys(grouped);
    
    if (dates.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-user-tie"></i><p>No employees found</p></div>';
        return;
    }
    
    var html = '';
    var globalSerial = 1;
    
    for (var d = 0; d < dates.length; d++) {
        var date = dates[d];
        var employees = grouped[date];
        
        html += '<div class="date-group">';
        html += '<div class="date-header">';
        html += '<i class="fas fa-chevron-down"></i>';
        html += '<strong>📅 Joined: ' + formatDate(date) + '</strong>';
        html += '<span class="employee-count">' + employees.length + ' employees</span>';
        html += '</div>';
        
        html += '<div class="date-content">';
        html += '<div class="table-responsive">';
        html += '<table class="employees-table">';
        html += '<thead>';
        html += '<tr>';
        html += '<th width="50">#</th>';
        html += '<th>Name</th>';
        html += '<th>Email</th>';
        html += '<th>Phone</th>';
        html += '<th>Status</th>';
        html += '<th width="160">Actions</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';
        
        for (var i = 0; i < employees.length; i++) {
            var emp = employees[i];
            html += '<tr>';
            html += '<td class="serial-number">' + (globalSerial++) + '</td>';
            html += '<td><strong>' + escapeHtml(emp.name) + '</strong></td>';
            html += '<td>' + (escapeHtml(emp.email) || '—') + '</td>';
            html += '<td>' + (escapeHtml(emp.phone) || '—') + '</td>';
            html += '<td><span class="status-badge status-' + emp.status + '">' + (emp.status === 'active' ? 'Active' : 'Inactive') + '</span></td>';
            html += '<td class="action-buttons">';
            html += '<a href="view_employee.php?id=' + emp.id + '" class="btn-sm btn-view"><i class="fas fa-eye"></i> View</a>';
            html += '<a href="edit_employee.php?id=' + emp.id + '" class="btn-sm btn-edit"><i class="fas fa-edit"></i> Edit</a>';
            html += '<button onclick="changePassword(' + emp.id + ', \'' + escapeHtml(emp.name) + '\', event)" class="btn-sm btn-password"><i class="fas fa-key"></i> Password</button>';
            html += '<button onclick="deleteEmployee(' + emp.id + ', \'' + escapeHtml(emp.name) + '\')" class="btn-sm btn-delete"><i class="fas fa-trash"></i> Delete</button>';
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
    
    var headers = document.querySelectorAll('.date-header');
    for (var h = 0; h < headers.length; h++) {
        headers[h].addEventListener('click', function(e) {
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

function updateStats(allEmployees, filteredEmployees) {
    var total = allEmployees.length;
    var active = 0;
    var inactive = 0;
    var hasCustomers = 0;
    
    for (var i = 0; i < allEmployees.length; i++) {
        if (allEmployees[i].status === 'active') active++;
        else inactive++;
        // Count employees with assigned customers (to be implemented)
    }
    
    document.getElementById('totalCount').innerHTML = total;
    document.getElementById('activeCount').innerHTML = active;
    document.getElementById('inactiveCount').innerHTML = inactive;
    document.getElementById('assignedCount').innerHTML = hasCustomers;
}

function changePassword(id, name, event) {
    var newPassword = prompt('Enter new password for ' + name + ':\n\nPassword must be at least 4 characters');
    
    if (newPassword === null) return;
    if (newPassword.length < 4) {
        showToast('Password must be at least 4 characters', 'error');
        return;
    }
    
    var btn = event.target;
    if (btn.tagName !== 'BUTTON') btn = btn.closest('button');
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(BASE_URL + 'api/employees.php?action=change_password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&password=' + encodeURIComponent(newPassword)
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            showToast('Password updated successfully for ' + name, 'success');
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(function(error) {
        showToast('Network error. Please try again.', 'error');
    })
    .finally(function() {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function deleteEmployee(id, name) {
    if (confirm('Are you sure you want to delete employee "' + name + '"?\n\nThis action cannot be undone.')) {
        fetch(BASE_URL + 'api/employees.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                showToast('Employee deleted successfully', 'success');
                loadAndFilterEmployees();
            } else {
                showToast('Error: ' + data.message, 'error');
            }
        })
        .catch(function(error) {
            showToast('Network error. Please try again.', 'error');
        });
    }
}

function exportToExcel() {
    window.location.href = BASE_URL + 'api/export_employees.php';
}

function showToast(message, type) {
    var existingToast = document.querySelector('.toast-notification');
    if (existingToast) existingToast.remove();
    
    var toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';
    toast.innerHTML = message;
    document.body.appendChild(toast);
    
    setTimeout(function() { toast.remove(); }, 3000);
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
    if (!dateString) return '-';
    var date = new Date(dateString);
    return date.toLocaleDateString('en-IN', { year: 'numeric', month: 'long', day: 'numeric' });
}

// Load on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAndFilterEmployees();
});
</script>

<?php include_once '../../components/footer.php'; ?>