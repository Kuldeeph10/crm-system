<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$employee = getEmployeeById($id);

if (!$employee) {
    header('Location: employees.php');
    exit();
}

$page_title = 'Edit Employee';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if (empty($name)) {
        $error = 'Name is required';
    } elseif (empty($email)) {
        $error = 'Email is required';
    } else {
        // Check if email exists for other employee
        $existing = getEmployeeByEmail($email);
        if ($existing && $existing['id'] != $id) {
            $error = 'Email already exists for another employee';
        } elseif (!empty($phone)) {
            $existingPhone = getEmployeeByPhone($phone);
            if ($existingPhone && $existingPhone['id'] != $id) {
                $error = 'Phone number already exists for another employee';
            }
        }

        if (empty($error)) {
            $result = updateEmployee($id, $name, $email, $phone, 'employee', $status);
            if ($result) {
                $success = 'Employee updated successfully!';
                $employee = getEmployeeById($id);
            } else {
                $error = 'Failed to update employee';
            }
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
            <h1><i class="fas fa-user-edit"></i> Edit Employee</h1>
            <div class="header-actions">
                <a href="employees.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Employees
                </a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <script>
                setTimeout(function () {
                    window.location.href = 'employees.php';
                }, 1500);
            </script>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control"
                            value="<?php echo htmlspecialchars($employee['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control"
                            value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control"
                            value="<?php echo htmlspecialchars($employee['phone']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active" <?php echo $employee['status'] == 'active' ? 'selected' : ''; ?>>Active
                            </option>
                            <option value="inactive" <?php echo $employee['status'] == 'inactive' ? 'selected' : ''; ?>>
                                Inactive</option>
                        </select>
                        <small>Inactive employees cannot login</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Employee
                    </button>
                    <a href="employees.php" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <div class="info-card">
            <h4><i class="fas fa-info-circle"></i> Note</h4>
            <p>To change password, go to Employee List and click the "Password" button.</p>
            <p>Employee ID: <?php echo $employee['id']; ?> | Created:
                <?php echo date('F j, Y', strtotime($employee['created_at'])); ?></p>
        </div>
    </div>
</div>

<style>
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
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
    }

    .form-control:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
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

    .info-card {
        background: #e8f4fd;
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid #3498db;
    }

    .info-card h4 {
        margin: 0 0 10px 0;
        font-size: 16px;
    }

    .info-card p {
        margin: 5px 0;
        color: #555;
    }

    .alert {
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
            gap: 15px;
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

<?php include_once '../../components/footer.php'; ?>