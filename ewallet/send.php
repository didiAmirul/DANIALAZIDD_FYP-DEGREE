<?php
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

    // Check if recipient exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $stmt->bind_param("si", $recipient_username, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $message = "Recipient not found.";
    } else {
        $recipient = $result->fetch_assoc();

        // Fetch sender's balance
        $stmt = $conn->prepare("SELECT balance FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $sender = $result->fetch_assoc();
        
        if ($sender['balance'] < $amount) {
            $message = "Insufficient balance.";
        } else {
            // Perform transaction
            $conn->begin_transaction();
            try {
                // Deduct from sender
                $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE user_id = ?");
                $stmt->bind_param("di", $amount, $_SESSION['user_id']);
                $stmt->execute();

                // Add to recipient
                $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE user_id = ?");
                $stmt->bind_param("di", $amount, $recipient['id']);
                $stmt->execute();

                // Log transaction for sender
                $desc = "Sent $amount points to " . $recipient_username;
                $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'transfer', ?, ?)");
                $stmt->bind_param("ids", $_SESSION['user_id'], -$amount, $desc);
                $stmt->execute();

                // Log transaction for recipient
                $desc2 = "Received $amount points from user ID " . $_SESSION['user_id'];
                $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'receive', ?, ?)");
                $stmt->bind_param("ids", $recipient['id'], $amount, $desc2);
                $stmt->execute();

                $conn->commit();
                $message = "Transfer successful!";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "Transaction failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>eWallet - Send Points</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Send Points</h1>
    </header>

    <div class="container">
        <h2>Transfer Points</h2>
        <?php if ($message !== "") { echo "<p>$message</p>"; } ?>
        <form method="post" action="send.php">
            <label>Recipient Username:
                <input type="text" name="recipient" required>
            </label>
            <br>
            <label>Amount:
                <input type="number" step="0.01" name="amount" required>
            </label>
            <br>
            <button type="submit">Send</button>
        </form>
    </div>

    <nav>
        <a href="dashboard.php">🏠 Home</a>
    </nav>
</body>
</html>
