<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

$message = "";
$recipient_id = "2";
$recipient_name = "ali";
$sender_name = "";

// Fetch sender's name dynamically
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $sender = $result->fetch_assoc();
    $sender_name = $sender['username'];
}
$stmt->close();

// Check if redirected from QR Scan
if (isset($_GET['qrData'])) {
    $qrData = $_GET['qrData'];

    if (is_numeric($qrData)) {
        $recipient_id = intval($qrData);

        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->bind_param("i", $recipient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $recipient = $result->fetch_assoc();
            $recipient_name = $recipient['username'];
        } else {
            $message = "Recipient not found.";
            $recipient_id = "";
        }
        $stmt->close();
    } else {
        $message = "Invalid QR code data.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($recipient_id)) {
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $message = "Please enter a valid amount.";
    } else {
        $conn->begin_transaction();

        try {
            // Deduct from sender
            $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?");
            $stmt->bind_param("dii", $amount, $_SESSION['user_id'], $amount);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Insufficient balance.");
            }

            // Add to recipient
            $stmt2 = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt2->bind_param("di", $amount, $recipient_id);
            $stmt2->execute();

            if ($stmt2->affected_rows === 0) {
                throw new Exception("Recipient not found.");
            }

            // Log transaction as "Transfer" for sender
            $descriptionSender = "Transfer to $recipient_name";
            $stmt3 = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'transfer', ?, ?)");
            $stmt3->bind_param("ids", $_SESSION['user_id'], $amount, $descriptionSender);
            $stmt3->execute();

            // Log transaction as "Receive" for recipient
            $descriptionRecipient = "Received from $sender_name";
            $stmt4 = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'receive', ?, ?)");
            $stmt4->bind_param("ids", $recipient_id, $amount, $descriptionRecipient);
            $stmt4->execute();

            // Commit transaction
            $conn->commit();

            // Show success message then redirect to dashboard
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('payment-success').style.display = 'block';
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 3000);
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
    <title>Transfer Points</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #F1F5F9;
            margin: 0;
            padding: 0;
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
        input[type="number"] {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1rem;
            border: 2px solid #1E3A8A;
            border-radius: 5px;
            font-size: 1rem;
            text-align: left;
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
            margin: 0.5rem auto; /* Centers horizontally */
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="payment-success" class="success-message">✅ Payment Successful! Redirecting...</div>

        <h2>Transfer Points</h2>
        <?php if ($message !== "") { echo "<p>$message</p>"; } ?>

        <?php if (!empty($recipient_id)): ?>
            <p>Transfer to: <strong><?php echo htmlspecialchars($recipient_name); ?></strong></p>
            <form method="post" action="pay.php?qrData=<?php echo htmlspecialchars($recipient_id); ?>">
                <label>Amount (Points):</label>
                <input type="number" step="0.01" name="amount" required>
                <button type="submit">Transfer</button>
            </form>
        <?php else: ?>
            <p>Invalid recipient. Please scan again.</p>
        <?php endif; ?>

        <!-- Cancel button to return to dashboard -->
        <a href="dashboard.php" class="cancel-btn">Cancel</a>
    </div>
</body>
</html>
