<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    sendResponse(false, 'Unauthorized', null, 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'calls_last_7days') {
    $labels = [];
    $values = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('D, M j', strtotime($date));
        
        $stmt = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM call_records WHERE DATE(called_at) = ?");
        $stmt->execute([$date]);
        $values[] = (int)$stmt->fetchColumn();
    }
    
    sendResponse(true, 'Chart data retrieved', ['labels' => $labels, 'values' => $values]);
}

if ($method === 'GET' && $action === 'interest_distribution') {
    $stmt = $GLOBALS['pdo']->query("SELECT interest_level, COUNT(*) as count FROM call_records GROUP BY interest_level");
    $results = $stmt->fetchAll();
    
    $labels = [];
    $values = [];
    $labelMap = ['interested' => 'Interested', 'not_interested' => 'Not Interested', 'follow_up' => 'Follow Up'];
    
    foreach ($results as $row) {
        $labels[] = $labelMap[$row['interest_level']] ?? ucfirst($row['interest_level']);
        $values[] = (int)$row['count'];
    }
    
    sendResponse(true, 'Chart data retrieved', ['labels' => $labels, 'values' => $values]);
}

sendResponse(false, 'Invalid action');
?>