<?php
// Debug file to help troubleshoot AJAX requests
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a log file
$logFile = __DIR__ . '/debug_log.txt';
$timestamp = date('Y-m-d H:i:s');

// Log all request information
$data = [
    'time' => $timestamp,
    'method' => $_SERVER['REQUEST_METHOD'],
    'GET' => $_GET,
    'POST' => $_POST,
    'headers' => getallheaders()
];

// Append to log file
file_put_contents($logFile, $timestamp . " - " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Return a success response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Request data logged for debugging',
    'data' => $data
]);
?>
