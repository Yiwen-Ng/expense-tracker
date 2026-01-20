<?php
// Include database connection
require_once "../config/database.php";

// Read raw JSON input sent from the frontend
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (
    empty($data["description"]) ||
    empty($data["category"]) ||
    empty($data["amount"]) ||
    empty($data["currency"]) ||
    empty($data["transaction_date"])
) 
// Return error if any required field is missing
{
    http_response_code(400);
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

// Prepare SQL statement to prevent SQL injection
$stmt = $conn->prepare(
    "INSERT INTO transactions (description, amount, currency, transaction_date)
     VALUES (?, ?, ?, ?)"
);

// Bind user input to the prepared statement -> s = string and d = double
$stmt->bind_param(
    "sdss",
    $data["description"],
    $data["category"],
    $data["amount"],
    $data["currency"],
    $data["transaction_date"]
);

// Execute the SQL statement
$stmt->execute();

// Return success response
echo json_encode([
    "message" => "Transaction created successfully"
]);