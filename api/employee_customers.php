<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('employee')) {
    sendResponse(false, 'Unauthorized', null, 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'list') {
    $employee_id = $_GET['employee_id'] ?? $_SESSION['user_id'];
    $customers = getUsersByEmployeeWithStatus($employee_id);
    
    $grouped = [];
    foreach ($customers as $customer) {
        $date = $customer['assignment_date'] ?? $customer['created_at'];
        $status = getCustomerCallStatus($customer);
        $customer['call_status'] = $status['status'];
        $customer['status_label'] = $status['label'];
        $customer['status_color'] = $status['color'];
        $customer['button_text'] = $status['button_text'];
        $customer['button_action'] = $status['button_action'];
        $customer['button_class'] = $status['button_class'];
        
        if (!isset($grouped[$date])) {
            $grouped[$date] = [];
        }
        $grouped[$date][] = $customer;
    }
    
    sendResponse(true, 'Customers retrieved', $grouped);
}
?>