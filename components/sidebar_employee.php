<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-crm"></i> CRM Employee</h4>
    </div>
    <div class="sidebar-menu">
        <a href="<?php echo BASE_URL; ?>pages/employee/index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>pages/employee/my_customers.php" class="<?php echo $current_page == 'my_customers.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> My Customers
        </a>
        <a href="<?php echo BASE_URL; ?>pages/employee/follow_ups.php" class="<?php echo $current_page == 'follow_ups.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Follow Ups
        </a>
        <a href="<?php echo BASE_URL; ?>pages/employee/my_history.php" class="<?php echo $current_page == 'my_history.php' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i> Call History
        </a>
    </div>
</div>