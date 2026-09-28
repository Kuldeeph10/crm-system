<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    if (hasRole('admin')) {
        header('Location: admin/index.php');
    } else {
        header('Location: employee/index.php');
    }
    exit();
}

// Check remember me FIRST before showing login page
checkRememberMe();

// Double-check if remember me restored session
if (isLoggedIn()) {
    if (hasRole('admin')) {
        header('Location: admin/index.php');
    } else {
        header('Location: employee/index.php');
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = 'Please enter email/phone and password';
    } else {
        $user = validateLogin($username, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            if ($remember) {
                setRememberMe($user['id']);
            }

            if ($user['role'] == 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: employee/index.php');
            }
            exit();
        } else {
            $error = 'Invalid email/phone or password!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM System - Login</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background shapes */
        .bg-shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite;
        }

        .shape-1 {
            width: 500px;
            height: 500px;
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 500px;
            height: 500px;
            bottom: 50px;
            right: -250px;
            animation-delay: 5s;
        }

        .shape-3 {
            width: 400px;
            height: 400px;
            top: -26%;
            left: 40%;
            transform: translate(-50%, -50%);
            animation-delay: 10s;
            opacity: 0.05;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            33% {
                transform: translate(30px, -30px) rotate(120deg);
            }

            66% {
                transform: translate(-20px, 20px) rotate(240deg);
            }
        }

        /* Main container */
        .login-container {
            width: 100%;
            max-width: 1200px;
            margin: 20px;
            position: relative;
            z-index: 1;
        }

        .login-wrapper {
            display: flex;
            background: white;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
        }

        /* Left side - Branding */
        .brand-side {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .brand-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><path d="M10,50 L90,50 M50,10 L50,90" stroke="white" stroke-width="2"/><circle cx="50" cy="50" r="30" fill="none" stroke="white" stroke-width="2"/><circle cx="50" cy="50" r="15" fill="white"/></svg>') repeat;
            background-size: 40px;
            opacity: 0.1;
        }

        .logo {
            position: relative;
            z-index: 1;
        }

        .logo h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .logo p {
            opacity: 0.8;
            font-size: 14px;
        }

        .brand-content {
            position: relative;
            z-index: 1;
        }

        .brand-content h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .brand-content p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .features-list {
            list-style: none;
        }

        .features-list li {
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .features-list li i {
            width: 20px;
            font-size: 18px;
        }

        .stats {
            display: flex;
            gap: 30px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-item h3 {
            font-size: 28px;
            font-weight: 700;
        }

        .stat-item p {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
        }

        /* Right side - Login Form */
        .form-side {
            flex: 1;
            padding: 48px;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 32px;
        }

        .form-header h3 {
            font-size: 28px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .form-header p {
            color: #718096;
            font-size: 14px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background: #fed7d7;
            color: #c53030;
            border-left: 4px solid #c53030;
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #22543d;
        }

        .input-group {
            margin-bottom: 24px;
        }

        .input-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 18px;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            background: white;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .input-wrapper .toggle-password {
            position: absolute;
            right: 16px;
            left: auto;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
            transition: color 0.3s;
        }

        .input-wrapper .toggle-password:hover {
            color: #667eea;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 14px;
            color: #4a5568;
        }

        .checkbox-label input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .forgot-link {
            font-size: 14px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .forgot-link:hover {
            color: #764ba2;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn i {
            font-size: 18px;
        }

        .register-link {
            text-align: center;
            margin-top: 28px;
            padding-top: 28px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #718096;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }

        .register-link a:hover {
            color: #764ba2;
        }

        .demo-card {
            margin-top: 24px;
            background: #f7fafc;
            border-radius: 16px;
            padding: 16px;
        }

        .demo-card p {
            font-size: 12px;
            color: #718096;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-card p i {
            color: #667eea;
        }

        .demo-credentials {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .demo-badge {
            background: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-family: monospace;
            color: #2d3748;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.3s;
        }

        .demo-badge:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        /* Loading state */
        .login-btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .login-btn.loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media (max-width: 968px) {
            .login-wrapper {
                flex-direction: column;
            }

            .brand-side {
                padding: 32px;
                text-align: center;
            }

            .features-list li {
                justify-content: center;
            }

            .stats {
                justify-content: center;
            }

            .form-side {
                padding: 32px;
            }

            .brand-content h1 {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
            }

            .brand-side,
            .form-side {
                padding: 24px;
            }

            .form-header h3 {
                font-size: 24px;
            }

            .demo-credentials {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>

    <div class="login-container">
        <div class="login-wrapper">
            <!-- Left Side - Branding -->
            <div class="brand-side">
                <div class="logo">
                    <h2><i class="fas fa-crm"></i> CRM System</h2>
                    <p>Customer Relationship Management</p>
                </div>

                <div class="brand-content">
                    <h1>Welcome Back!</h1>
                    <p>Manage your customers, track calls, and grow your business with our powerful CRM platform.</p>

                    <ul class="features-list">
                        <li><i class="fas fa-check-circle"></i> Customer Management</li>
                        <li><i class="fas fa-phone-alt"></i> Call Tracking & Recording</li>
                        <li><i class="fas fa-chart-line"></i> Analytics & Reports</li>
                        <li><i class="fas fa-users"></i> Team Collaboration</li>
                    </ul>
                </div>

                <div class="stats">
                    <div class="stat-item">
                        <h3>500+</h3>
                        <p>Happy Clients</p>
                    </div>
                    <div class="stat-item">
                        <h3>10k+</h3>
                        <p>Calls Tracked</p>
                    </div>
                    <div class="stat-item">
                        <h3>99%</h3>
                        <p>Uptime</p>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="form-side">
                <div class="form-header">
                    <h3>Sign In</h3>
                    <p>Enter your credentials to access your account</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="loginForm" autocomplete="on">
                    <div class="input-group">
                        <label class="input-label">Email or Phone Number</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="text" name="username" id="username" class="form-input"
                                placeholder="Enter your email or phone number" autocomplete="username"
                                value="<?php echo isset($_COOKIE['saved_username']) ? htmlspecialchars($_COOKIE['saved_username']) : ''; ?>"
                                required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" class="form-input"
                                placeholder="Enter your password" autocomplete="current-password" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                        </div>
                    </div>

                    <div class="checkbox-wrapper">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Remember Me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="login-btn" id="loginBtn">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>

                <div class="register-link">
                    New here? 
                    <a href="subscribe.php">Subscribe to our newsletter</a>
                </div>

                <!-- <div class="demo-card">
                    <p><i class="fas fa-info-circle"></i> Demo Credentials (Click to auto-fill)</p>
                    <div class="demo-credentials">
                        <span class="demo-badge" onclick="fillCredentials('admin@crm.com', 'admin123')">
                            <i class="fas fa-user-shield"></i> Admin: admin@crm.com
                        </span>
                        <span class="demo-badge" onclick="fillCredentials('employee@crm.com', 'emp123')">
                            <i class="fas fa-user"></i> Employee: employee@crm.com
                        </span>
                        <span class="demo-badge" onclick="fillCredentials('1234567890', 'admin123')">
                            <i class="fas fa-phone"></i> Phone: 1234567890
                        </span>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Fill demo credentials
        function fillCredentials(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;

            // Visual feedback
            const demoBadges = document.querySelectorAll('.demo-badge');
            demoBadges.forEach(badge => {
                badge.style.opacity = '0.5';
            });
            event.target.style.opacity = '1';

            setTimeout(() => {
                demoBadges.forEach(badge => {
                    badge.style.opacity = '1';
                });
            }, 500);
        }

        // Save credentials to localStorage when Remember Me is checked
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const rememberCheckbox = document.getElementById('remember');
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            if (rememberCheckbox.checked) {
                localStorage.setItem('saved_username', username);
                localStorage.setItem('saved_password', password);
            } else {
                localStorage.removeItem('saved_username');
                localStorage.removeItem('saved_password');
            }

            // Show loading state
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner"></i> Signing in...';
        });

        // Auto-fill from localStorage on page load
        document.addEventListener('DOMContentLoaded', function () {
            const savedUsername = localStorage.getItem('saved_username');
            const savedPassword = localStorage.getItem('saved_password');

            if (savedUsername && savedPassword) {
                document.getElementById('username').value = savedUsername;
                document.getElementById('password').value = savedPassword;
                document.getElementById('remember').checked = true;
            }
        });

        // Add animation to inputs
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function () {
                this.parentElement.style.transform = 'scale(1.01)';
            });
            input.addEventListener('blur', function () {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>

</html>