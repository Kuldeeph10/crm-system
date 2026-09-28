<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
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
if ($method === 'GET' && $action === 'employees') {
    $employees = getAllEmployees();
    sendResponse(true, 'Employees retrieved', $employees);
}

// ============ GET UNASSIGNED CUSTOMERS ============
if ($method === 'GET' && $action === 'unassigned') {
    $customers = getUnassignedUsers();
    sendResponse(true, 'Unassigned customers retrieved', $customers);
}

// ============ GET CUSTOMERS BY EMPLOYEE ============
if ($method === 'GET' && $action === 'by_employee') {
    $employee_id = $_GET['employee_id'] ?? 0;

    if (empty($employee_id)) {
        sendResponse(false, 'Employee ID required');
    }

    $customers = getUsersByEmployee($employee_id);
    sendResponse(true, 'Customers retrieved', $customers);
}

// ============ ASSIGN CUSTOMERS TO EMPLOYEE ============
if ($method === 'POST' && $action === 'assign') {
    $employee_id = $_POST['employee_id'] ?? 0;
    $customer_ids = $_POST['customer_ids'] ?? [];

    if (empty($employee_id)) {
        sendResponse(false, 'Please select an employee');
    }

    if (empty($customer_ids)) {
        sendResponse(false, 'Please select at least one customer');
    }

    if (!is_array($customer_ids)) {
        $customer_ids = [$customer_ids];
    }

    $assigned_by = $_SESSION['user_id'];
    $assignment_date = date('Y-m-d');
    $success_count = 0;
    $failed_count = 0;

    foreach ($customer_ids as $customer_id) {
        $result = assignUserToEmployee($customer_id, $employee_id, $assigned_by, $assignment_date);
        if ($result) {
            $success_count++;
        } else {
            $failed_count++;
        }
    }

    sendResponse(true, "Assigned $success_count customers successfully", [
        'success_count' => $success_count,
        'failed_count' => $failed_count
    ]);
}

// ============ UNASSIGN CUSTOMER ============
if ($method === 'POST' && $action === 'unassign') {
    $customer_id = $_POST['customer_id'] ?? 0;

    if (empty($customer_id)) {
        sendResponse(false, 'Customer ID required');
    }

    // Unassign by setting assigned_to to NULL and assignment_date to NULL
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET assigned_to = NULL, assignment_date = NULL WHERE id = ?");
    $result = $stmt->execute([$customer_id]);

    if ($result) {
        sendResponse(true, 'Customer unassigned successfully');
    } else {
        sendResponse(false, 'Failed to unassign customer');
    }
}

// ============ GET ASSIGNMENT SUMMARY ============
if ($method === 'GET' && $action === 'summary') {
    $employees = getAllEmployees();
    $summary = [];

    foreach ($employees as $employee) {
        $customers = getUsersByEmployee($employee['id']);
        $summary[] = [
            'employee' => $employee,
            'customer_count' => count($customers),
            'customers' => $customers
        ];
    }

    sendResponse(true, 'Assignment summary retrieved', $summary);
}
?>