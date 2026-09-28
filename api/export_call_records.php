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
$employee_id = $_GET['employee_id'] ?? '';
$interest_level = $_GET['interest_level'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

$records = getAllCallRecordsFiltered($employee_id, $interest_level, $date_from, $date_to, $search);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Call Records');

// Title
$sheet->setCellValue('A1', 'Call Records Report');
$sheet->setCellValue('A2', 'Export Date: ' . date('Y-m-d H:i:s'));
$sheet->getStyle('A1:A2')->getFont()->setBold(true);

// Headers
$headers = ['#', 'Employee', 'Customer', 'Phone', 'Company', 'Notes', 'Interest Level', 'Follow-up Date', 'Recording', 'Called At'];
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
$sheet->getStyle('A4:J4')->applyFromArray($headerStyle);

// Add data
$row = 5;
$serial = 1;
foreach ($records as $record) {
    $interestText = $record['interest_level'] === 'interested' ? 'Interested' : 
                    ($record['interest_level'] === 'not_interested' ? 'Not Interested' : 'Follow Up');
    
    $sheet->setCellValue('A' . $row, $serial++);
    $sheet->setCellValue('B' . $row, $record['employee_name']);
    $sheet->setCellValue('C' . $row, $record['user_name']);
    $sheet->setCellValue('D' . $row, $record['phone'] ?? '-');
    $sheet->setCellValue('E' . $row, $record['company'] ?? '-');
    $sheet->setCellValue('F' . $row, $record['notes']);
    $sheet->setCellValue('G' . $row, $interestText);
    $sheet->setCellValue('H' . $row, $record['follow_up_date'] ?? '-');
    $sheet->setCellValue('I' . $row, $record['recording_file'] ? 'Yes' : 'No');
    $sheet->setCellValue('J' . $row, $record['called_at']);
    $row++;
}

// Add summary
$sheet->setCellValue('A' . ($row + 1), 'Total Records: ' . count($records));
$sheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);

// Auto-size columns
foreach (range('A', 'J') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = 'call_records_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>