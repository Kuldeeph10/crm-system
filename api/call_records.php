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

// ============ GET ALL CALL RECORDS (ADMIN) ============
if ($method === 'GET' && $action === 'all') {
    // Check if admin
    if (!hasRole('admin')) {
        sendResponse(false, 'Access denied. Admin only.', null, 403);
    }
    
    $employee_id = $_GET['employee_id'] ?? '';
    $interest_level = $_GET['interest_level'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $records = getAllCallRecordsFiltered($employee_id, $interest_level, $date_from, $date_to, $search);
    sendResponse(true, 'Call records retrieved', $records);
}

// ============ GET EMPLOYEE CALL RECORDS ============
if ($method === 'GET' && $action === 'my_records') {
    $employee_id = $_SESSION['user_id'];
    $records = getCallRecordsByEmployee($employee_id);
    sendResponse(true, 'My call records retrieved', $records);
}

// ============ GET SINGLE CALL RECORD ============
if ($method === 'GET' && $action === 'single') {
    $id = $_GET['id'] ?? 0;
    
    if (empty($id)) {
        sendResponse(false, 'Call record ID required');
    }
    
    $record = getCallRecordById($id);
    
    // Check permission
    if (!hasRole('admin') && $record['employee_id'] != $_SESSION['user_id']) {
        sendResponse(false, 'Access denied', null, 403);
    }
    
    if ($record) {
        sendResponse(true, 'Call record found', $record);
    } else {
        sendResponse(false, 'Call record not found', null, 404);
    }
}

// ============ DELETE CALL RECORD (ADMIN ONLY) ============
if ($method === 'DELETE') {
    if (!hasRole('admin')) {
        sendResponse(false, 'Access denied. Admin only.', null, 403);
    }
    
    parse_str(file_get_contents('php://input'), $data);
    $id = $data['id'] ?? $_GET['id'] ?? 0;
    
    if (empty($id)) {
        sendResponse(false, 'Call record ID required');
    }
    
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM call_records WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        sendResponse(true, 'Call record deleted successfully');
    } else {
        sendResponse(false, 'Failed to delete call record');
    }
}
?>