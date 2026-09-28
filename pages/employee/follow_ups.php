<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is employee
if (!isLoggedIn() || !hasRole('employee')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$page_title = 'My Follow-ups';
$page_css = 'follow_ups.css';

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<div class="wrapper">
    <?php include_once '../../components/sidebar_employee.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-calendar-alt"></i> My Follow-ups</h1>
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
            </div>
        </div>
        
        <!-- Filters Card -->
        <div class="filters-card">
            <h3><i class="fas fa-filter"></i> Filter Follow-ups</h3>
            <div class="filters-row">
                <div class="filter-group">
                    <label>Status</label>
                    <select id="filterStatus" class="form-control">
                        <option value="all">All Follow-ups</option>
                        <option value="pending">Pending (Today & Future)</option>
                        <option value="overdue">Overdue</option>
                        <option value="today">Today</option>
                        <option value="upcoming">Upcoming</option>
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
                    <input type="text" id="filterSearch" class="form-control" placeholder="Customer name or notes...">
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
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="todayCount">0</div>
                <div class="stat-label">Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="overdueCount">0</div>
                <div class="stat-label">Overdue</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="upcomingCount">0</div>
                <div class="stat-label">Upcoming</div>
            </div>
        </div>
        
        <!-- Follow-ups Container -->
        <div id="followupsContainer">
            <div class="loading-text">Loading follow-ups...</div>
        </div>
    </div>
</div>

<script>
var BASE_URL = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/crm_system/';
var allFollowups = [];
var currentView = 'datewise';

function showLoading() {
    document.getElementById('followupsContainer').innerHTML = '<div class="loading-text">Loading follow-ups...</div>';
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
    
    loadAndFilterFollowups();
}

function applyFilters() {
    loadAndFilterFollowups();
}

function resetFilters() {
    document.getElementById('filterStatus').value = 'all';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterSearch').value = '';
    loadAndFilterFollowups();
}

function checkIfFiltersApplied() {
    var status = document.getElementById('filterStatus').value;
    var dateFrom = document.getElementById('filterDateFrom').value;
    var dateTo = document.getElementById('filterDateTo').value;
    var search = document.getElementById('filterSearch').value;
    
    return (status !== 'all' || dateFrom !== '' || dateTo !== '' || search !== '');
}

function loadAndFilterFollowups() {
    showLoading();
    
    var hasFilters = checkIfFiltersApplied();
    
    fetch(BASE_URL + 'api/followups.php?action=my_followups&status=all')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                allFollowups = data.data;
                var filtered = filterFollowups(allFollowups);
                updateStats(filtered);
                
                if (hasFilters || currentView === 'simple') {
                    displayFlatList(filtered);
                } else {
                    var grouped = groupByDate(filtered);
                    displayGroupedList(grouped);
                }
            } else {
                document.getElementById('followupsContainer').innerHTML = '<div class="empty-state"><i class="fas fa-calendar-check"></i><p>No follow-ups found</p><p class="text-muted">You have no follow-ups scheduled.</p><a href="my_customers.php" class="btn btn-primary mt-3">View My Customers</a></div>';
                updateStats([]);
            }
        })
        .catch(function(error) {
            document.getElementById('followupsContainer').innerHTML = '<div class="empty-state">Error loading follow-ups</div>';
        });
}

function filterFollowups(followups) {
    var statusFilter = document.getElementById('filterStatus').value;
    var dateFrom = document.getElementById('filterDateFrom').value;
    var dateTo = document.getElementById('filterDateTo').value;
    var searchTerm = document.getElementById('filterSearch').value.toLowerCase();
    var today = new Date().toISOString().split('T')[0];
    
    var result = [];
    for (var i = 0; i < followups.length; i++) {
        var f = followups[i];
        var match = true;
        
        if (statusFilter !== 'all') {
            if (statusFilter === 'pending' && f.follow_up_date < today) match = false;
            else if (statusFilter === 'overdue' && f.follow_up_date >= today) match = false;
            else if (statusFilter === 'today' && f.follow_up_date !== today) match = false;
            else if (statusFilter === 'upcoming' && f.follow_up_date <= today) match = false;
        }
        
        if (dateFrom && f.follow_up_date < dateFrom) match = false;
        if (dateTo && f.follow_up_date > dateTo) match = false;
        
        if (searchTerm && match) {
            var matchName = (f.user_name || '').toLowerCase().indexOf(searchTerm) !== -1;
            var matchNotes = (f.notes || '').toLowerCase().indexOf(searchTerm) !== -1;
            if (!matchName && !matchNotes) match = false;
        }
        
        if (match) result.push(f);
    }
    return result;
}

function groupByDate(followups) {
    var grouped = {};
    for (var i = 0; i < followups.length; i++) {
        var f = followups[i];
        var date = f.follow_up_date;
        if (!grouped[date]) grouped[date] = [];
        grouped[date].push(f);
    }
    var sortedDates = Object.keys(grouped).sort(function(a, b) { return a.localeCompare(b); });
    var sortedGrouped = {};
    for (var d = 0; d < sortedDates.length; d++) {
        sortedGrouped[sortedDates[d]] = grouped[sortedDates[d]];
    }
    return sortedGrouped;
}

function displayFlatList(followups) {
    var container = document.getElementById('followupsContainer');
    
    if (followups.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-search"></i><p>No follow-ups match your filters</p><button onclick="resetFilters()" class="btn btn-primary mt-3">Reset Filters</button></div>';
        return;
    }
    
    var today = new Date().toISOString().split('T')[0];
    
    var html = '<div class="flat-list"><div class="flat-header"><h3><i class="fas fa-list"></i> My Follow-ups (' + followups.length + ')</h3><button onclick="resetFilters()" class="btn btn-sm btn-secondary">Clear Filters</button></div><div class="table-responsive"><table class="followup-table"><thead>';
    html += '<tr>';
    html += '<th width="50">#</th>';
    html += '<th>Customer</th>';
    html += '<th>Phone</th>';
    html += '<th>Follow-up Date</th>';
    html += '<th>Status</th>';
    html += '<th>Notes</th>';
    html += '<th width="100">Action</th>';
    html += '</tr>';
    html += '</thead><tbody>';
    
    for (var i = 0; i < followups.length; i++) {
        var f = followups[i];
        var followDate = f.follow_up_date;
        var isToday = followDate === today;
        var isOverdue = followDate < today;
        
        var dateClass = '';
        var statusHtml = '';
        
        if (isOverdue) {
            dateClass = 'overdue-date';
            statusHtml = '<span class="status-badge status-overdue">⚠️ OVERDUE</span>';
        } else if (isToday) {
            dateClass = 'today-date';
            statusHtml = '<span class="status-badge status-today">🔴 TODAY</span>';
        } else {
            dateClass = 'upcoming-date';
            statusHtml = '<span class="status-badge status-upcoming">📅 Upcoming</span>';
        }
        
        html += '<tr>';
        html += '<td class="serial-number">' + (i + 1) + '</td>';
        html += '<td><strong>' + escapeHtml(f.user_name) + '</strong></td>';
        html += '<td>' + (escapeHtml(f.phone) || '—') + '</td>';
        html += '<td class="' + dateClass + '"><strong>' + formatDateOnly(f.follow_up_date) + '</strong></td>';
        html += '<td>' + statusHtml + '</td>';
        html += '<td class="notes-cell">' + escapeHtml(f.notes) + '</td>';
        html += '<td class="action-buttons"><a href="call_customer.php?id=' + f.user_id + '&followup_id=' + f.id + '" class="btn-sm btn-warning"><i class="fas fa-phone"></i> Call Again</a></td>';
        html += '</tr>';
    }
    
    html += '</tbody></table></div></div>';
    container.innerHTML = html;
}

function displayGroupedList(grouped) {
    var container = document.getElementById('followupsContainer');
    var dates = Object.keys(grouped);
    
    if (dates.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-calendar-check"></i><p>No follow-ups found</p></div>';
        return;
    }
    
    var today = new Date().toISOString().split('T')[0];
    var html = '';
    var globalSerial = 1;
    
    for (var d = 0; d < dates.length; d++) {
        var date = dates[d];
        var followups = grouped[date];
        
        html += '<div class="date-group">';
        html += '<div class="date-header">';
        html += '<i class="fas fa-chevron-down"></i>';
        html += '<strong>📅 ' + formatDateOnly(date) + '</strong>';
        html += '<span class="followup-count">' + followups.length + ' follow-ups</span>';
        html += '</div>';
        
        html += '<div class="date-content">';
        html += '<div class="table-responsive">';
        html += '<table class="followup-table">';
        html += '<thead>';
        html += '<tr>';
        html += '<th width="50">#</th>';
        html += '<th>Customer</th>';
        html += '<th>Phone</th>';
        html += '<th>Status</th>';
        html += '<th>Notes</th>';
        html += '<th width="100">Action</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';
        
        for (var i = 0; i < followups.length; i++) {
            var f = followups[i];
            var followDate = f.follow_up_date;
            var isToday = followDate === today;
            var isOverdue = followDate < today;
            
            var statusHtml = '';
            if (isOverdue) {
                statusHtml = '<span class="status-badge status-overdue">⚠️ OVERDUE</span>';
            } else if (isToday) {
                statusHtml = '<span class="status-badge status-today">🔴 TODAY</span>';
            } else {
                statusHtml = '<span class="status-badge status-upcoming">📅 Upcoming</span>';
            }
            
            html += '<tr>';
            html += '<td class="serial-number">' + (globalSerial++) + '</td>';
            html += '<td><strong>' + escapeHtml(f.user_name) + '</strong></td>';
            html += '<td>' + (escapeHtml(f.phone) || '—') + '</td>';
            html += '<td>' + statusHtml + '</td>';
            html += '<td class="notes-cell">' + escapeHtml(f.notes) + '</td>';
            html += '<td class="action-buttons"><a href="call_customer.php?id=' + f.user_id + '&followup_id=' + f.id + '" class="btn-sm btn-warning"><i class="fas fa-phone"></i> Call Again</a></td>';
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

function updateStats(followups) {
    var today = new Date().toISOString().split('T')[0];
    var total = followups.length;
    var todayCount = 0, overdueCount = 0, upcomingCount = 0;
    
    for (var i = 0; i < followups.length; i++) {
        var date = followups[i].follow_up_date;
        if (date === today) todayCount++;
        else if (date < today) overdueCount++;
        else if (date > today) upcomingCount++;
    }
    
    document.getElementById('totalCount').innerHTML = total;
    document.getElementById('todayCount').innerHTML = todayCount;
    document.getElementById('overdueCount').innerHTML = overdueCount;
    document.getElementById('upcomingCount').innerHTML = upcomingCount;
}

function exportToExcel() {
    var status = document.getElementById('filterStatus').value;
    var dateFrom = document.getElementById('filterDateFrom').value;
    var dateTo = document.getElementById('filterDateTo').value;
    var search = document.getElementById('filterSearch').value;
    
    var url = BASE_URL + 'api/export_followups.php?type=employee';
    if (status !== 'all') url += '&status=' + status;
    if (dateFrom) url += '&date_from=' + dateFrom;
    if (dateTo) url += '&date_to=' + dateTo;
    if (search) url += '&search=' + encodeURIComponent(search);
    
    window.location.href = url;
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

function formatDateOnly(dateString) {
    if (!dateString) return '—';
    var date = new Date(dateString);
    if (isNaN(date.getTime())) return '—';
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

// Load on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAndFilterFollowups();
});
</script>

<?php include_once '../../components/footer.php'; ?>