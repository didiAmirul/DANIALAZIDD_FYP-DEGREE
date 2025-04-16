<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get logged-in user role
$stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$userResult = $stmt->get_result()->fetch_assoc();
$isAdmin = ($userResult['role'] === 'admin');
$stmt->close();

// Determine target user for transaction history
$target_user_id = $_SESSION['user_id'];
if ($isAdmin && isset($_GET['user_id'])) {
    $target_user_id = intval($_GET['user_id']);
}

// Fetch username of the target user
$stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $target_user_id);
$stmt->execute();
$userRow = $stmt->get_result()->fetch_assoc();
$username = $userRow ? $userRow['username'] : "Unknown User";
$stmt->close();

// Get user transactions
$stmt = $conn->prepare("SELECT type, amount, description, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $target_user_id);
$stmt->execute();
$transactions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction History - NFC Wallet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #F1F5F9;
            margin: 0;
            padding: 0;
            text-align: center;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .history-header {
            background: #1E3A8A;
            color: white;
            padding: 1.5rem;
            border-radius: 0 0 20px 20px;
            font-size: 1.5rem;
        }
        .transaction-container {
            background: white;
            max-width: 90%;
            margin: 1.5rem auto;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-align: left;
            flex-grow: 1;
            overflow-y: auto;
            max-height: 70vh;
        }
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #E3E7F1;
        }
        .transaction-item:last-child {
            border-bottom: none;
        }
        .transaction-date {
            font-size: 0.9rem;
            color: #555;
        }
        .transaction-type {
            font-weight: bold;
            color: #1E3A8A;
        }
        .transaction-amount {
            font-weight: bold;
            font-size: 1rem;
        }
        .income {
            color: green;
        }
        .expense {
            color: red;
        }
        .description {
            font-size: 0.9rem;
            color: #333;
        }
        .empty-message {
            font-size: 1.2rem;
            color: #777;
            padding: 2rem;
        }
        .back-btn {
            display: block;
            width: 100%;
            max-width: 300px;
            padding: 0.8rem;
            background: #2563EB;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 1rem auto;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0px 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

    <header class="history-header">
        <?= $isAdmin ? "Transactions for " . htmlspecialchars($username) : "Transaction History" ?>
    </header>

    <div class="transaction-container">
        <?php if ($transactions->num_rows > 0): ?>
            <?php while ($row = $transactions->fetch_assoc()): ?>
                <div class="transaction-item">
                    <div>
                        <div class="transaction-type"><?= ucfirst($row['type']) ?></div>
                        <div class="description"><?= htmlspecialchars($row['description']) ?></div>
                        <div class="transaction-date"><?= date("F j, Y, g:i A", strtotime($row['created_at'])) ?></div>
                    </div>
                        <?php
                         $isPositive = in_array(strtolower($row['type']), ['topup', 'receive']);
                         $sign = $isPositive ? '+' : '-';
                         $class = $isPositive ? 'income' : 'expense';
                         
                            ?>
<div class="transaction-amount <?= $class ?>">
    <?= $sign . ' ' . number_format($row['amount'], 2) ?> Points
</div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-message">No transactions found.</div>
        <?php endif; ?>
    </div>

    <a href="<?= $isAdmin ? 'admin.php' : 'dashboard.php' ?>" class="back-btn">
        ← Back to <?= $isAdmin ? 'Admin Panel' : 'Dashboard' ?>
    </a>

</body>
</html>
