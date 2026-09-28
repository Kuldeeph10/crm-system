<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../components/alert.php';  // Add this line

// Check if logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$page_title = 'Add New Customer';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $created_at = $_POST['created_at'] ?? date('Y-m-d');
    
    if (empty($name)) {
        $error = 'Customer name is required';
    } else {
        $result = createUser($name, $email, $phone, $company, $address, $created_at);
        if ($result) {
            $success = 'Customer added successfully! Redirecting...';
            // Clear form after success
            $_POST = array();
        } else {
            $error = 'Failed to add customer';
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
            <h1><i class="fas fa-user-plus"></i> Add New Customer</h1>
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
            <?php displaySuccess($success); ?>
            <script>
                setTimeout(function() {
                    window.location.href = 'customers.php';
                }, 2000);
            </script>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="POST" action="" id="customerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                               placeholder="Enter customer full name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               placeholder="customer@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                               placeholder="+1 234 567 8900">
                    </div>
                    
                    <div class="form-group">
                        <label for="company">Company Name</label>
                        <input type="text" id="company" name="company" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['company'] ?? ''); ?>" 
                               placeholder="Company name">
                    </div>
                    
                    <div class="form-group">
                        <label for="created_at">Created Date</label>
                        <input type="date" id="created_at" name="created_at" class="form-control" 
                               value="<?php echo $_POST['created_at'] ?? date('Y-m-d'); ?>">
                        <small class="form-text">Date when this customer was added</small>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" 
                                  rows="3" placeholder="Enter customer address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Customer
                    </button>
                    <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                    <a href="customers.php" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Form specific styles */
.form-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group {
    margin-bottom: 0;
}

.form-group.full-width {
    grid-column: span 2;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
    font-size: 14px;
}

.required {
    color: #e74c3c;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
}

.form-text {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #666;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

/* Responsive */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .form-group.full-width {
        grid-column: span 1;
    }
    
    .form-card {
        padding: 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Form validation before submit
document.getElementById('customerForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    
    if (name === '') {
        e.preventDefault();
        showErrorAlert('Please enter customer name');
        return false;
    }
    
    // Email validation (if provided)
    if (email !== '') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            showErrorAlert('Please enter a valid email address');
            return false;
        }
    }
    
    // Phone validation (if provided)
    if (phone !== '') {
        const phoneRegex = /^[\+\d\s\-\(\)]{10,20}$/;
        if (!phoneRegex.test(phone)) {
            e.preventDefault();
            showErrorAlert('Please enter a valid phone number');
            return false;
        }
    }
    
    // Show loading state on button
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    btn.disabled = true;
});

function resetForm() {
    if (confirm('Reset all form fields?')) {
        document.getElementById('customerForm').reset();
        // Reset date to today
        document.getElementById('created_at').value = '<?php echo date('Y-m-d'); ?>';
    }
}

function showErrorAlert(message) {
    // Remove existing alerts
    const existingAlert = document.querySelector('.alert-danger');
    if (existingAlert) existingAlert.remove();
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger';
    alertDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
        <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Insert at top of main content
    const mainContent = document.querySelector('.main-content');
    const pageHeader = document.querySelector('.page-header');
    mainContent.insertBefore(alertDiv, pageHeader.nextSibling);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alertDiv) alertDiv.remove();
    }, 3000);
}
</script>

<?php include_once '../../components/footer.php'; ?>