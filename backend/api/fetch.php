<?php
require_once "../config/database.php";

// SQL query to fetch all transactions and ordered by latest transaction date first
$result = $conn->query(
    "SELECT id, description, category, amount, currency, transaction_date
     FROM transactions
     ORDER BY transaction_date DESC"
);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
