<?php
require_once "../config/database.php";

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (
    empty($data["description"]) ||
    empty($data["category"]) ||
    empty($data["amount"]) ||
    empty($data["currency"]) ||
    empty($data["transaction_date"])
) {
    http_response_code(400);
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

// Prepare SQL
$stmt = $conn->prepare(
    "INSERT INTO transactions (description, category, amount, currency, transaction_date)
     VALUES (?, ?, ?, ?, ?)"
);

// Bind params (s = string, d = double)
$stmt->bind_param(
    "ssdss",
    $data["description"],
    $data["category"],
    $data["amount"],
    $data["currency"],
    $data["transaction_date"]
);

// Execute
$stmt->execute();

// Success response
echo json_encode([
    "message" => "Transaction created successfully"
]);