<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../components/alert.php';

// Check if logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customer = getUserById($id);

if (!$customer) {
    header('Location: customers.php');
    exit();
}

$page_title = 'Edit Customer';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name)) {
        $error = 'Customer name is required';
    } else {
        $result = updateUser($id, $name, $email, $phone, $company, $address, $status);
        if ($result) {
            $success = true; // Set success flag for redirect
        } else {
            $error = 'Failed to update customer';
        }
    }
}

include_once '../../components/header.php';
include_once '../../components/navbar.php';
?>

<div class="wrapper">
    <?php include_once '../../components/sidebar_admin.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> Edit Customer</h1>
            <div class="header-actions">
                <a href="customers.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Customers
                </a>
            </div>
        </div>
        
        <?php if ($error): ?>
            <?php displayError($error); ?>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <?php displaySuccess('Customer updated successfully! Redirecting...'); ?>
            <script>
                setTimeout(function() {
                    window.location.href = 'customers.php';
                }, 1500);
            </script>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="POST" action="" id="editCustomerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($customer['name']); ?>" 
                               placeholder="Enter customer full name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($customer['email']); ?>" 
                               placeholder="customer@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($customer['phone']); ?>" 
                               placeholder="+1 234 567 8900">
                    </div>
                    
                    <div class="form-group">
                        <label for="company">Company Name</label>
                        <input type="text" id="company" name="company" class="form-control" 
                               value="<?php echo htmlspecialchars($customer['company']); ?>" 
                               placeholder="Company name">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="active" <?php echo $customer['status'] == 'active' ? 'selected' : ''; ?>>
                                Active
                            </option>
                            <option value="inactive" <?php echo $customer['status'] == 'inactive' ? 'selected' : ''; ?>>
                                Inactive
                            </option>
                        </select>
                        <small class="form-text">Inactive customers won't appear in employee lists</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="created_at">Created Date</label>
                        <input type="date" id="created_at" name="created_at" class="form-control" 
                               value="<?php echo $customer['created_at']; ?>" disabled>
                        <small class="form-text">Created date cannot be changed</small>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" 
                                  rows="3" placeholder="Enter customer address"><?php echo htmlspecialchars($customer['address']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Update Customer
                    </button>
                    <a href="customers.php" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Assignment Information Card -->
        <?php if ($customer['assigned_to']): ?>
        <div class="info-card">
            <h4><i class="fas fa-info-circle"></i> Assignment Information</h4>
            <p>
                <strong>Assigned To:</strong> <?php echo htmlspecialchars($customer['assigned_employee_name']); ?><br>
                <strong>Assigned Since:</strong> <?php echo date('F j, Y', strtotime($customer['updated_at'] ?? $customer['created_at'])); ?>
            </p>
            <p class="text-muted">To change assignment, go to <a href="assign.php">Assign Customers</a> page.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../../components/footer.php'; ?>