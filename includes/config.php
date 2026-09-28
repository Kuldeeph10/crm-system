<?php
// Start session for all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = 'localhost';
$dbname = 'crm_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Auto-detect base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// Remove any subfolders like /includes, /api, /pages/admin from path
$base_uri = str_replace(['/includes', '/api', '/pages/admin', '/pages/employee', '/pages'], '', $uri);
define('BASE_URL', $protocol . '://' . $host . $base_uri . '/');

// For debugging - uncomment to see BASE_URL
// echo "BASE_URL: " . BASE_URL . "<br>";
?>