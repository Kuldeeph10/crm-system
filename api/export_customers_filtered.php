<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Check if logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

// Get filter parameters
$assignment = $_GET['assignment'] ?? 'all';
$status = $_GET['status'] ?? 'all';
$employee = $_GET['employee'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'created_desc';

// Get all customers
$allCustomers = getAllUsers();
$filtered = [];

foreach ($allCustomers as $customer) {
    // Assignment filter
    if ($assignment === 'assigned' && !$customer['assigned_to']) continue;
    if ($assignment === 'unassigned' && $customer['assigned_to']) continue;
    
    // Status filter
    if ($status !== 'all' && $customer['status'] !== $status) continue;
    
    // Employee filter
    if ($employee === 'none' && $customer['assigned_to']) continue;
    if ($employee !== 'all' && $employee !== 'none' && $customer['assigned_to'] != $employee) continue;
    
    // Date range filter
    if ($date_from && $customer['created_at'] < $date_from) continue;
    if ($date_to && $customer['created_at'] > $date_to) continue;
    
    // Search filter
    if ($search) {
        $search_lower = strtolower($search);
        if (!str_contains(strtolower($customer['name']), $search_lower) &&
            !str_contains(strtolower($customer['email'] ?? ''), $search_lower) &&
            !str_contains(strtolower($customer['phone'] ?? ''), $search_lower) &&
            !str_contains(strtolower($customer['company'] ?? ''), $search_lower)) {
            continue;
        }
    }
    
    $filtered[] = $customer;
}

// Sort
switch($sort) {
    case 'created_desc':
        usort($filtered, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });
        break;
    case 'created_asc':
        usort($filtered, function($a, $b) { return strtotime($a['created_at']) - strtotime($b['created_at']); });
        break;
    case 'name_asc':
        usort($filtered, function($a, $b) { return strcmp($a['name'], $b['name']); });
        break;
    case 'name_desc':
        usort($filtered, function($a, $b) { return strcmp($b['name'], $a['name']); });
        break;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Customers');

// Title
$sheet->setCellValue('A1', 'Customer Management Report');
$sheet->setCellValue('A2', 'Export Date: ' . date('Y-m-d H:i:s'));
$sheet->getStyle('A1:A2')->getFont()->setBold(true);

// Headers
$headers = ['#', 'Name', 'Email', 'Phone', 'Company', 'Address', 'Assigned To', 'Status', 'Created Date'];
$col = 'A';
$row = 4;
foreach ($headers as $header) {
    $sheet->setCellValue($col . $row, $header);
    $col++;
}

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '3498db']]
];
$sheet->getStyle('A4:I4')->applyFromArray($headerStyle);

// Add data
$row = 5;
$serial = 1;
foreach ($filtered as $customer) {
    $sheet->setCellValue('A' . $row, $serial++);
    $sheet->setCellValue('B' . $row, $customer['name']);
    $sheet->setCellValue('C' . $row, $customer['email'] ?? '-');
    $sheet->setCellValue('D' . $row, $customer['phone'] ?? '-');
    $sheet->setCellValue('E' . $row, $customer['company'] ?? '-');
    $sheet->setCellValue('F' . $row, $customer['address'] ?? '-');
    $sheet->setCellValue('G' . $row, $customer['assigned_employee_name'] ?? 'Unassigned');
    $sheet->setCellValue('H' . $row, ucfirst($customer['status']));
    $sheet->setCellValue('I' . $row, $customer['created_at']);
    $row++;
}

// Auto-size columns
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = 'customers_export_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>