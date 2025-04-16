<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $from_username = $_POST['from_user'];
    $amount = floatval($_POST['amount']);

    // Check if requested user exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $stmt->bind_param("si", $from_username, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $message = "User not found.";
    } else {
        // Insert request into database (For now, just a message)
        $message = "Request sent to " . $from_username . " for " . $amount . " points.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>eWallet - Request Points</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Request Points</h1>
    </header>

    <div class="container">
        <h2>Request Points from a User</h2>
        <?php if ($message !== "") { echo "<p>$message</p>"; } ?>
        <form method="post" action="request.php">
            <label>Request from (Username):
                <input type="text" name="from_user" required>
            </label>
            <br>
            <label>Amount:
                <input type="number" step="0.01" name="amount" required>
            </label>
            <br>
            <button type="submit">Request</button>
        </form>
    </div>

    <nav>
        <a href="dashboard.php">🏠 Home</a>
    </nav>
</body>
</html>
