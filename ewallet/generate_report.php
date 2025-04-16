<?php
require_once 'db.php';

// Set header untuk CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=transaction_report.csv');

$output = fopen('php://output', 'w');

// Header columns
fputcsv($output, ['Transaction ID', 'Username', 'Amount (RM)', 'Type', 'Description', 'Timestamp']);

// Query data
$sql = "SELECT t.id, u.username, t.amount, t.type, t.description, t.created_at 
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['username'],
            number_format($row['amount'], 2),
            ucfirst($row['type']),
            $row['description'],
            date("d-m-Y h:i A", strtotime($row['created_at']))
        ]);
    }
}

fclose($output);
exit();
