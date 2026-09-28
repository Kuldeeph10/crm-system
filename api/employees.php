<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    sendResponse(false, 'Unauthorized', null, 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ============ GET ALL EMPLOYEES ============
if ($method === 'GET' && $action === 'list') {
    $search = $_GET['search'] ?? '';

    if (!empty($search)) {
        $employees = searchEmployees($search);
    } else {
        $employees = getAllEmployees();
    }

    sendResponse(true, 'Employees retrieved', $employees);
}

// ============ GET SINGLE EMPLOYEE ============
if ($method === 'GET' && $action === 'single') {
    $id = $_GET['id'] ?? 0;

    if (empty($id)) {
        sendResponse(false, 'Employee ID required');
    }

    $employee = getEmployeeById($id);
    if ($employee) {
        sendResponse(true, 'Employee found', $employee);
    } else {
        sendResponse(false, 'Employee not found', null, 404);
    }
}

// ============ CREATE EMPLOYEE ============
if ($method === 'POST' && $action === 'create') {
    $data = $_POST;
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($name)) {
        sendResponse(false, 'Name is required');
    }
    if (empty($email)) {
        sendResponse(false, 'Email is required');
    }
    if (empty($password)) {
        sendResponse(false, 'Password is required');
    }
    if (strlen($password) < 4) {
        sendResponse(false, 'Password must be at least 4 characters');
    }

    // Check if email exists
    $existing = getEmployeeByEmail($email);
    if ($existing) {
        sendResponse(false, 'Email already exists');
    }

    // Check if phone exists
    if (!empty($phone)) {
        $existingPhone = getEmployeeByPhone($phone);
        if ($existingPhone) {
            sendResponse(false, 'Phone number already exists');
        }
    }

    $result = createEmployee($name, $email, $phone, $password, 'employee');
    if ($result) {
        sendResponse(true, 'Employee created successfully');
    } else {
        sendResponse(false, 'Failed to create employee');
    }
}

// ============ UPDATE EMPLOYEE ============
if ($method === 'POST' && $action === 'update') {
    $id = $_POST['id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if (empty($id)) {
        sendResponse(false, 'Employee ID required');
    }
    if (empty($name)) {
        sendResponse(false, 'Name is required');
    }
    if (empty($email)) {
        sendResponse(false, 'Email is required');
    }

    // Check if email exists for other employee
    $existing = getEmployeeByEmail($email);
    if ($existing && $existing['id'] != $id) {
        sendResponse(false, 'Email already exists for another employee');
    }

    // Check if phone exists for other employee
    if (!empty($phone)) {
        $existingPhone = getEmployeeByPhone($phone);
        if ($existingPhone && $existingPhone['id'] != $id) {
            sendResponse(false, 'Phone number already exists for another employee');
        }
    }

    $result = updateEmployee($id, $name, $email, $phone, 'employee', $status);
    if ($result) {
        sendResponse(true, 'Employee updated successfully');
    } else {
        sendResponse(false, 'Failed to update employee');
    }
}

// ============ UPDATE PASSWORD ============
if ($method === 'POST' && $action === 'change_password') {
    $input = $_POST;
    $id = $input['id'] ?? 0;
    $password = $input['password'] ?? '';

    if (empty($id)) {
        sendResponse(false, 'Employee ID required');
    }
    if (empty($password)) {
        sendResponse(false, 'Password is required');
    }
    if (strlen($password) < 4) {
        sendResponse(false, 'Password must be at least 4 characters');
    }

    $result = updateEmployeePassword($id, $password);
    if ($result) {
        sendResponse(true, 'Password updated successfully');
    } else {
        sendResponse(false, 'Failed to update password');
    }
}

// ============ DELETE EMPLOYEE ============
if ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $data);
    $id = $data['id'] ?? $_GET['id'] ?? 0;

    if (empty($id)) {
        sendResponse(false, 'Employee ID required');
    }

    // Check if employee has assigned users
    $assignedUsers = getUsersByEmployee($id);
    if (count($assignedUsers) > 0) {
        sendResponse(false, 'Cannot delete employee with assigned customers. Please reassign customers first.');
    }

    $result = deleteEmployee($id);
    if ($result) {
        sendResponse(true, 'Employee deleted successfully');
    } else {
        sendResponse(false, 'Failed to delete employee');
    }
}

// ============ GET ASSIGNED CUSTOMERS FOR EMPLOYEE ============
if ($method === 'GET' && $action === 'assigned_customers') {
    $employee_id = $_GET['employee_id'] ?? 0;
    $search = $_GET['search'] ?? '';
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    if (empty($employee_id)) {
        sendResponse(false, 'Employee ID required');
    }

    // Verify employee exists
    $employee = getEmployeeById($employee_id);
    if (!$employee) {
        sendResponse(false, 'Employee not found', null, 404);
    }

    $customers = getAssignedCustomersPaginated($employee_id, $search, $limit, $offset);
    $total = getAssignedCustomersCount($employee_id, $search);

    sendResponse(true, 'Assigned customers retrieved', [
        'customers' => $customers,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit)
    ]);
}
?>