<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Check if logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    sendResponse(false, 'Unauthorized', null, 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ============ EXPORT TO EXCEL ============
if ($method === 'GET' && $action === 'export') {
    $type = $_GET['type'] ?? 'all';
    $keyword = $_GET['keyword'] ?? '';
    
    // Get customers based on filter
    if ($type === 'search' && !empty($keyword)) {
        $customers = searchUsers($keyword);
        $filename = 'customers_search_' . date('Y-m-d') . '.xlsx';
    } else {
        $customers = getAllUsers();
        $filename = 'customers_all_' . date('Y-m-d') . '.xlsx';
    }
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set title
    $sheet->setTitle('Customers');
    
    // Headers
    $headers = ['#', 'Name', 'Email', 'Phone', 'Company', 'Address', 'Assigned To', 'Status', 'Created Date'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }
    
    // Style headers
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2c3e50']],
        'alignment' => ['horizontal' => 'center']
    ];
    $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
    
    // Add data
    $row = 2;
    $serial = 1;
    foreach ($customers as $customer) {
        $sheet->setCellValue('A' . $row, $serial++);
        $sheet->setCellValue('B' . $row, $customer['name']);
        $sheet->setCellValue('C' . $row, $customer['email']);
        $sheet->setCellValue('D' . $row, $customer['phone']);
        $sheet->setCellValue('E' . $row, $customer['company']);
        $sheet->setCellValue('F' . $row, $customer['address']);
        $sheet->setCellValue('G' . $row, $customer['assigned_employee_name'] ?? 'Unassigned');
        $sheet->setCellValue('H' . $row, ucfirst($customer['status']));
        $sheet->setCellValue('I' . $row, $customer['created_at']);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Set background for data rows
    $dataStyle = [
        'alignment' => ['vertical' => 'top']
    ];
    $sheet->getStyle('A2:I' . ($row - 1))->applyFromArray($dataStyle);
    
    // Output file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}

// ============ DOWNLOAD TEMPLATE ============
if ($method === 'GET' && $action === 'template') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $sheet->setTitle('Import Template');
    
    // Headers with required indicator
    $sheet->setCellValue('A1', 'Name*');
    $sheet->setCellValue('B1', 'Email');
    $sheet->setCellValue('C1', 'Phone');
    $sheet->setCellValue('D1', 'Company');
    $sheet->setCellValue('E1', 'Address');
    $sheet->setCellValue('F1', 'Created Date (YYYY-MM-DD)');
    
    // Style headers
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '27ae60']]
    ];
    $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
    
    // Sample data
    $sheet->setCellValue('A2', 'John Doe');
    $sheet->setCellValue('B2', 'john@example.com');
    $sheet->setCellValue('C2', '+1234567890');
    $sheet->setCellValue('D2', 'Acme Inc');
    $sheet->setCellValue('E2', '123 Main Street');
    $sheet->setCellValue('F2', date('Y-m-d'));
    
    $sheet->setCellValue('A3', 'Jane Smith');
    $sheet->setCellValue('B3', 'jane@example.com');
    $sheet->setCellValue('C3', '+0987654321');
    $sheet->setCellValue('D3', 'Tech Corp');
    $sheet->setCellValue('E3', '456 Oak Avenue');
    $sheet->setCellValue('F3', date('Y-m-d'));
    
    // Instructions
    $sheet->setCellValue('A5', 'Instructions:');
    $sheet->setCellValue('A6', '1. Name column is required');
    $sheet->setCellValue('A7', '2. Date format must be YYYY-MM-DD (e.g., 2026-06-04)');
    $sheet->setCellValue('A8', '3. If date is empty, today\'s date will be used');
    $sheet->setCellValue('A9', '4. Do not modify the header row');
    
    $sheet->getStyle('A5:A9')->getFont()->setItalic(true);
    $sheet->getStyle('A5:A9')->getFont()->setSize(10);
    
    // Color the required column
    $sheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('e74c3c');
    
    // Auto-size columns
    foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'customer_import_template.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}

// ============ IMPORT FROM EXCEL ============
if ($method === 'POST' && $action === 'import') {
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] != 0) {
        sendResponse(false, 'Please upload a valid Excel file');
    }
    
    $file = $_FILES['excel_file']['tmp_name'];
    $extension = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, ['xlsx', 'xls'])) {
        sendResponse(false, 'Please upload an Excel file (.xlsx or .xls)');
    }
    
    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        
        // Remove header row
        array_shift($rows);
        
        $success_count = 0;
        $error_count = 0;
        $errors = [];
        $row_number = 1;
        
        foreach ($rows as $row) {
            $row_number++;
            
            // Skip completely empty rows
            if (empty($row[0]) && empty($row[1]) && empty($row[2]) && empty($row[3])) {
                continue;
            }
            
            $name = trim($row[0] ?? '');
            $email = trim($row[1] ?? '');
            $phone = trim($row[2] ?? '');
            $company = trim($row[3] ?? '');
            $address = trim($row[4] ?? '');
            $created_at = trim($row[5] ?? date('Y-m-d'));
            
            // Validate name
            if (empty($name)) {
                $error_count++;
                $errors[] = "Row $row_number: Name is required";
                continue;
            }
            
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $created_at)) {
                $created_at = date('Y-m-d');
            }
            
            // Insert customer
            $result = createUser($name, $email, $phone, $company, $address, $created_at);
            if ($result) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Row $row_number: Failed to insert - " . $name;
            }
        }
        
        sendResponse(true, "Import completed: $success_count imported, $error_count failed", [
            'success_count' => $success_count,
            'error_count' => $error_count,
            'errors' => $errors
        ]);
        
    } catch (Exception $e) {
        sendResponse(false, 'Error processing file: ' . $e->getMessage());
    }
}
?>