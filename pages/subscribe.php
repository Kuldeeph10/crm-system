<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validation - Name and Phone are required
    if (empty($name)) {
        $error = 'Please enter your full name';
    } elseif (empty($phone)) {
        $error = 'Please enter your phone number';
    } else {
        // Check if phone already exists (unique validation)
        global $pdo;
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $error = 'This phone number is already registered. Please use a different number.';
        } else {
            // Check if email already exists (only if email is provided)
            if (!empty($email)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'This email is already registered. Please use a different email.';
                }
            }
        }

        // Insert if no error
        if (empty($error)) {
            $created_at = date('Y-m-d');
            $result = createUser($name, $email, $phone, $company, $address, $created_at);

            if ($result) {
                $success = 'Thank you for subscribing! Our team will contact you soon on ' . $phone . '.';
                // Clear form data
                $_POST = array();
            } else {
                $error = 'Something went wrong. Please try again.';
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
    <title>Subscribe - CRM System</title>
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
            padding: 20px;
        }

        /* Animated background shapes */
        .bg-shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
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
            width: 200px;
            height: 200px;
            top: 50%;
            left: 50%;
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
        .subscribe-container {
            width: 100%;
            max-width: 1200px;
            margin: 20px;
            position: relative;
            z-index: 1;
        }

        .subscribe-wrapper {
            display: flex;
            background: white;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
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

        .benefits-list {
            list-style: none;
        }

        .benefits-list li {
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .benefits-list li i {
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

        /* Right side - Subscribe Form */
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
            position: relative;
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

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-10px);
                display: none;
            }
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #22543d;
        }

        .alert-danger {
            background: #fed7d7;
            color: #c53030;
            border-left: 4px solid #c53030;
        }

        .alert .close-alert {
            margin-left: auto;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .alert .close-alert:hover {
            opacity: 1;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .input-label .required {
            color: #e53e3e;
            margin-left: 4px;
        }

        .input-label .optional {
            color: #a0aec0;
            font-size: 11px;
            font-weight: 400;
            margin-left: 8px;
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

        .input-wrapper input,
        .input-wrapper textarea {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            background: white;
        }

        .input-wrapper textarea {
            padding: 14px 16px 14px 48px;
            resize: vertical;
            min-height: 80px;
        }

        .input-wrapper input:focus,
        .input-wrapper textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .row-2cols {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .subscribe-btn {
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
            margin-top: 10px;
        }

        .subscribe-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .subscribe-btn:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 28px;
            padding-top: 28px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #718096;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }

        .login-link a:hover {
            color: #764ba2;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .subscribe-wrapper {
                flex-direction: column;
            }

            .brand-side {
                padding: 32px;
                text-align: center;
            }

            .benefits-list li {
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

            .row-2cols {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

        @media (max-width: 480px) {
            .subscribe-container {
                margin: 10px;
            }

            .brand-side,
            .form-side {
                padding: 24px;
            }

            .form-header h3 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>

    <div class="subscribe-container">
        <div class="subscribe-wrapper">
            <!-- Left Side - Branding -->
            <div class="brand-side">
                <div class="logo">
                    <h2><i class="fas fa-crm"></i> CRM System</h2>
                    <p>Customer Relationship Management</p>
                </div>

                <div class="brand-content">
                    <h1>Stay Connected!</h1>
                    <p>Subscribe to get updates, exclusive offers, and the best CRM experience for your business.</p>

                    <ul class="benefits-list">
                        <li><i class="fas fa-gift"></i> Exclusive Offers & Discounts</li>
                        <li><i class="fas fa-newspaper"></i> Monthly Newsletter</li>
                        <li><i class="fas fa-headset"></i> Priority Support</li>
                        <li><i class="fas fa-chart-line"></i> Business Insights & Tips</li>
                    </ul>
                </div>

                <div class="stats">
                    <div class="stat-item">
                        <h3>500+</h3>
                        <p>Active Subscribers</p>
                    </div>
                    <div class="stat-item">
                        <h3>10k+</h3>
                        <p>Happy Clients</p>
                    </div>
                    <div class="stat-item">
                        <h3>24/7</h3>
                        <p>Support</p>
                    </div>
                </div>
            </div>

            <!-- Right Side - Subscribe Form -->
            <div class="form-side">
                <div class="form-header">
                    <h3>Subscribe Now</h3>
                    <p>Fill out the form below to get started</p>
                </div>

                <div id="alertContainer">
                    <?php if ($success): ?>
                        <div class="alert alert-success" id="successAlert">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success); ?>
                            <span class="close-alert" onclick="closeAlert('successAlert')">&times;</span>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" id="errorAlert">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <span class="close-alert" onclick="closeAlert('errorAlert')">&times;</span>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="POST" action="" id="subscribeForm">
                    <div class="input-group">
                        <label class="input-label">
                            Full Name <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" id="name" placeholder="Enter your full name"
                                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">
                            Phone Number <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="phone" id="phone" placeholder="+1 234 567 8900"
                                value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                required>
                        </div>
                    </div>

                    <div class="row-2cols">
                        <div class="input-group">
                            <label class="input-label">
                                Email Address <span class="optional">(optional)</span>
                            </label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" id="email" placeholder="your@email.com"
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>

                        <div class="input-group">
                            <label class="input-label">
                                Company Name <span class="optional">(optional)</span>
                            </label>
                            <div class="input-wrapper">
                                <i class="fas fa-building"></i>
                                <input type="text" name="company" id="company" placeholder="Your company name"
                                    value="<?php echo isset($_POST['company']) ? htmlspecialchars($_POST['company']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">
                            Address <span class="optional">(optional)</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-location-dot"></i>
                            <textarea name="address" id="address"
                                placeholder="Your full address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="subscribe-btn" id="subscribeBtn">
                        <i class="fas fa-paper-plane"></i>
                        Subscribe Now
                    </button>
                </form>

                <div class="login-link">
                    Already have an account?
                    <a href="login.php">Sign In</a>
                </div>

                <div style="margin-top: 20px; font-size: 12px; color: #a0aec0; text-align: center;">
                    <i class="fas fa-lock"></i> Your information is safe with us. We never share your data.
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 4 seconds
        setTimeout(function () {
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');

            if (successAlert) {
                successAlert.style.animation = 'fadeOut 0.5s ease forwards';
                setTimeout(function () {
                    if (successAlert) successAlert.remove();
                }, 500);
            }

            if (errorAlert) {
                errorAlert.style.animation = 'fadeOut 0.5s ease forwards';
                setTimeout(function () {
                    if (errorAlert) errorAlert.remove();
                }, 500);
            }
        }, 4000);

        // Manual close alert function
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(function () {
                    if (alert) alert.remove();
                }, 300);
            }
        }

        // Phone number formatting (unique and professional)
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                let formattedValue = '';

                if (value.length > 0) {
                    if (value.length <= 3) {
                        formattedValue = value;
                    } else if (value.length <= 6) {
                        formattedValue = value.slice(0, 3) + '-' + value.slice(3);
                    } else if (value.length <= 10) {
                        formattedValue = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
                    } else {
                        formattedValue = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10) + ' ext ' + value.slice(10, 14);
                    }
                    e.target.value = formattedValue;
                }
            });

            // Real-time validation for unique phone (optional enhancement)
            phoneInput.addEventListener('blur', function () {
                const phone = this.value.replace(/\D/g, '');
                if (phone.length > 0 && phone.length < 10) {
                    this.style.borderColor = '#e53e3e';
                    const errorMsg = document.getElementById('phoneError');
                    if (!errorMsg) {
                        const msg = document.createElement('small');
                        msg.id = 'phoneError';
                        msg.style.color = '#e53e3e';
                        msg.style.fontSize = '11px';
                        msg.style.marginTop = '5px';
                        msg.style.display = 'block';
                        msg.innerHTML = 'Please enter a valid 10-digit phone number';
                        this.parentElement.appendChild(msg);
                    }
                } else {
                    this.style.borderColor = '#e2e8f0';
                    const errorMsg = document.getElementById('phoneError');
                    if (errorMsg) errorMsg.remove();
                }
            });

            phoneInput.addEventListener('focus', function () {
                this.style.borderColor = '#667eea';
                const errorMsg = document.getElementById('phoneError');
                if (errorMsg) errorMsg.remove();
            });
        }

        // Form submission loading state
        document.getElementById('subscribeForm').addEventListener('submit', function () {
            const btn = document.getElementById('subscribeBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
            btn.disabled = true;
        });

        // Input focus effects
        const inputs = document.querySelectorAll('.input-wrapper input, .input-wrapper textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', function () {
                this.parentElement.style.transform = 'scale(1.01)';
            });
            input.addEventListener('blur', function () {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>

    <style>
        .loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .fa-spin {
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

        .close-alert {
            font-size: 20px;
            font-weight: 300;
            cursor: pointer;
            margin-left: auto;
            padding: 0 5px;
        }
    </style>
</body>

</html>