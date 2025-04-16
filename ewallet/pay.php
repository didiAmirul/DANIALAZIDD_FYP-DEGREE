<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

$message = "";
$recipient_name = "Canteen"; // Default recipient
$sender_name = "";

// Fetch sender's name dynamically
$stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $sender = $result->fetch_assoc();
    $sender_name = $sender['username'];
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $recipient_name = $_POST['recipient'];
    $payment_method = $_POST['payment_method'];

    if ($amount <= 0) {
        $message = "Please enter a valid amount.";
    } else {
        $conn->begin_transaction();
        try {
            // Deduct from sender
            $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE user_id = ? AND balance >= ?");
            $stmt->bind_param("dii", $amount, $_SESSION['user_id'], $amount);
            $stmt->execute();
            if ($stmt->affected_rows === 0) {
                throw new Exception("Insufficient balance.");
            }

            // Log transaction as "Payment"
            $descriptionSender = "Payment to $recipient_name via $payment_method";
            $stmt2 = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'payment', ?, ?)");
            $stmt2->bind_param("ids", $_SESSION['user_id'], $amount, $descriptionSender);
            $stmt2->execute();

            // Commit transaction
            $conn->commit();

            // Success Message and Redirect
            $message = "Payment Successful!";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('payment-success').style.display = 'block';
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 3000); // Redirect after 3 seconds
                });
            </script>";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Transaction failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Make a Payment</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #F1F5F9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 400px;
        }
        h2 {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            text-align: left;
            margin-bottom: 0.3rem;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1rem;
            border: 2px solid #1E3A8A;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 1rem;
            background: #1E3A8A;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            margin-top: 1rem;
        }
        button:hover {
            background: #2563EB;
        }
        .cancel-btn {
            display: block;
            width: 50%;
            padding: 0.5rem;
            text-align: center;
            background: #D32F2F;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            margin: 0.5rem auto;
        }
        .cancel-btn:hover {
            background: #B71C1C;
        }
        .success-message {
            display: none;
            padding: 1rem;
            background: #4CAF50;
            color: white;
            border-radius: 5px;
            margin-bottom: 1rem;
            font-weight: bold;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="payment-success" class="success-message">✅ Payment Successful! Redirecting...</div>
        <h2>Make a Payment</h2>
        <?php if ($message !== "") { echo "<p>$message</p>"; } ?>
        <form method="post" action="pay.php">
            <label>Recipient:</label>
            <select name="recipient" required>
                <option value="Canteen">Canteen</option>
                <option value="Bookstore">Bookstore</option>
            </select>

            <label>Amount (Points):</label>
            <input type="number" step="0.01" name="amount" required>

            <label>Payment Method:</label>
            <select name="payment_method" required>
                <option value="Transfer">Transfer</option>
            </select>

            <button type="submit">Proceed Payment</button>
        </form>
        <a href="dashboard.php" class="cancel-btn">Cancel</a>
    </div>
</body>
</html>
