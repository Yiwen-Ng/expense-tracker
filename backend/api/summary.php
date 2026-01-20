<?php
require_once "../config/database.php";

// SQL query to calculate summary information - Total transactions and total amount per currency
$result = $conn->query("
    SELECT currency,
           COUNT(*) AS total_transactions,
           SUM(amount) AS total_amount
    FROM transactions
    GROUP BY currency
");

// Store summary data
$data = [];

// Fetch each summary row
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Return summary as JSON
echo json_encode($data);