<?php
require_once __DIR__ . '/config.php';

// ============ SESSION & AUTHENTICATION ============

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Check if user has specific role
function hasRole($role)
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
}

// Redirect if not logged in
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'pages/login.php');
        exit();
    }
}

// Redirect if role doesn't match
function requireRole($role)
{
    requireLogin();
    if (!hasRole($role)) {
        if ($role == 'admin') {
            header('Location: ' . BASE_URL . 'pages/employee/index.php');
        } else {
            header('Location: ' . BASE_URL . 'pages/admin/index.php');
        }
        exit();
    }
}

// ============ API RESPONSE FUNCTIONS ============

function sendResponse($success, $message, $data = null, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// ============ EMPLOYEE / AUTH FUNCTIONS ============

// Get employee by ID
function getEmployeeById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, name, email, phone, role, status, created_at FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Get employee by email OR phone
function getEmployeeByLogin($username)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE (email = ? OR phone = ?) AND status = 'active'");
    $stmt->execute([$username, $username]);
    return $stmt->fetch();
}

// Get employee by email
function getEmployeeByEmail($email)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// Get employee by phone
function getEmployeeByPhone($phone)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE phone = ?");
    $stmt->execute([$phone]);
    return $stmt->fetch();
}

// Validate login
function validateLogin($username, $password)
{
    $user = getEmployeeByLogin($username);
    if ($user && $user['password'] == $password) {
        return $user;
    }
    return false;
}

// Get all employees
function getAllEmployees()
{
    global $pdo;
    $stmt = $pdo->query("SELECT id, name, email, phone, role, status, created_at FROM employees WHERE role = 'employee' ORDER BY name");
    return $stmt->fetchAll();
}

// Get all employees including admins
function getAllEmployeesWithAdmins()
{
    global $pdo;
    $stmt = $pdo->query("SELECT id, name, email, phone, role, status, created_at FROM employees ORDER BY role DESC, name");
    return $stmt->fetchAll();
}

// Create new employee
function createEmployee($name, $email, $phone, $password, $role = 'employee')
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO employees (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $phone, $password, $role]);
}

// Update employee
function updateEmployee($id, $name, $email, $phone, $role, $status)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE employees SET name = ?, email = ?, phone = ?, role = ?, status = ? WHERE id = ?");
    return $stmt->execute([$name, $email, $phone, $role, $status, $id]);
}

// Delete employee
function deleteEmployee($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE assigned_to = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        return false;
    }

    $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ? AND role != 'admin'");
    return $stmt->execute([$id]);
}

// ============ SEARCH EMPLOYEES ============
function searchEmployees($keyword)
{
    global $pdo;
    $keyword = "%$keyword%";
    $stmt = $pdo->prepare("SELECT id, name, email, phone, role, status, created_at 
                           FROM employees 
                           WHERE (name LIKE ? OR email LIKE ? OR phone LIKE ?) AND role = 'employee'
                           ORDER BY name");
    $stmt->execute([$keyword, $keyword, $keyword]);
    return $stmt->fetchAll();
}

// ============ UPDATE EMPLOYEE PASSWORD ============
function updateEmployeePassword($id, $password)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE employees SET password = ? WHERE id = ?");
    return $stmt->execute([$password, $id]);
}


// ============ REMEMBER ME FUNCTIONS ============


function setRememberMe($user_id)
{
    $token = bin2hex(random_bytes(32));
    $expiry = time() + (86400 * 30); // 30 days
    $expiry_date = date('Y-m-d H:i:s', $expiry);

    global $pdo;

    try {
        // Delete old tokens for this user
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Store new token
        $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expiry) VALUES (?, ?, ?)");
        $result = $stmt->execute([$user_id, $token, $expiry_date]);

        if ($result) {
            // Set cookie
            setcookie('remember_token', $token, $expiry, '/');
            error_log("Remember Me: Token saved for user_id: $user_id, token: $token");
            return true;
        } else {
            error_log("Remember Me: Failed to insert token - " . print_r($stmt->errorInfo(), true));
            return false;
        }
    } catch (Exception $e) {
        error_log("Remember Me Exception: " . $e->getMessage());
        return false;
    }
}

function checkRememberMe()
{
    // Check if cookie exists
    if (isset($_COOKIE['remember_token'])) {
        global $pdo;
        $token = $_COOKIE['remember_token'];

        // First, clean up expired tokens
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE expiry < NOW()");
        $stmt->execute();

        // Check for valid token
        $stmt = $pdo->prepare("SELECT * FROM remember_tokens WHERE token = ? AND expiry > NOW()");
        $stmt->execute([$token]);
        $token_data = $stmt->fetch();

        if ($token_data) {
            // Get user details
            $stmt = $pdo->prepare("SELECT id, name, email, phone, role, status FROM employees WHERE id = ? AND status = 'active'");
            $stmt->execute([$token_data['user_id']]);
            $user = $stmt->fetch();

            if ($user) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                // Refresh the cookie expiry (optional - extends for another 30 days)
                $new_expiry = time() + (86400 * 30);
                setcookie('remember_token', $token, $new_expiry, '/');

                return true;
            } else {
                // User not found or inactive, delete invalid token
                $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
                $stmt->execute([$token]);
                setcookie('remember_token', '', time() - 3600, '/');
            }
        } else {
            // Token invalid or expired, clear cookie
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
    return false;
}

function clearRememberMe()
{
    if (isset($_COOKIE['remember_token'])) {
        global $pdo;
        $token = $_COOKIE['remember_token'];
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
        $stmt->execute([$token]);
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

// ============ USER (CUSTOMER) FUNCTIONS ============

function getAllUsers()
{
    global $pdo;
    $stmt = $pdo->query("SELECT u.*, e.name as assigned_employee_name 
                         FROM users u 
                         LEFT JOIN employees e ON u.assigned_to = e.id 
                         ORDER BY u.created_at DESC");
    return $stmt->fetchAll();
}

function getUsersGroupedByDate()
{
    global $pdo;
    $stmt = $pdo->query("SELECT u.*, e.name as assigned_employee_name 
                         FROM users u 
                         LEFT JOIN employees e ON u.assigned_to = e.id 
                         ORDER BY u.created_at DESC");
    $users = $stmt->fetchAll();

    $grouped = [];
    foreach ($users as $user) {
        $date = $user['created_at'];
        if (!isset($grouped[$date])) {
            $grouped[$date] = [];
        }
        $grouped[$date][] = $user;
    }
    return $grouped;
}

function getUserById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT u.*, e.name as assigned_employee_name 
                           FROM users u 
                           LEFT JOIN employees e ON u.assigned_to = e.id 
                           WHERE u.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getUsersByEmployee($employee_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE assigned_to = ? ORDER BY created_at DESC");
    $stmt->execute([$employee_id]);
    return $stmt->fetchAll();
}

function getUnassignedUsers()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM users WHERE assigned_to IS NULL ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function createUser($name, $email, $phone, $company, $address, $created_at)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, company, address, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $phone, $company, $address, $created_at]);
}

function updateUser($id, $name, $email, $phone, $company, $address, $status)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, company = ?, address = ?, status = ? WHERE id = ?");
    return $stmt->execute([$name, $email, $phone, $company, $address, $status, $id]);
}

function deleteUser($id)
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$id]);
}

function getTotalUsersCount()
{
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    return $stmt->fetchColumn();
}

// ============ ASSIGNMENT FUNCTIONS ============

function assignUserToEmployee($user_id, $employee_id, $assigned_by, $assignment_date = null)
{
    global $pdo;
    if ($assignment_date === null) {
        $assignment_date = date('Y-m-d');
    }

    try {
        $pdo->beginTransaction();

        // Update user with assignment_date
        $stmt = $pdo->prepare("UPDATE users SET assigned_to = ?, assignment_date = ? WHERE id = ?");
        $stmt->execute([$employee_id, $assignment_date, $user_id]);

        // Record in history
        $stmt = $pdo->prepare("INSERT INTO assignments_history (user_id, employee_id, assigned_by) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $employee_id, $assigned_by]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

function bulkAssignUsersToEmployee($user_ids, $employee_id, $assigned_by)
{
    global $pdo;
    try {
        $pdo->beginTransaction();

        foreach ($user_ids as $user_id) {
            $stmt = $pdo->prepare("UPDATE users SET assigned_to = ? WHERE id = ?");
            $stmt->execute([$employee_id, $user_id]);

            $stmt = $pdo->prepare("INSERT INTO assignments_history (user_id, employee_id, assigned_by) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $employee_id, $assigned_by]);
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// ============ CALL RECORDS FUNCTIONS ============

// Add call record and handle follow-up updates
// Add call record and handle follow-up updates
function addCallRecord($user_id, $employee_id, $notes, $interest_level, $follow_up_date = null, $recording_file = null, $followup_id = null)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // If this is a follow-up call (has followup_id)
        if ($followup_id && $followup_id > 0) {
            // Get the original follow-up record
            $stmt = $pdo->prepare("SELECT * FROM call_records WHERE id = ?");
            $stmt->execute([$followup_id]);
            $old_record = $stmt->fetch();

            if ($old_record) {
                if ($interest_level === 'interested' || $interest_level === 'not_interested') {
                    // No more follow-up needed - DELETE the old follow-up record
                    $stmt = $pdo->prepare("DELETE FROM call_records WHERE id = ?");
                    $stmt->execute([$followup_id]);

                    // Insert the new call record (for history)
                    $stmt = $pdo->prepare("INSERT INTO call_records (user_id, employee_id, notes, interest_level, follow_up_date, recording_file) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$user_id, $employee_id, $notes, $interest_level, null, $recording_file]);

                } elseif ($interest_level === 'follow_up' && $follow_up_date) {
                    // UPDATE the existing follow-up record with new date (NO new record)
                    $stmt = $pdo->prepare("UPDATE call_records SET follow_up_date = ?, notes = CONCAT(notes, '\n\n[Follow-up rescheduled from ', follow_up_date, ' to ', ?, ' on ', NOW(), ']'), called_at = NOW() WHERE id = ?");
                    $stmt->execute([$follow_up_date, $follow_up_date, $followup_id]);
                }
            } else {
                // No existing record, just insert new
                $stmt = $pdo->prepare("INSERT INTO call_records (user_id, employee_id, notes, interest_level, follow_up_date, recording_file) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $employee_id, $notes, $interest_level, $follow_up_date, $recording_file]);
            }
        } else {
            // Normal call (not from follow-up)
            $stmt = $pdo->prepare("INSERT INTO call_records (user_id, employee_id, notes, interest_level, follow_up_date, recording_file) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $employee_id, $notes, $interest_level, $follow_up_date, $recording_file]);
        }

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error in addCallRecord: " . $e->getMessage());
        return false;
    }
}

function getCallRecordsByEmployee($employee_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT cr.*, u.name as user_name, u.phone, u.company 
                           FROM call_records cr 
                           JOIN users u ON cr.user_id = u.id 
                           WHERE cr.employee_id = ? 
                           ORDER BY cr.called_at DESC");
    $stmt->execute([$employee_id]);
    return $stmt->fetchAll();
}

function getAllCallRecords()
{
    global $pdo;
    $stmt = $pdo->query("SELECT cr.*, u.name as user_name, u.phone, u.company, e.name as employee_name 
                         FROM call_records cr 
                         JOIN users u ON cr.user_id = u.id 
                         JOIN employees e ON cr.employee_id = e.id 
                         ORDER BY cr.called_at DESC");
    return $stmt->fetchAll();
}

function getFollowUpsByEmployee($employee_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT cr.*, u.name as user_name, u.phone, u.company 
                           FROM call_records cr 
                           JOIN users u ON cr.user_id = u.id 
                           WHERE cr.employee_id = ? AND cr.interest_level = 'follow_up' AND cr.follow_up_date >= CURDATE()
                           ORDER BY cr.follow_up_date ASC");
    $stmt->execute([$employee_id]);
    return $stmt->fetchAll();
}

function getAllFollowUps()
{
    global $pdo;
    $stmt = $pdo->query("SELECT cr.*, u.name as user_name, e.name as employee_name 
                         FROM call_records cr 
                         JOIN users u ON cr.user_id = u.id 
                         JOIN employees e ON cr.employee_id = e.id 
                         WHERE cr.interest_level = 'follow_up' AND cr.follow_up_date >= CURDATE()
                         ORDER BY cr.follow_up_date ASC");
    return $stmt->fetchAll();
}

// ============ DASHBOARD STATS ============

function getAdminStats()
{
    global $pdo;

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalCustomers = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE role = 'employee'");
    $totalEmployees = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM call_records");
    $totalCalls = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM call_records WHERE interest_level = 'follow_up' AND follow_up_date >= CURDATE()");
    $pendingFollowUps = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE assigned_to IS NULL");
    $unassignedCustomers = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM call_records WHERE called_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recentCalls = $stmt->fetchColumn();

    return [
        'total_customers' => $totalCustomers,
        'total_employees' => $totalEmployees,
        'total_calls' => $totalCalls,
        'pending_follow_ups' => $pendingFollowUps,
        'unassigned_customers' => $unassignedCustomers,
        'recent_calls' => $recentCalls
    ];
}

function getEmployeeStats($employee_id)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE assigned_to = ?");
    $stmt->execute([$employee_id]);
    $assignedCustomers = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM call_records WHERE employee_id = ?");
    $stmt->execute([$employee_id]);
    $totalCalls = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM call_records WHERE employee_id = ? AND interest_level = 'follow_up' AND follow_up_date >= CURDATE()");
    $stmt->execute([$employee_id]);
    $pendingFollowUps = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM call_records WHERE employee_id = ? AND DATE(called_at) = CURDATE()");
    $stmt->execute([$employee_id]);
    $todayCalls = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM call_records WHERE employee_id = ? AND interest_level = 'interested'");
    $stmt->execute([$employee_id]);
    $interestedCalls = $stmt->fetchColumn();

    return [
        'assigned_customers' => $assignedCustomers,
        'total_calls' => $totalCalls,
        'pending_follow_ups' => $pendingFollowUps,
        'today_calls' => $todayCalls,
        'interested_calls' => $interestedCalls
    ];
}

// ============ SEARCH FUNCTIONS ============

function searchUsers($keyword)
{
    global $pdo;
    $keyword = "%$keyword%";
    $stmt = $pdo->prepare("SELECT u.*, e.name as assigned_employee_name 
                           FROM users u 
                           LEFT JOIN employees e ON u.assigned_to = e.id 
                           WHERE u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.company LIKE ?
                           ORDER BY u.created_at DESC");
    $stmt->execute([$keyword, $keyword, $keyword, $keyword]);
    return $stmt->fetchAll();
}

// Get users assigned to employee grouped by date
function getUsersByEmployeeGroupedByDate($employee_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE assigned_to = ? ORDER BY created_at DESC");
    $stmt->execute([$employee_id]);
    $users = $stmt->fetchAll();

    $grouped = [];
    foreach ($users as $user) {
        $date = $user['created_at'];
        if (!isset($grouped[$date])) {
            $grouped[$date] = [];
        }
        $grouped[$date][] = $user;
    }
    return $grouped;
}

// Get all call records with filters (admin)
function getAllCallRecordsFiltered($employee_id = '', $interest_level = '', $date_from = '', $date_to = '', $search = '')
{
    global $pdo;

    $sql = "SELECT cr.*, u.name as user_name, u.phone, u.company, e.name as employee_name 
            FROM call_records cr 
            JOIN users u ON cr.user_id = u.id 
            JOIN employees e ON cr.employee_id = e.id 
            WHERE 1=1";
    $params = [];

    if (!empty($employee_id)) {
        $sql .= " AND cr.employee_id = ?";
        $params[] = $employee_id;
    }

    if (!empty($interest_level)) {
        $sql .= " AND cr.interest_level = ?";
        $params[] = $interest_level;
    }

    if (!empty($date_from)) {
        $sql .= " AND DATE(cr.called_at) >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $sql .= " AND DATE(cr.called_at) <= ?";
        $params[] = $date_to;
    }

    if (!empty($search)) {
        $sql .= " AND (u.name LIKE ? OR cr.notes LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
    }

    $sql .= " ORDER BY cr.called_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Get all follow-ups with filters (admin)
function getAllFollowUpsFiltered($employee_id = '', $date_from = '', $date_to = '', $status = 'pending', $search = '')
{
    global $pdo;

    $sql = "SELECT cr.*, u.name as user_name, u.phone, u.company, e.name as employee_name 
            FROM call_records cr 
            JOIN users u ON cr.user_id = u.id 
            JOIN employees e ON cr.employee_id = e.id 
            WHERE cr.interest_level = 'follow_up'";
    $params = [];

    if ($status === 'pending') {
        $sql .= " AND cr.follow_up_date >= CURDATE()";
    } elseif ($status === 'completed') {
        $sql .= " AND cr.interest_level = 'completed'";
    }

    if (!empty($employee_id)) {
        $sql .= " AND cr.employee_id = ?";
        $params[] = $employee_id;
    }

    if (!empty($date_from)) {
        $sql .= " AND cr.follow_up_date >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $sql .= " AND cr.follow_up_date <= ?";
        $params[] = $date_to;
    }

    if (!empty($search)) {
        $sql .= " AND (u.name LIKE ? OR cr.notes LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
    }

    $sql .= " ORDER BY cr.follow_up_date ASC, cr.called_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Get employee follow-ups with filters
function getEmployeeFollowUpsFiltered($employee_id, $status = 'pending', $date_from = '', $date_to = '', $search = '')
{
    global $pdo;

    // Get all follow-ups (including overdue)
    $sql = "SELECT cr.*, u.name as user_name, u.phone, u.company 
            FROM call_records cr 
            JOIN users u ON cr.user_id = u.id 
            WHERE cr.employee_id = ? 
            AND cr.interest_level = 'follow_up' 
            AND cr.follow_up_date IS NOT NULL";
    $params = [$employee_id];

    // For 'pending' status, show only future and today (not overdue)
    if ($status === 'pending') {
        // Show follow-ups with date >= today (including today)
        $sql .= " AND cr.follow_up_date >= CURDATE()";
    }
    // For 'all' status, show everything including overdue

    if (!empty($date_from)) {
        $sql .= " AND cr.follow_up_date >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $sql .= " AND cr.follow_up_date <= ?";
        $params[] = $date_to;
    }

    if (!empty($search)) {
        $sql .= " AND (u.name LIKE ? OR cr.notes LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
    }

    $sql .= " ORDER BY cr.follow_up_date ASC, cr.called_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    // For 'all' status, return everything (including overdue)
    // For 'pending', we already filtered above
    return $results;
}

// Get users assigned to employee with their latest call status
function getUsersByEmployeeWithStatus($employee_id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT u.*, 
            (SELECT interest_level FROM call_records WHERE user_id = u.id ORDER BY called_at DESC LIMIT 1) as last_call_status,
            (SELECT follow_up_date FROM call_records WHERE user_id = u.id ORDER BY called_at DESC LIMIT 1) as last_follow_up_date,
            (SELECT called_at FROM call_records WHERE user_id = u.id ORDER BY called_at DESC LIMIT 1) as last_call_date
        FROM users u 
        WHERE u.assigned_to = ? 
        ORDER BY u.assignment_date DESC, u.created_at DESC
    ");
    $stmt->execute([$employee_id]);
    return $stmt->fetchAll();
}

// Get users assigned to employee grouped by assignment date with status
function getUsersByEmployeeGroupedByAssignmentDate($employee_id)
{
    $users = getUsersByEmployeeWithStatus($employee_id);

    $grouped = [];
    foreach ($users as $user) {
        $date = $user['assignment_date'] ?? $user['created_at'];
        if (!isset($grouped[$date])) {
            $grouped[$date] = [];
        }

        // Determine customer status
        $status = getCustomerCallStatus($user);
        $user['call_status'] = $status['status'];
        $user['status_label'] = $status['label'];
        $user['status_color'] = $status['color'];
        $user['button_text'] = $status['button_text'];
        $user['button_action'] = $status['button_action'];
        $user['button_class'] = $status['button_class'];

        $grouped[$date][] = $user;
    }
    return $grouped;
}

// Get customer call status based on last call record
function getCustomerCallStatus($customer)
{
    $last_status = $customer['last_call_status'] ?? null;
    $follow_up_date = $customer['last_follow_up_date'] ?? null;
    $today = date('Y-m-d');

    // Debug: Log the status for troubleshooting
    // error_log("Customer: " . $customer['name'] . " - Last Status: " . $last_status);

    // No call ever made
    if ($last_status === null || $last_status === '') {
        return [
            'status' => 'pending',
            'label' => '⚪ Not Called Yet',
            'color' => 'secondary',
            'button_text' => 'Call Now',
            'button_action' => 'call',
            'button_class' => 'btn-primary'
        ];
    }

    // Interested - no more calls needed
    if ($last_status === 'interested') {
        return [
            'status' => 'interested',
            'label' => '✅ Interested',
            'color' => 'success',
            'button_text' => 'View Details',
            'button_action' => 'view',
            'button_class' => 'btn-info'
        ];
    }

    // Not Interested - no more calls needed
    if ($last_status === 'not_interested') {
        return [
            'status' => 'not_interested',
            'label' => '❌ Not Interested',
            'color' => 'danger',
            'button_text' => 'View Details',
            'button_action' => 'view',
            'button_class' => 'btn-secondary'
        ];
    }

    // Follow Up
    if ($last_status === 'follow_up') {
        if ($follow_up_date && $follow_up_date < $today) {
            return [
                'status' => 'follow_up_overdue',
                'label' => '⚠️ Follow Up (Overdue)',
                'color' => 'danger',
                'button_text' => 'Call Now',
                'button_action' => 'call',
                'button_class' => 'btn-danger'
            ];
        } elseif ($follow_up_date && $follow_up_date == $today) {
            return [
                'status' => 'follow_up_today',
                'label' => '🔴 Follow Up (Today!)',
                'color' => 'danger',
                'button_text' => 'Call Now',
                'button_action' => 'call',
                'button_class' => 'btn-danger'
            ];
        } elseif ($follow_up_date) {
            return [
                'status' => 'follow_up',
                'label' => '🔄 Follow Up on ' . date('d M Y', strtotime($follow_up_date)),
                'color' => 'warning',
                'button_text' => 'Call Again',
                'button_action' => 'call',
                'button_class' => 'btn-warning'
            ];
        }
    }

    // Default - allow call
    return [
        'status' => 'pending',
        'label' => '📞 Call Pending',
        'color' => 'secondary',
        'button_text' => 'Call Now',
        'button_action' => 'call',
        'button_class' => 'btn-primary'
    ];
}
// Get call records for a specific customer by employee
function getCallRecordsByCustomer($customer_id, $employee_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM call_records WHERE user_id = ? AND employee_id = ? ORDER BY called_at DESC");
    $stmt->execute([$customer_id, $employee_id]);
    return $stmt->fetchAll();
}

// Get single call record by ID with joins
function getCallRecordById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT cr.*, 
               u.name as user_name, 
               u.email as user_email, 
               u.phone as user_phone,
               u.company as user_company,
               u.address as user_address,
               e.name as employee_name,
               e.email as employee_email,
               e.phone as employee_phone
        FROM call_records cr 
        JOIN users u ON cr.user_id = u.id 
        JOIN employees e ON cr.employee_id = e.id 
        WHERE cr.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ============ ASSIGNED CUSTOMERS PAGINATED FOR EMPLOYEE VIEW ============

function getAssignedCustomersPaginated($employee_id, $search = '', $limit = 10, $offset = 0)
{
    global $pdo;

    $employee_id = (int) $employee_id;
    $limit = (int) $limit;
    $offset = (int) $offset;

    $sql = "SELECT * FROM users WHERE assigned_to = :employee_id";
    $params = [':employee_id' => $employee_id];

    if (!empty($search)) {
        $sql .= " AND (name LIKE :search1 OR email LIKE :search2 OR phone LIKE :search3 OR company LIKE :search4)";
        $searchTerm = "%$search%";
        $params[':search1'] = $searchTerm;
        $params[':search2'] = $searchTerm;
        $params[':search3'] = $searchTerm;
        $params[':search4'] = $searchTerm;
    }

    $sql .= " ORDER BY assignment_date DESC, created_at DESC LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
function getAssignedCustomersCount($employee_id, $search = '')
{
    global $pdo;

    // Force integer
    $employee_id = (int) $employee_id;

    $sql = "SELECT COUNT(*) FROM users WHERE assigned_to = ?";
    $params = [$employee_id];

    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR company LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

?>