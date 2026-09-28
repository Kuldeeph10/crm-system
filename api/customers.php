<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if logged in
if (!isLoggedIn()) {
    sendResponse(false, 'Unauthorized', null, 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Helper function to get customer status with call history
function getCustomerWithStatus($customer) {
    global $pdo;
    
    // If customer is not assigned, return regular status
    if (!$customer['assigned_to']) {
        $customer['display_status'] = $customer['status'] === 'active' ? 'Active' : 'Inactive';
        $customer['status_class'] = $customer['status'] === 'active' ? 'badge-success' : 'badge-danger';
        $customer['status_icon'] = $customer['status'] === 'active' ? '🟢' : '🔴';
        return $customer;
    }
    
    // Get latest call record for this customer
    $stmt = $pdo->prepare("SELECT * FROM call_records WHERE user_id = ? ORDER BY called_at DESC LIMIT 1");
    $stmt->execute([$customer['id']]);
    $last_call = $stmt->fetch();
    
    if (!$last_call) {
        // No call ever made
        $customer['display_status'] = '⚪ Not Called Yet';
        $customer['status_class'] = 'badge-secondary';
        $customer['status_icon'] = '⚪';
        return $customer;
    }
    
    // Determine status based on last call
    $today = date('Y-m-d');
    $follow_up_date = $last_call['follow_up_date'];
    
    switch($last_call['interest_level']) {
        case 'interested':
            $customer['display_status'] = '✅ Interested';
            $customer['status_class'] = 'badge-success';
            $customer['status_icon'] = '✅';
            break;
            
        case 'not_interested':
            $customer['display_status'] = '❌ Not Interested';
            $customer['status_class'] = 'badge-danger';
            $customer['status_icon'] = '❌';
            break;
            
        case 'follow_up':
            if ($follow_up_date && $follow_up_date < $today) {
                $customer['display_status'] = '⚠️ Follow Up (Overdue!)';
                $customer['status_class'] = 'badge-danger';
                $customer['status_icon'] = '⚠️';
            } elseif ($follow_up_date && $follow_up_date == $today) {
                $customer['display_status'] = '🔴 Follow Up (Today!)';
                $customer['status_class'] = 'badge-warning';
                $customer['status_icon'] = '🔴';
            } elseif ($follow_up_date) {
                $customer['display_status'] = '🔄 Follow Up on ' . date('d M Y', strtotime($follow_up_date));
                $customer['status_class'] = 'badge-warning';
                $customer['status_icon'] = '🔄';
            } else {
                $customer['display_status'] = '🔄 Follow Up';
                $customer['status_class'] = 'badge-warning';
                $customer['status_icon'] = '🔄';
            }
            break;
            
        default:
            $customer['display_status'] = '⚪ Not Called Yet';
            $customer['status_class'] = 'badge-secondary';
            $customer['status_icon'] = '⚪';
    }
    
    return $customer;
}

// ============ GET ALL CUSTOMERS (with status) ============
if ($method === 'GET' && $action === 'list') {
    // Check if admin
    if (!hasRole('admin')) {
        sendResponse(false, 'Access denied. Admin only.', null, 403);
    }
    
    $stmt = $pdo->query("SELECT u.*, e.name as assigned_employee_name 
                         FROM users u 
                         LEFT JOIN employees e ON u.assigned_to = e.id 
                         ORDER BY u.created_at DESC");
    $users = $stmt->fetchAll();
    
    $customers_with_status = [];
    foreach ($users as $user) {
        $customers_with_status[] = getCustomerWithStatus($user);
    }
    
    // Group by created date
    $grouped = [];
    foreach ($customers_with_status as $customer) {
        $date = $customer['created_at'];
        if (!isset($grouped[$date])) {
            $grouped[$date] = [];
        }
        $grouped[$date][] = $customer;
    }
    
    sendResponse(true, 'Customers retrieved', $grouped);
}

// ============ GET SINGLE CUSTOMER ============
if ($method === 'GET' && $action === 'single') {
    $id = $_GET['id'] ?? 0;
    
    if (empty($id)) {
        sendResponse(false, 'Customer ID required');
    }
    
    $stmt = $pdo->prepare("SELECT u.*, e.name as assigned_employee_name 
                           FROM users u 
                           LEFT JOIN employees e ON u.assigned_to = e.id 
                           WHERE u.id = ?");
    $stmt->execute([$id]);
    $customer = $stmt->fetch();
    
    if ($customer) {
        $customer = getCustomerWithStatus($customer);
        sendResponse(true, 'Customer found', $customer);
    } else {
        sendResponse(false, 'Customer not found', null, 404);
    }
}

// ============ CREATE CUSTOMER ============
if ($method === 'POST' && $action === 'create') {
    // Check if admin
    if (!hasRole('admin')) {
        sendResponse(false, 'Only admin can create customers', null, 403);
    }
    
    $data = $_POST;
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $company = trim($data['company'] ?? '');
    $address = trim($data['address'] ?? '');
    $created_at = $data['created_at'] ?? date('Y-m-d');
    
    if (empty($name)) {
        sendResponse(false, 'Customer name is required');
    }
    
    $result = createUser($name, $email, $phone, $company, $address, $created_at);
    if ($result) {
        sendResponse(true, 'Customer created successfully');
    } else {
        sendResponse(false, 'Failed to create customer');
    }
}

// ============ UPDATE CUSTOMER ============
if ($method === 'POST' && $action === 'update') {
    // Check if admin
    if (!hasRole('admin')) {
        sendResponse(false, 'Only admin can update customers', null, 403);
    }
    
    $id = $_POST['id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($id) || empty($name)) {
        sendResponse(false, 'Customer ID and name are required');
    }
    
    $result = updateUser($id, $name, $email, $phone, $company, $address, $status);
    if ($result) {
        sendResponse(true, 'Customer updated successfully');
    } else {
        sendResponse(false, 'Failed to update customer');
    }
}

// ============ DELETE CUSTOMER ============
if ($method === 'DELETE') {
    // Check if admin
    if (!hasRole('admin')) {
        sendResponse(false, 'Only admin can delete customers', null, 403);
    }
    
    parse_str(file_get_contents('php://input'), $data);
    $id = $data['id'] ?? $_GET['id'] ?? 0;
    
    if (empty($id)) {
        sendResponse(false, 'Customer ID required');
    }
    
    $result = deleteUser($id);
    if ($result) {
        sendResponse(true, 'Customer deleted successfully');
    } else {
        sendResponse(false, 'Failed to delete customer');
    }
}

// ============ GET UNASSIGNED CUSTOMERS ============
if ($method === 'GET' && $action === 'unassigned') {
    if (!hasRole('admin')) {
        sendResponse(false, 'Access denied. Admin only.', null, 403);
    }
    
    $customers = getUnassignedUsers();
    sendResponse(true, 'Unassigned customers retrieved', $customers);
}

// ============ SEARCH CUSTOMERS ============
if ($method === 'GET' && $action === 'search') {
    if (!hasRole('admin')) {
        sendResponse(false, 'Access denied. Admin only.', null, 403);
    }
    
    $keyword = $_GET['keyword'] ?? '';
    $results = searchUsers($keyword);
    
    // Add status to each result
    $results_with_status = [];
    foreach ($results as $result) {
        $results_with_status[] = getCustomerWithStatus($result);
    }
    
    sendResponse(true, 'Search results', $results_with_status);
}
?>