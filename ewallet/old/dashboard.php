<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';

// Get user details
$stmt = $conn->prepare("SELECT username, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Wallet - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700">
</head>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const nfcButton = document.getElementById("nfc-pay");
    const nfcResult = document.getElementById("nfc-result");

    if ("NDEFReader" in window) {
        nfcButton.addEventListener("click", async () => {
            try {
                const ndef = new NDEFReader();
                await ndef.scan();
                ndef.onreading = event => {
                    const decoder = new TextDecoder();
                    let nfcData = decoder.decode(event.message.records[0].data);

                    nfcResult.textContent = `Processing payment...`;

                    // Send NFC data to server
                    fetch("nfc_process_payment.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `nfc_data=${encodeURIComponent(nfcData)}`
                    })
                    .then(response => response.text())
                    .then(data => {
                        nfcResult.innerHTML = data;
                    });
                };
            } catch (error) {
                nfcResult.textContent = "NFC scanning failed or not supported.";
            }
        });
    } else {
        nfcResult.textContent = "NFC not supported on this device.";
    }
});
</script>


<body>

    <!-- Top Section: Greeting & Premium Banner -->
    <!-- <header class="dashboard-header">
    <div class="user-info">
        <h2>Hello, <?php echo htmlspecialchars($user['username']); ?>!</h2>
        <p>Available Balance: <strong><?php echo number_format($user['balance'], 2); ?> points</strong></p>
    </div>
    <div class="nfc-banner">
        <button id="nfc-scan">📡 Pay with NFC</button>
        <p id="nfc-result">Tap your phone</p>
    </div>
</header> -->

<!-- NFC Tap to Pay Section -->
    <header class="dashboard-header">
        <div class="user-info">
            <h2>Hello, <?php echo htmlspecialchars($user['username']); ?>!</h2>
         <p>Available Balance: <strong><?php echo number_format($user['balance'], 2); ?> points</strong></p>
        </div>
        <div class="nfc-banner">
            <button id="nfc-pay">📡 Tap to Pay</button>
            <p id="nfc-result">Tap your phone to start payment</p>
        </div>
    </header>


    <!-- Features Section -->
    <div class="container">
        <h3>Features</h3>
        <div class="features-grid">
            <a href="topup.php" class="feature-item">💰 Top Up</a>
            <a href="pay.php" class="feature-item">💳 Pay</a>
            <a href="send.php" class="feature-item">📤 Transfer</a>
            <a href="canteen.php" class="feature-item">🛍️ Canteen</a>
            <a href="bookstore.php" class="feature-item">🧾 Book Store</a>
            <a href="more.php" class="feature-item">➕ More</a>
        </div>

        <!-- Promotions Section -->
        <h3>Promo</h3>
        <div class="promo-grid">
            <div class="promo-item">💰 Bookstore<br><small>Get 10% off for workbooks</small></div>
            <div class="promo-item">💲 Co-op<br><small>Get 10 points for each purchase</small></div>
        </div>

        <!-- Recent Transactions Section -->
        <h3>Recent Transactions</h3>
        <div class="transactions">
            <?php
            // Fetch last 5 transactions from the database
            $stmt = $conn->prepare("SELECT type, amount, description, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $transactions = $stmt->get_result();

            if ($transactions->num_rows > 0):
                while ($row = $transactions->fetch_assoc()): ?>
                    <div class="transaction-item">
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="amount" style="color: <?php echo ($row['amount'] < 0) ? 'red' : 'green'; ?>">
                            <?php echo ($row['amount'] < 0) ? "- " : "+ "; ?>
                            <?php echo number_format(abs($row['amount']), 2); ?> points
                        </p>
                        <small><?php echo $row['created_at']; ?></small>
                    </div>
            <?php endwhile;
            else: ?>
                <p>No recent transactions.</p>
            <?php endif; ?>
        </div>
        <a href="transaction_history.php" class="view-all">View All Transactions</a>

        </div>

    <!-- Bottom Navigation Bar -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item active">🏠 Home</a>
        <a href="search.php" class="nav-item">🔍 Search</a>
        <a href="profile.php" class="nav-item">👤 Profile</a>
    </nav>

    <script>
document.addEventListener("DOMContentLoaded", () => {
    const nfcButton = document.getElementById("nfc-scan");
    const nfcResult = document.getElementById("nfc-result");

    if ("NDEFReader" in window) {
        nfcButton.addEventListener("click", async () => {
            try {
                const ndef = new NDEFReader();
                await ndef.scan();
                ndef.onreading = event => {
                    const decoder = new TextDecoder();
                    for (const record of event.message.records) {
                        nfcResult.textContent = `Scanned Data: ${decoder.decode(record.data)}`;
                    }
                };
            } catch (error) {
                nfcResult.textContent = "NFC scanning failed or not supported.";
            }
        });
    } else {
        nfcResult.textContent = "NFC not supported on this device.";
    }
});
</script>


</body>
</html>
