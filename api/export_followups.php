<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Check if logged in
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$is_admin = hasRole('admin');
$type = $_GET['type'] ?? ($is_admin ? 'admin' : 'employee');

if ($is_admin && $type === 'admin') {
    // Admin export
    $employee_id = $_GET['employee_id'] ?? '';
    $status = $_GET['status'] ?? 'pending';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $followups = getAllFollowUpsFiltered($employee_id, $date_from, $date_to, $status, $search);
    $title = 'All Follow-ups Report';
} else {
    // Employee export
    $employee_id = $_SESSION['user_id'];
    $status = $_GET['status'] ?? 'pending';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $followups = getEmployeeFollowUpsFiltered($employee_id, $status, $date_from, $date_to, $search);
    $title = 'My Follow-ups Report';
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Follow-ups');

// Title
$sheet->setCellValue('A1', $title);
$sheet->setCellValue('A2', 'Export Date: ' . date('Y-m-d H:i:s'));
$sheet->getStyle('A1:A2')->getFont()->setBold(true);

// Headers
if ($is_admin && $type === 'admin') {
    $headers = ['#', 'Employee', 'Customer', 'Phone', 'Company', 'Follow-up Date', 'Notes', 'Called At'];
} else {
    $headers = ['#', 'Customer', 'Phone', 'Company', 'Follow-up Date', 'Notes', 'Called At'];
}
$col = 'A';
$row = 4;
foreach ($headers as $header) {
    $sheet->setCellValue($col . $row, $header);
    $col++;
}

// Style headers
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '3498db']]
];
$colRange = $is_admin && $type === 'admin' ? 'A4:G4' : 'A4:F4';
$sheet->getStyle($colRange)->applyFromArray($headerStyle);

// Add data
$row = 5;
$serial = 1;
foreach ($followups as $f) {
    if ($is_admin && $type === 'admin') {
        $sheet->setCellValue('A' . $row, $serial++);
        $sheet->setCellValue('B' . $row, $f['employee_name']);
        $sheet->setCellValue('C' . $row, $f['user_name']);
        $sheet->setCellValue('D' . $row, $f['phone'] ?? '-');
        $sheet->setCellValue('E' . $row, $f['company'] ?? '-');
        $sheet->setCellValue('F' . $row, $f['follow_up_date']);
        $sheet->setCellValue('G' . $row, $f['notes']);
        $sheet->setCellValue('H' . $row, $f['called_at']);
    } else {
        $sheet->setCellValue('A' . $row, $serial++);
        $sheet->setCellValue('B' . $row, $f['user_name']);
        $sheet->setCellValue('C' . $row, $f['phone'] ?? '-');
        $sheet->setCellValue('D' . $row, $f['company'] ?? '-');
        $sheet->setCellValue('E' . $row, $f['follow_up_date']);
        $sheet->setCellValue('F' . $row, $f['notes']);
        $sheet->setCellValue('G' . $row, $f['called_at']);
    }
    $row++;
}

// Auto-size columns
$lastCol = $is_admin && $type === 'admin' ? 'H' : 'G';
foreach (range('A', $lastCol) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = 'followups_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>