<?php
session_start();
require_once 'db.php';

// Ensure only admin can access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if ($user['role'] !== 'admin') {
    header("Location: admin.php");
    exit();
}
$stmt->close();

// Handle actions: delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $user_id = intval($_POST['user_id']);

    if ($action === 'delete') {
        $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $roleResult = $stmt->get_result()->fetch_assoc();

        if ($roleResult['role'] !== 'admin') {
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
    }
}

// Fetch users
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$stmt = $conn->prepare("SELECT user_id, username, email, mobile, balance, role FROM users WHERE username LIKE ? AND role = 'user' ORDER BY username ASC");
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Admin Dashboard - NFC Wallet</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #F1F5F9;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #1E3A8A;
            color: white;
            padding: 1rem;
            width: 100%;
            border-radius: 0 0 20px 20px;
            position: relative;
        }

        .admin-header .logout-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .admin-header .logout-btn:hover {
            background-color: darkred;
        }

        .admin-header h1 {
            flex-grow: 1;
            text-align: center;
            margin: 0;
        }

        .search-box {
            text-align: center;
            margin-bottom: 1rem;
        }

        .search-box form {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        input[type="text"] {
            padding: 0.6rem;
            border-radius: 5px;
            border: 2px solid #1E3A8A;
            width: 250px;
        }

        .btn {
            padding: 0.6rem 1rem;
            margin: 0.2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .history {
            background: #FFC107;
            color: black;
        }

        .delete {
            background: #D32F2F;
            color: white;
        }

        .reset-btn {
            background: #ccc;
            color: black;
        }

        .create-btns-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .create-btn a {
            background: #4CAF50;
            color: white;
            padding: 0.6rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 0.6rem;
            text-align: center;
        }

        th {
            background: #1E3A8A;
            color: white;
        }

        @media (max-width: 600px) {
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-header h1 {
                margin: 0.5rem 0;
                text-align: center;
                width: 100%;
            }

            .create-btns-container {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>

    <!-- Admin Header with Logout on left -->
    <header class="admin-header">
        <a href="logout.php" class="logout-btn">Logout</a>
        <h1>Admin Dashboard</h1>
    </header>

    <div class="search-box">
        <form method="get">
            <input type="text" name="search" placeholder="Search username...">
            <button type="submit" class="btn">Search</button>
            <a href="admin.php" class="btn reset-btn">Reset List</a>
        </form>
    </div>

    <!--  Topup Buttons Side by Side -->
    <div class="create-btns-container">
       
        <div class="create-btn">
            <a href="topup.php">Topup</a>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Balance</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['user_id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['mobile']) ?></td>
                    <td><?= number_format($user['balance'], 2) ?></td>
                    <td>
                        <a href="transaction_history.php?user_id=<?= $user['user_id'] ?>" class="btn history">Transactions</a>
                        <?php if ($user['role'] !== 'admin'): ?>
                            <form method="post" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <button name="action" value="delete" class="btn delete">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
