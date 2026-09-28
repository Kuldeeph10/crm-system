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

$employees = getAllEmployeesWithAdmins();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Employees');

// Headers
$headers = ['#', 'Name', 'Email', 'Phone', 'Role', 'Status', 'Created Date'];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Style headers
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2c3e50']]
];
$sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

// Add data
$row = 2;
$serial = 1;
foreach ($employees as $employee) {
    $sheet->setCellValue('A' . $row, $serial++);
    $sheet->setCellValue('B' . $row, $employee['name']);
    $sheet->setCellValue('C' . $row, $employee['email']);
    $sheet->setCellValue('D' . $row, $employee['phone'] ?? '-');
    $sheet->setCellValue('E' . $row, ucfirst($employee['role']));
    $sheet->setCellValue('F' . $row, ucfirst($employee['status']));
    $sheet->setCellValue('G' . $row, date('Y-m-d', strtotime($employee['created_at'])));
    $row++;
}

// Auto-size columns
foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = 'employees_export_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>