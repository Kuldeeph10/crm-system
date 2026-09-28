<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-crm"></i> CRM Admin</h4>
    </div>
    <div class="sidebar-menu">
        <a href="<?php echo BASE_URL; ?>pages/admin/index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>pages/admin/customers.php" class="<?php echo $current_page == 'customers.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Customers
        </a>
        <a href="<?php echo BASE_URL; ?>pages/admin/bulk_import.php" class="<?php echo $current_page == 'bulk_import.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-import"></i> Bulk Import
        </a>
        <a href="<?php echo BASE_URL; ?>pages/admin/employees.php" class="<?php echo $current_page == 'employees.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-tie"></i> Employees
        </a>
        <a href="<?php echo BASE_URL; ?>pages/admin/assign.php" class="<?php echo $current_page == 'assign.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-plus"></i> Assign Customers
        </a>
        <a href="<?php echo BASE_URL; ?>pages/admin/call_records.php" class="<?php echo $current_page == 'call_records.php' ? 'active' : ''; ?>">
            <i class="fas fa-phone-alt"></i> Call Records
        </a>
        <a href="<?php echo BASE_URL; ?>pages/admin/follow_ups.php" class="<?php echo $current_page == 'follow_ups.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Follow Ups
        </a>
    </div>
</div>