<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Check if logged in and is employee
if (!isLoggedIn() || !hasRole('employee')) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$employee_id = $_SESSION['user_id'];
$employee_name = $_SESSION['user_name'];

// Get all call records for this employee
$records = getCallRecordsByEmployee($employee_id);

// Apply filters if provided
$interest_level = $_GET['interest_level'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

if (!empty($interest_level)) {
    $records = array_filter($records, function($r) use ($interest_level) {
        return $r['interest_level'] == $interest_level;
    });
}

if (!empty($date_from)) {
    $records = array_filter($records, function($r) use ($date_from) {
        return substr($r['called_at'], 0, 10) >= $date_from;
    });
}

if (!empty($date_to)) {
    $records = array_filter($records, function($r) use ($date_to) {
        return substr($r['called_at'], 0, 10) <= $date_to;
    });
}

if (!empty($search)) {
    $search_lower = strtolower($search);
    $records = array_filter($records, function($r) use ($search_lower) {
        return strpos(strtolower($r['user_name']), $search_lower) !== false ||
               strpos(strtolower($r['notes'] ?? ''), $search_lower) !== false;
    });
}

$records = array_values($records);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('My Call History');

// Title
$sheet->setCellValue('A1', 'My Call History Report');
$sheet->setCellValue('A2', 'Employee: ' . $employee_name);
$sheet->setCellValue('A3', 'Export Date: ' . date('Y-m-d H:i:s'));
$sheet->getStyle('A1:A3')->getFont()->setBold(true);

// Statistics
$total = count($records);
$interested = count(array_filter($records, function($r) { return $r['interest_level'] == 'interested'; }));
$not_interested = count(array_filter($records, function($r) { return $r['interest_level'] == 'not_interested'; }));
$follow_up = count(array_filter($records, function($r) { return $r['interest_level'] == 'follow_up'; }));

$sheet->setCellValue('A5', 'Statistics:');
$sheet->setCellValue('A6', 'Total Calls:');
$sheet->setCellValue('B6', $total);
$sheet->setCellValue('A7', 'Interested:');
$sheet->setCellValue('B7', $interested);
$sheet->setCellValue('A8', 'Not Interested:');
$sheet->setCellValue('B8', $not_interested);
$sheet->setCellValue('A9', 'Follow Ups:');
$sheet->setCellValue('B9', $follow_up);

// Headers
$headers = ['#', 'Customer', 'Phone', 'Company', 'Notes', 'Interest Level', 'Follow-up Date', 'Recording', 'Called At'];
$col = 'A';
$row = 11;
foreach ($headers as $header) {
    $sheet->setCellValue($col . $row, $header);
    $col++;
}

// Style headers
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '3498db']]
];
$sheet->getStyle('A11:I11')->applyFromArray($headerStyle);

// Add data
$row = 12;
$serial = 1;
foreach ($records as $record) {
    $interestText = $record['interest_level'] === 'interested' ? 'Interested' : 
                    ($record['interest_level'] === 'not_interested' ? 'Not Interested' : 'Follow Up');
    
    $sheet->setCellValue('A' . $row, $serial++);
    $sheet->setCellValue('B' . $row, $record['user_name']);
    $sheet->setCellValue('C' . $row, $record['phone'] ?? '-');
    $sheet->setCellValue('D' . $row, $record['company'] ?? '-');
    $sheet->setCellValue('E' . $row, $record['notes']);
    $sheet->setCellValue('F' . $row, $interestText);
    $sheet->setCellValue('G' . $row, $record['follow_up_date'] ?? '-');
    $sheet->setCellValue('H' . $row, $record['recording_file'] ? 'Yes' : 'No');
    $sheet->setCellValue('I' . $row, $record['called_at']);
    $row++;
}

// Auto-size columns
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = 'my_call_history_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>