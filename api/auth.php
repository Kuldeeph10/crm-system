<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../includes/functions.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $action = $input['action'] ?? '';
    
    if ($action === 'login') {
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        $remember = $input['remember'] ?? false;
        
        if (empty($username) || empty($password)) {
            sendResponse(false, 'Username/email and password are required');
        }
        
        $user = validateLogin($username, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            if ($remember) {
                setRememberMe($user['id']);
            }
            
            sendResponse(true, 'Login successful', [
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ],
                'redirect' => ($user['role'] == 'admin') ? '../pages/admin/dashboard.html' : '../pages/employee/dashboard.html'
            ]);
        } else {
            sendResponse(false, 'Invalid username/email or password');
        }
    }
    
    elseif ($action === 'register') {
        $name = $input['name'] ?? '';
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($name) || empty($email) || empty($password)) {
            sendResponse(false, 'Name, email and password are required');
        }
        
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM employees WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            sendResponse(false, 'Email already registered');
        }
        
        // Check if phone exists (if provided)
        if (!empty($phone)) {
            $stmt = $pdo->prepare("SELECT id FROM employees WHERE phone = ?");
            $stmt->execute([$phone]);
            if ($stmt->rowCount() > 0) {
                sendResponse(false, 'Phone number already registered');
            }
        }
        
        // Insert new employee
        $stmt = $pdo->prepare("INSERT INTO employees (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'employee')");
        if ($stmt->execute([$name, $email, $phone, $password])) {
            sendResponse(true, 'Registration successful! Please login.');
        } else {
            sendResponse(false, 'Registration failed');
        }
    }
    
    elseif ($action === 'logout') {
        session_destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        sendResponse(true, 'Logged out successfully');
    }
    
    elseif ($action === 'check_session') {
        if (isLoggedIn()) {
            sendResponse(true, 'Session active', [
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'name' => $_SESSION['user_name'],
                    'email' => $_SESSION['user_email'],
                    'role' => $_SESSION['user_role']
                ]
            ]);
        } else {
            // Check remember me
            if (checkRememberMe()) {
                sendResponse(true, 'Session restored', [
                    'user' => [
                        'id' => $_SESSION['user_id'],
                        'name' => $_SESSION['user_name'],
                        'email' => $_SESSION['user_email'],
                        'role' => $_SESSION['user_role']
                    ]
                ]);
            } else {
                sendResponse(false, 'No active session');
            }
        }
    }
}
?>