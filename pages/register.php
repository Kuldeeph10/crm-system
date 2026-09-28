<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, email and password are required';
    } elseif ($password != $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 4) {
        $error = 'Password must be at least 4 characters';
    } else {
        $existing = getEmployeeByEmail($email);
        if ($existing) {
            $error = 'Email already registered';
        } elseif (!empty($phone) && getEmployeeByPhone($phone)) {
            $error = 'Phone number already registered';
        } else {
            if (createEmployee($name, $email, $phone, $password, 'employee')) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-card {
            background: white;
            border-radius: 10px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .register-card h2 {
            margin-bottom: 30px;
            text-align: center;
        }
        .btn-primary {
            background: #667eea;
            border: none;
            width: 100%;
            padding: 12px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <h2><i class="fas fa-user-plus"></i> Register</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label>Full Name *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Phone Number (Optional)</label>
                <input type="tel" name="phone" class="form-control" placeholder="Can also be used for login">
                <small class="text-muted">If provided, you can login using phone number</small>
            </div>
            <div class="mb-3">
                <label>Password *</label>
                <input type="password" name="password" class="form-control" required>
                <small class="text-muted">Minimum 4 characters</small>
            </div>
            <div class="mb-3">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Register
            </button>
        </form>
        
        <div class="login-link">
            <a href="login.php">Already have an account? Login here</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>