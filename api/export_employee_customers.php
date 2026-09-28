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
$customers = getUsersByEmployee($employee_id);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('My Customers');

// Set title
$sheet->setCellValue('A1', 'My Customers List');
$sheet->setCellValue('A2', 'Employee: ' . $employee_name);
$sheet->setCellValue('A3', 'Export Date: ' . date('Y-m-d H:i:s'));

// Headers (starting from row 5)
$headers = ['#', 'Name', 'Email', 'Phone', 'Company', 'Address', 'Created Date'];
$col = 'A';
$row = 5;
foreach ($headers as $header) {
    $sheet->setCellValue($col . $row, $header);
    $col++;
}

// Style headers
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '3498db']]
];
$sheet->getStyle('A5:G5')->applyFromArray($headerStyle);

// Add data
$row = 6;
$serial = 1;
foreach ($customers as $customer) {
    $sheet->setCellValue('A' . $row, $serial++);
    $sheet->setCellValue('B' . $row, $customer['name']);
    $sheet->setCellValue('C' . $row, $customer['email']);
    $sheet->setCellValue('D' . $row, $customer['phone']);
    $sheet->setCellValue('E' . $row, $customer['company']);
    $sheet->setCellValue('F' . $row, $customer['address']);
    $sheet->setCellValue('G' . $row, $customer['created_at']);
    $row++;
}

// Add summary
$sheet->setCellValue('A' . ($row + 1), 'Total Customers: ' . count($customers));
$sheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);

// Auto-size columns
foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = 'my_customers_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>