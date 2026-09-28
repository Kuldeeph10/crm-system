<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$page_title = 'Assign Customers to Employee';

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<div class="wrapper">
    <?php include_once '../../components/sidebar_admin.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-user-plus"></i> Assign Customers to Employee</h1>
            <div class="header-actions">
                <a href="customers.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Customers
                </a>
            </div>
        </div>
        
        <div class="assign-container">
            <!-- Left Panel: Select Employee & Unassigned Customers -->
            <div class="assign-panel">
                <div class="panel-header">
                    <h3><i class="fas fa-user-tie"></i> Step 1: Select Employee</h3>
                </div>
                <div class="panel-body">
                    <select id="employeeSelect" class="form-control" onchange="loadUnassignedCustomers()">
                        <option value="">-- Select Employee --</option>
                    </select>
                </div>
                
                <div class="panel-header mt-4">
                    <h3><i class="fas fa-users"></i> Step 2: Select Customers to Assign</h3>
                </div>
                <div class="panel-body">
                    <div class="search-box with-count">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchUnassigned" placeholder="Search customers..." onkeyup="filterUnassignedList(this.value)">
                        <span class="count-badge" id="unassignedCount">0</span>
                    </div>
                    <div id="unassignedList" class="customer-list">
                        <div class="loading-small">Select an employee first</div>
                    </div>
                    <div class="selection-actions">
                        <button onclick="selectAll()" class="btn btn-sm btn-secondary">Select All</button>
                        <button onclick="deselectAll()" class="btn btn-sm btn-secondary">Deselect All</button>
                    </div>
                    <button id="assignBtn" class="btn btn-primary btn-block" onclick="assignCustomers()">
                        <i class="fas fa-user-plus"></i> Assign Selected Customers
                    </button>
                </div>
            </div>
            
            <!-- Right Panel: Currently Assigned Customers -->
            <div class="assign-panel">
                <div class="panel-header">
                    <h3><i class="fas fa-clipboard-list"></i> Currently Assigned Customers</h3>
                </div>
                <div class="panel-body">
                    <div class="search-box with-count">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchAssigned" placeholder="Search assigned customers..." onkeyup="filterAssignedList(this.value)">
                        <span class="count-badge" id="assignedCount">0</span>
                    </div>
                    <div id="assignedList" class="customer-list">
                        <div class="loading-small">Select an employee to view assigned customers</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.assign-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

.assign-panel {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.panel-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
}

.panel-header h3 {
    margin: 0;
    font-size: 16px;
    color: #333;
}

.panel-header h3 i {
    margin-right: 8px;
    color: #3498db;
}

.panel-body {
    padding: 20px;
}

.search-box {
    position: relative;
    margin-bottom: 15px;
}

.search-box.with-count {
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-box.with-count i {
    color: #999;
    font-size: 14px;
}

.search-box.with-count input {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
}

.search-box.with-count input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
}

.count-badge {
    background: #3498db;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
    min-width: 55px;
    text-align: center;
}

.count-badge.total {
    background: #2c3e50;
}

.count-badge.filtered {
    background: #e67e22;
}

.customer-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 15px;
}

.customer-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: background 0.3s;
}

.customer-item:hover {
    background: #f8f9fa;
}

.customer-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.customer-info {
    flex: 1;
}

.customer-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.customer-details {
    font-size: 12px;
    color: #666;
}

.customer-details i {
    margin-right: 5px;
    width: 14px;
}

.unassign-btn {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    transition: background 0.3s;
}

.unassign-btn:hover {
    background: #c0392b;
}

.loading-small {
    text-align: center;
    padding: 40px;
    color: #999;
}

.selection-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.btn-block {
    width: 100%;
    margin-top: 10px;
}

.btn-sm {
    padding: 6px 14px;
    font-size: 12px;
    border-radius: 6px;
    cursor: pointer;
}

.mt-4 {
    margin-top: 20px;
}

.empty-message {
    text-align: center;
    padding: 40px;
    color: #999;
}

.empty-message i {
    font-size: 48px;
    margin-bottom: 10px;
    display: block;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
}

@media (max-width: 992px) {
    .assign-container {
        grid-template-columns: 1fr;
        gap: 20px;
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
    font-size: 14px;
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
const BASE_URL = '<?php echo BASE_URL; ?>';
let currentEmployeeId = null;
let unassignedCustomers = [];
let assignedCustomers = [];

// Load employees on page load
document.addEventListener('DOMContentLoaded', function() {
    loadEmployees();
});

function loadEmployees() {
    fetch(BASE_URL + 'api/assignments.php?action=employees')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                const select = document.getElementById('employeeSelect');
                select.innerHTML = '<option value="">-- Select Employee --</option>';
                
                data.data.forEach(emp => {
                    select.innerHTML += `<option value="${emp.id}">${escapeHtml(emp.name)} (${emp.email})</option>`;
                });
            } else {
                document.getElementById('unassignedList').innerHTML = '<div class="empty-message"><i class="fas fa-user-tie"></i>No employees found. <a href="add_employee.php">Add an employee</a> first.</div>';
            }
        });
}

function loadUnassignedCustomers() {
    const employeeId = document.getElementById('employeeSelect').value;
    if (!employeeId) {
        document.getElementById('unassignedList').innerHTML = '<div class="loading-small">Select an employee first</div>';
        document.getElementById('assignedList').innerHTML = '<div class="loading-small">Select an employee to view assigned customers</div>';
        document.getElementById('unassignedCount').innerHTML = '0';
        document.getElementById('assignedCount').innerHTML = '0';
        return;
    }
    
    currentEmployeeId = employeeId;
    
    // Clear search boxes
    document.getElementById('searchUnassigned').value = '';
    document.getElementById('searchAssigned').value = '';
    
    // Load unassigned customers
    fetch(BASE_URL + 'api/assignments.php?action=unassigned')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                unassignedCustomers = data.data;
                displayUnassignedList(unassignedCustomers);
            }
        });
    
    // Load assigned customers for this employee
    fetch(BASE_URL + 'api/assignments.php?action=by_employee&employee_id=' + employeeId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                assignedCustomers = data.data;
                displayAssignedList(assignedCustomers);
            }
        });
}

function displayUnassignedList(customers) {
    const container = document.getElementById('unassignedList');
    const countSpan = document.getElementById('unassignedCount');
    
    if (customers.length === 0) {
        container.innerHTML = '<div class="empty-message"><i class="fas fa-check-circle"></i>No unassigned customers available</div>';
        countSpan.innerHTML = '0';
        countSpan.className = 'count-badge';
        return;
    }
    
    let html = '';
    customers.forEach(customer => {
        html += `
            <div class="customer-item" data-name="${escapeHtml(customer.name).toLowerCase()}" data-email="${escapeHtml(customer.email || '').toLowerCase()}" data-phone="${escapeHtml(customer.phone || '').toLowerCase()}">
                <input type="checkbox" class="customer-checkbox" value="${customer.id}">
                <div class="customer-info">
                    <div class="customer-name">${escapeHtml(customer.name)}</div>
                    <div class="customer-details">
                        <i class="fas fa-envelope"></i> ${escapeHtml(customer.email) || 'No email'} &nbsp;
                        <i class="fas fa-phone"></i> ${escapeHtml(customer.phone) || 'No phone'} &nbsp;
                        <i class="fas fa-building"></i> ${escapeHtml(customer.company) || 'No company'}
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Update count
    const totalCount = customers.length;
    countSpan.innerHTML = totalCount;
    countSpan.className = 'count-badge total';
    
    // Store original data for filtering
    window.unassignedOriginalHtml = html;
    window.unassignedOriginalCount = totalCount;
}

function displayAssignedList(customers) {
    const container = document.getElementById('assignedList');
    const countSpan = document.getElementById('assignedCount');
    
    if (customers.length === 0) {
        container.innerHTML = '<div class="empty-message"><i class="fas fa-users"></i>No customers assigned to this employee</div>';
        countSpan.innerHTML = '0';
        countSpan.className = 'count-badge';
        return;
    }
    
    let html = '';
    customers.forEach(customer => {
        html += `
            <div class="customer-item" data-name="${escapeHtml(customer.name).toLowerCase()}" data-email="${escapeHtml(customer.email || '').toLowerCase()}" data-phone="${escapeHtml(customer.phone || '').toLowerCase()}">
                <div class="customer-info">
                    <div class="customer-name">${escapeHtml(customer.name)}</div>
                    <div class="customer-details">
                        <i class="fas fa-envelope"></i> ${escapeHtml(customer.email) || 'No email'} &nbsp;
                        <i class="fas fa-phone"></i> ${escapeHtml(customer.phone) || 'No phone'}
                    </div>
                </div>
                <button onclick="unassignCustomer(${customer.id})" class="unassign-btn" title="Remove assignment">
                    <i class="fas fa-user-minus"></i> Remove
                </button>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Update count
    const totalCount = customers.length;
    countSpan.innerHTML = totalCount;
    countSpan.className = 'count-badge total';
    
    // Store original data for filtering
    window.assignedOriginalHtml = html;
    window.assignedOriginalCount = totalCount;
}

function filterUnassignedList(searchTerm) {
    const items = document.querySelectorAll('#unassignedList .customer-item');
    const countSpan = document.getElementById('unassignedCount');
    const term = searchTerm.toLowerCase();
    let visibleCount = 0;
    
    if (term === '') {
        // Reset to show all
        items.forEach(item => {
            item.style.display = 'flex';
            visibleCount++;
        });
        countSpan.innerHTML = window.unassignedOriginalCount || visibleCount;
        countSpan.className = 'count-badge total';
    } else {
        // Filter based on search
        items.forEach(item => {
            const name = item.getAttribute('data-name') || '';
            const email = item.getAttribute('data-email') || '';
            const phone = item.getAttribute('data-phone') || '';
            
            if (name.indexOf(term) > -1 || email.indexOf(term) > -1 || phone.indexOf(term) > -1) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        countSpan.innerHTML = visibleCount;
        countSpan.className = 'count-badge filtered';
    }
}

function filterAssignedList(searchTerm) {
    const items = document.querySelectorAll('#assignedList .customer-item');
    const countSpan = document.getElementById('assignedCount');
    const term = searchTerm.toLowerCase();
    let visibleCount = 0;
    
    if (term === '') {
        // Reset to show all
        items.forEach(item => {
            item.style.display = 'flex';
            visibleCount++;
        });
        countSpan.innerHTML = window.assignedOriginalCount || visibleCount;
        countSpan.className = 'count-badge total';
    } else {
        // Filter based on search
        items.forEach(item => {
            const name = item.getAttribute('data-name') || '';
            const email = item.getAttribute('data-email') || '';
            const phone = item.getAttribute('data-phone') || '';
            
            if (name.indexOf(term) > -1 || email.indexOf(term) > -1 || phone.indexOf(term) > -1) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        countSpan.innerHTML = visibleCount;
        countSpan.className = 'count-badge filtered';
    }
}

function selectAll() {
    const checkboxes = document.querySelectorAll('#unassignedList .customer-item:not([style*="display: none"]) .customer-checkbox, #unassignedList .customer-item:not([style*="display: none"]) input');
    checkboxes.forEach(cb => {
        if (cb.type === 'checkbox') {
            cb.checked = true;
        }
    });
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('#unassignedList .customer-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = false;
    });
}

function assignCustomers() {
    if (!currentEmployeeId) {
        showToast('Please select an employee first', 'error');
        return;
    }
    
    const selectedCheckboxes = document.querySelectorAll('#unassignedList .customer-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        showToast('Please select at least one customer to assign', 'error');
        return;
    }
    
    const customerIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    const formData = new FormData();
    formData.append('employee_id', currentEmployeeId);
    customerIds.forEach(id => formData.append('customer_ids[]', id));
    
    const assignBtn = document.getElementById('assignBtn');
    assignBtn.disabled = true;
    assignBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Assigning...';
    
    fetch(BASE_URL + 'api/assignments.php?action=assign', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`Successfully assigned ${data.data.success_count} customers`, 'success');
            loadUnassignedCustomers();
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Network error. Please try again.', 'error');
    })
    .finally(() => {
        assignBtn.disabled = false;
        assignBtn.innerHTML = '<i class="fas fa-user-plus"></i> Assign Selected Customers';
    });
}

function unassignCustomer(customerId) {
    if (confirm('Are you sure you want to remove this customer from the employee?')) {
        const formData = new FormData();
        formData.append('customer_id', customerId);
        
        fetch(BASE_URL + 'api/assignments.php?action=unassign', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Customer unassigned successfully', 'success');
                loadUnassignedCustomers();
            } else {
                showToast('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Network error. Please try again.', 'error');
        });
    }
}

function showToast(message, type) {
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';
    toast.innerHTML = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 3000);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php include_once '../../components/footer.php'; ?>