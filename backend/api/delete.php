<?php
// Include database connection
require_once "../config/database.php";

// Get the raw JSON data
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data["id"])) {
    $id = $data["id"];

    // Prepare the delete statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Transaction deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to delete transaction"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "ID is required"]);
}