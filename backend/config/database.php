<?php
// API returns JSON data
header("Content-Type: application/json");

// Allow requests from other origins
header("Access-Control-Allow-Origin: *");

// Specify which HTTP methods are allowed for this API
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Allow JSON data to be sent in the request headers
header("Access-Control-Allow-Headers: Content-Type");

// Browsers send an OPTIONS request before POST requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database connection details
$host = "localhost";        
$user = "root";             
$pass = "";                 
$db   = "expense_tracker";

// Create a new MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);

// Check if the connection failed
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}