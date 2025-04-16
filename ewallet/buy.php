<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name = $_POST['item_name'];
    $price = floatval($_POST['price']);

    // Fetch user's balance
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user['balance'] < $price) {
        $message = "Insufficient points for this purchase.";
    } else {
        // Deduct from user's balance
        $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->bind_param("di", $price, $_SESSION['user_id']);
        $stmt->execute();

        // Insert transaction record
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'payment', ?, ?)");
        $desc = "Purchased " . $item_name;
        $stmt->bind_param("ids", $_SESSION['user_id'], -$price, $desc);
        $stmt->execute();

        $message = "Purchase successful!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>eWallet - Buy</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Buy Item</h1>
    </header>
    
    <div class="container">
        <h2>Purchase an Item</h2>
        <?php if ($message !== "") { echo "<p>$message</p>"; } ?>
        <form method="post" action="buy.php">
            <label>Item Name:
                <input type="text" name="item_name" required>
            </label>
            <br>
            <label>Price (in points):
                <input type="number" step="0.01" name="price" required>
            </label>
            <br>
            <button type="submit">Buy Now</button>
        </form>
    </div>

    <nav>
        <a href="dashboard.php">🏠 Home</a>
    </nav>
</body>
</html>
