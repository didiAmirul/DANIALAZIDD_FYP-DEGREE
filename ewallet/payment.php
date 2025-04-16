<?php
// payment.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_username = $_POST['recipient'];
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $message = "Please enter a valid amount.";
    } else {
        // Verify that the recipient exists and is not the sender
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $recipient_username, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $message = "Recipient not found or invalid.";
        } else {
            $recipient = $result->fetch_assoc();
            // Check if the sender has enough balance
            $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $sender = $result->fetch_assoc();
            
            if ($sender['balance'] < $amount) {
                $message = "Insufficient balance.";
            } else {
                // Begin a transaction
                $conn->begin_transaction();
                try {
                    // Deduct the amount from the sender
                    $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                    $stmt->bind_param("di", $amount, $_SESSION['user_id']);
                    $stmt->execute();

                    // Credit the amount to the recipient
                    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmt->bind_param("di", $amount, $recipient['id']);
                    $stmt->execute();

                    // Record the sender's transaction (payment)
                    $description = "Payment of $amount points to " . $recipient_username;
                    $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'payment', ?, ?)");
                    $stmt->bind_param("ids", $_SESSION['user_id'], $amount, $description);
                    $stmt->execute();

                    // Record the recipient's transaction (receive)
                    $description2 = "Received $amount points from user ID " . $_SESSION['user_id'];
                    $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'receive', ?, ?)");
                    $stmt->bind_param("ids", $recipient['id'], $amount, $description2);
                    $stmt->execute();

                    $conn->commit();
                    $message = "Payment successful!";
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "Error processing payment: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>eWallet - Make Payment / Transfer</title>
</head>
<body>
    <h2>Make Payment / Transfer</h2>
    <?php if ($message !== "") { echo "<p>$message</p>"; } ?>
    <form method="post" action="payment.php">
        <label>
            Recipient Username:
            <input type="text" name="recipient" required />
        </label>
        <br/>
        <label>
            Amount (points):
            <input type="number" step="0.01" name="amount" required />
        </label>
        <br/>
        <!-- For NFC integration, you might auto-fill the recipient field via NFC scanning -->
        <button type="submit">Send Payment</button>
    </form>
    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
