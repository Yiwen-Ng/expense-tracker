<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

require_once "../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data['id']) ||
    !isset($data['description']) ||
    !isset($data['category']) ||
    !isset($data['amount'])
) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$id = (int)$data['id'];
$description = trim($data['description']);
$category = trim($data['category']);
$amount = (float)$data['amount'];

$sql = "
  UPDATE transactions
  SET description = ?, category = ?, amount = ?
  WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdi", $description, $category, $amount, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Update failed"]);
}

$stmt->close();
$conn->close();