<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
$message = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = intval($_POST['user_id']);
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $message = "Please enter a valid amount.";
    } else {
        // Check if the user exists
        $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update the user's balance
            $stmt2 = $conn->prepare("UPDATE users SET balance = balance + ? WHERE user_id = ?");
            $stmt2->bind_param("di", $amount, $user_id);
            if ($stmt2->execute()) {
                // Insert a transaction record for the top-up
                $description = "Top-up of $amount points";
                $stmt3 = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'topup', ?, ?)");
                $stmt3->bind_param("ids", $user_id, $amount, $description);
                $stmt3->execute();
                $message = "✅ Top-up successful!";
                $success = true;
            } else {
                $message = "❌ Error processing top-up.";
            }
        } else {
            $message = "❌ User not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Admin - Top Up Points</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #F1F5F9;
            margin: 0;
            padding: 0;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .topup-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        .topup-header {
            background: #1E3A8A;
            color: white;
            padding: 1.5rem;
            border-radius: 15px 15px 0 0;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .topup-message {
            font-size: 1rem;
            font-weight: bold;
            color: green;
            margin-bottom: 1rem;
        }
        label {
            display: block;
            text-align: left;
            font-weight: bold;
            margin-bottom: 0.3rem;
        }
        input[type="number"], input[type="text"] {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #1E3A8A;
            border-radius: 5px;
            font-size: 1rem;
            text-align: left;
            box-sizing: border-box;
            margin-bottom: 1rem;
        }
        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            align-items: center;
        }
        .btn {
            width: 100%;
            max-width: 300px;
            padding: 1rem;
            background: #FFD700;
            color: #1E3A8A;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            display: inline-block;
        }
        .btn:hover {
            background: #FFC107;
        }
        .cancel-btn {
            width: 100%;
            max-width: 300px;
            padding: 1rem;
            background: #D32F2F;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            text-align: center;
            display: inline-block;
        }
        .cancel-btn:hover {
            background: #B71C1C;
        }
    </style>
</head>
<body>
    <div class="topup-container">
        <div class="topup-header">Admin - Top Up Points</div>

        <?php if (!empty($message)) { echo "<p class='topup-message'>$message</p>"; } ?>

        <form method="post" action="topup.php">
            <label>User ID:</label>
            <input type="text" name="user_id" required>

            <label>Amount (Points):</label>
            <input type="number" step="0.01" name="amount" required>

            <div class="btn-container">
                <button type="submit" class="btn">💰 Add Points</button>
                <a href="admin.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
