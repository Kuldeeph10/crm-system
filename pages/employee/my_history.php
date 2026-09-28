<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is employee
if (!isLoggedIn() || !hasRole('employee')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$page_title = 'My Call History';
$page_css = 'call_history.css';

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<div class="wrapper">
    <?php include_once '../../components/sidebar_employee.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-history"></i> My Call History</h1>
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
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="filters-card">
            <h3><i class="fas fa-filter"></i> Filter Call Records</h3>
            <div class="filters-row">
                <div class="filter-group">
                    <label>Interest Level</label>
                    <select id="filterInterest" class="form-control">
                        <option value="">All</option>
                        <option value="interested">✅ Interested</option>
                        <option value="not_interested">❌ Not Interested</option>
                        <option value="follow_up">🔄 Follow Up</option>
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
                <div class="stat-label">Total Calls</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="interestedCount">0</div>
                <div class="stat-label">✅ Interested</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="notInterestedCount">0</div>
                <div class="stat-label">❌ Not Interested</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="followUpCount">0</div>
                <div class="stat-label">🔄 Follow Up</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="withRecordingCount">0</div>
                <div class="stat-label">📹 With Recording</div>
            </div>
        </div>

        <!-- Records Container -->
        <div id="recordsContainer">
            <div class="loading-text">Loading call history...</div>
        </div>
    </div>
</div>

<script>
    var BASE_URL = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/crm_system/';
    var allRecords = [];
    var currentView = 'datewise';

    function showLoading() {
        document.getElementById('recordsContainer').innerHTML = '<div class="loading-text">Loading call history...</div>';
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

        loadAndFilterRecords();
    }

    function applyFilters() {
        loadAndFilterRecords();
    }

    function resetFilters() {
        document.getElementById('filterInterest').value = '';
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value = '';
        document.getElementById('filterSearch').value = '';
        loadAndFilterRecords();
    }

    function checkIfFiltersApplied() {
        var interest = document.getElementById('filterInterest').value;
        var dateFrom = document.getElementById('filterDateFrom').value;
        var dateTo = document.getElementById('filterDateTo').value;
        var search = document.getElementById('filterSearch').value;

        return (interest !== '' || dateFrom !== '' || dateTo !== '' || search !== '');
    }

    function loadAndFilterRecords() {
        showLoading();

        var hasFilters = checkIfFiltersApplied();

        fetch(BASE_URL + 'api/call_records.php?action=my_records')
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success && data.data) {
                    allRecords = data.data;
                    var filtered = filterRecords(allRecords);
                    updateStats(filtered);

                    if (hasFilters || currentView === 'simple') {
                        displayFlatList(filtered);
                    } else {
                        var grouped = groupByDate(filtered);
                        displayGroupedList(grouped);
                    }
                } else {
                    document.getElementById('recordsContainer').innerHTML = '<div class="empty-state"><i class="fas fa-phone-slash"></i><p>No call history found</p><p class="text-muted">You haven\'t made any calls yet.</p><a href="my_customers.php" class="btn btn-primary mt-3">Start Calling</a></div>';
                    updateStats([]);
                }
            })
            .catch(function (error) {
                document.getElementById('recordsContainer').innerHTML = '<div class="empty-state">Error loading call history</div>';
            });
    }

    function filterRecords(records) {
        var interestFilter = document.getElementById('filterInterest').value;
        var dateFrom = document.getElementById('filterDateFrom').value;
        var dateTo = document.getElementById('filterDateTo').value;
        var searchTerm = document.getElementById('filterSearch').value.toLowerCase();

        var result = [];
        for (var i = 0; i < records.length; i++) {
            var r = records[i];
            var match = true;

            if (interestFilter && r.interest_level !== interestFilter) match = false;

            if (dateFrom && r.called_at && r.called_at.split(' ')[0] < dateFrom) match = false;
            if (dateTo && r.called_at && r.called_at.split(' ')[0] > dateTo) match = false;

            if (searchTerm && match) {
                var matchName = (r.user_name || '').toLowerCase().indexOf(searchTerm) !== -1;
                var matchNotes = (r.notes || '').toLowerCase().indexOf(searchTerm) !== -1;
                if (!matchName && !matchNotes) match = false;
            }

            if (match) result.push(r);
        }
        return result;
    }

    function groupByDate(records) {
        var grouped = {};
        for (var i = 0; i < records.length; i++) {
            var record = records[i];
            var date = record.called_at ? record.called_at.split(' ')[0] : 'Unknown';
            if (!grouped[date]) grouped[date] = [];
            grouped[date].push(record);
        }
        var sortedDates = Object.keys(grouped).sort(function (a, b) { return new Date(b) - new Date(a); });
        var sortedGrouped = {};
        for (var d = 0; d < sortedDates.length; d++) {
            sortedGrouped[sortedDates[d]] = grouped[sortedDates[d]];
        }
        return sortedGrouped;
    }

    function displayFlatList(records) {
        var container = document.getElementById('recordsContainer');

        if (records.length === 0) {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-phone-slash"></i><p>No call records match your filters</p><button onclick="resetFilters()" class="btn btn-primary mt-3">Reset Filters</button></div>';
            return;
        }

        var html = '<div class="flat-list"><div class="flat-header"><h3><i class="fas fa-list"></i> Call History (' + records.length + ')</h3><button onclick="resetFilters()" class="btn btn-sm btn-secondary">Clear Filters</button></div><div class="table-responsive"><table class="history-table"><thead>';
        html += '<tr>';
        html += '<th width="50">#</th>';
        html += '<th>Customer</th>';
        html += '<th>Phone</th>';
        html += '<th>Notes</th>';
        html += '<th>Interest</th>';
        html += '<th>Follow-up Date</th>';
        html += '<th>Recording</th>';
        html += '<th>Called Date</th>';
        html += '<th width="80">Action</th>';  // ← ADDED Action column
        html += '</tr>';
        html += '</thead><tbody>';

        for (var i = 0; i < records.length; i++) {
            var r = records[i];
            var interestClass = r.interest_level === 'interested' ? 'interest-interested' :
                (r.interest_level === 'not_interested' ? 'interest-not_interested' : 'interest-follow_up');
            var interestText = r.interest_level === 'interested' ? '✅ Interested' :
                (r.interest_level === 'not_interested' ? '❌ Not Interested' : '🔄 Follow Up');
            var followUpDate = (r.follow_up_date && r.follow_up_date !== '0000-00-00') ? formatDateOnly(r.follow_up_date) : '—';

            html += '<tr>';
            html += '<td class="serial-number">' + (i + 1) + '</td>';
            html += '<td><strong>' + escapeHtml(r.user_name) + '</strong></td>';
            html += '<td>' + (escapeHtml(r.phone) || '—') + '</td>';
            html += '<td class="notes-cell">' + escapeHtml(r.notes) + '</td>';
            html += '<td><span class="interest-badge ' + interestClass + '">' + interestText + '</span></td>';
            html += '<td>' + followUpDate + '</td>';
            html += '<td>' + (r.recording_file ? '<a href="' + BASE_URL + 'uploads/recordings/' + r.recording_file + '" class="recording-link" target="_blank"><i class="fas fa-download"></i> Download</a>' : '—') + '</td>';
            html += '<td>' + formatDateTime(r.called_at) + '</td>';
            html += '<td class="action-buttons">';
            html += '<a href="' + BASE_URL + 'pages/employee/view_call_record.php?id=' + r.id + '" class="btn-sm btn-info" title="View Details"><i class="fas fa-eye"></i> View</a>';
            html += '</td>';
            html += '</tr>';
        }

        html += '</tbody></table></div></div>';
        container.innerHTML = html;
    }

    function displayGroupedList(grouped) {
        var container = document.getElementById('recordsContainer');
        var dates = Object.keys(grouped);

        if (dates.length === 0) {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-phone-slash"></i><p>No call history found</p></div>';
            return;
        }

        var html = '';
        var globalSerial = 1;

        for (var d = 0; d < dates.length; d++) {
            var date = dates[d];
            var records = grouped[date];

            html += '<div class="date-group">';
            html += '<div class="date-header">';
            html += '<i class="fas fa-chevron-down"></i>';
            html += '<strong>📅 ' + formatDateOnly(date) + '</strong>';
            html += '<span class="call-count">' + records.length + ' calls</span>';
            html += '</div>';

            html += '<div class="date-content">';
            html += '<div class="table-responsive">';
            html += '<table class="history-table">';
            html += '<thead>';
            html += '<tr>';
            html += '<th width="50">#</th>';
            html += '<th>Customer</th>';
            html += '<th>Phone</th>';
            html += '<th>Notes</th>';
            html += '<th>Interest</th>';
            html += '<th>Follow-up Date</th>';
            html += '<th>Recording</th>';
            html += '<th>Called Date</th>';
            html += '<th width="80">Action</th>';  // ← ADDED Action column
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            for (var i = 0; i < records.length; i++) {
                var r = records[i];
                var interestClass = r.interest_level === 'interested' ? 'interest-interested' :
                    (r.interest_level === 'not_interested' ? 'interest-not_interested' : 'interest-follow_up');
                var interestText = r.interest_level === 'interested' ? '✅ Interested' :
                    (r.interest_level === 'not_interested' ? '❌ Not Interested' : '🔄 Follow Up');
                var followUpDate = (r.follow_up_date && r.follow_up_date !== '0000-00-00') ? formatDateOnly(r.follow_up_date) : '—';

                html += '<tr>';
                html += '<td class="serial-number">' + (globalSerial++) + '</td>';
                html += '<td><strong>' + escapeHtml(r.user_name) + '</strong></td>';
                html += '<td>' + (escapeHtml(r.phone) || '—') + '</td>';
                html += '<td class="notes-cell">' + escapeHtml(r.notes) + '</td>';
                html += '<td><span class="interest-badge ' + interestClass + '">' + interestText + '</span></td>';
                html += '<td>' + followUpDate + '</td>';
                html += '<td>' + (r.recording_file ? '<a href="' + BASE_URL + 'uploads/recordings/' + r.recording_file + '" class="recording-link" target="_blank"><i class="fas fa-download"></i> Download</a>' : '—') + '</td>';
                html += '<td>' + formatDateTime(r.called_at) + '</td>';
                html += '<td class="action-buttons">';
                html += '<a href="' + BASE_URL + 'pages/employee/view_call_record.php?id=' + r.id + '" class="btn-sm btn-info" title="View Details"><i class="fas fa-eye"></i> View</a>';
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
    function updateStats(records) {
        var total = records.length;
        var interested = 0, notInterested = 0, followUp = 0, withRecording = 0;

        for (var i = 0; i < records.length; i++) {
            if (records[i].interest_level === 'interested') interested++;
            else if (records[i].interest_level === 'not_interested') notInterested++;
            else if (records[i].interest_level === 'follow_up') followUp++;

            if (records[i].recording_file) withRecording++;
        }

        document.getElementById('totalCount').innerHTML = total;
        document.getElementById('interestedCount').innerHTML = interested;
        document.getElementById('notInterestedCount').innerHTML = notInterested;
        document.getElementById('followUpCount').innerHTML = followUp;
        document.getElementById('withRecordingCount').innerHTML = withRecording;
    }

    function exportToExcel() {
        var interest = document.getElementById('filterInterest').value;
        var dateFrom = document.getElementById('filterDateFrom').value;
        var dateTo = document.getElementById('filterDateTo').value;
        var search = document.getElementById('filterSearch').value;

        var url = BASE_URL + 'api/export_employee_history.php?';
        var params = [];
        if (interest) params.push('interest_level=' + interest);
        if (dateFrom) params.push('date_from=' + dateFrom);
        if (dateTo) params.push('date_to=' + dateTo);
        if (search) params.push('search=' + encodeURIComponent(search));

        window.location.href = url + params.join('&');
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

    function formatDateOnly(dateString) {
        if (!dateString) return '—';
        if (dateString === 'Invalid Date') return '—';
        var date = new Date(dateString);
        if (isNaN(date.getTime())) return '—';
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatDateTime(dateString) {
        if (!dateString) return '—';
        var date = new Date(dateString);
        if (isNaN(date.getTime())) return '—';
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' +
            date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    // Load on page load
    document.addEventListener('DOMContentLoaded', function () {
        loadAndFilterRecords();
    });
</script>

<?php include_once '../../components/footer.php'; ?>