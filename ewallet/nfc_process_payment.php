<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit();
}

$user_id = $_SESSION['user_id'];

// If NFC data is received
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nfc_data'])) {
    $nfc_data = $_POST['nfc_data'];

    // Fetch user balance
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $balance = $user['balance'];

    // Payment amount per NFC tap
    $payment_amount = 10;

    if ($balance >= $payment_amount) {
        // Deduct balance
        $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->bind_param("di", $payment_amount, $user_id);
        $stmt->execute();

        // Insert transaction record
        $description = "NFC Payment via Phone";
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'payment', ?, ?)");
        $stmt->bind_param("ids", $user_id, -$payment_amount, $description);
        $stmt->execute();

        echo "✅ Payment successful! $payment_amount points deducted.";
    } else {
        echo "❌ Insufficient balance!";
    }
} else {
    echo "❌ NFC Payment Failed.";
}
?>
