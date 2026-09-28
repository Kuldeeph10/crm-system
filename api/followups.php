<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if logged in
if (!isLoggedIn()) {
    sendResponse(false, 'Unauthorized', null, 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ============ GET ADMIN FOLLOW-UPS ============
if ($method === 'GET' && $action === 'all') {
    if (!hasRole('admin')) {
        sendResponse(false, 'Access denied. Admin only.', null, 403);
    }

    $employee_id = $_GET['employee_id'] ?? '';
    $status = $_GET['status'] ?? 'all';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $search = $_GET['search'] ?? '';

    $followups = getAllFollowUpsFiltered($employee_id, $date_from, $date_to, $status, $search);
    sendResponse(true, 'Follow-ups retrieved', $followups);
}

// ============ GET EMPLOYEE FOLLOW-UPS ============
if ($method === 'GET' && $action === 'my_followups') {
    $employee_id = $_SESSION['user_id'];
    $status = $_GET['status'] ?? 'pending';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $search = $_GET['search'] ?? '';

    $followups = getEmployeeFollowUpsFiltered($employee_id, $status, $date_from, $date_to, $search);
    sendResponse(true, 'My follow-ups retrieved', $followups);
}
?>