<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

// Fetch user data
$stmt = $conn->prepare("SELECT username, email, form_level, class, balance, profile_pic FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$is_admin = false;
if ($user && isset($user['email'])) {
    $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $resultRole = $stmt->get_result();
    $roleData = $resultRole->fetch_assoc();
    $is_admin = ($roleData['role'] === 'admin');
    $stmt->close();
}

// Determine online status
$status_color = "red";
if (isset($_SESSION['user_id'])) {
    $status_color = "green";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #F1F5F9;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .dashboard-header {
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
        .dashboard-header h1 {
            flex-grow: 1;
            text-align: center;
            margin: 0;
        }
        #menu-toggle {
            cursor: pointer;
            padding: 0.5rem;
        }
        .menu-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            color: #1E3A8A;
            width: 180px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            border-radius: 5px;
            z-index: 1000;
        }
        .menu-dropdown a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #1E3A8A;
        }
        .menu-dropdown a:hover {
            background: #E2E8F0;
        }
        .user-card {
            background: linear-gradient(135deg, #1E3A8A, #2563EB);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 90%;
            margin: 1rem auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .user-info {
            text-align: left;
            flex-grow: 1;
        }
        .user-profile {
            text-align: right;
        }
        .features-container {
            background: white;
            border-radius: 15px;
            padding: 1rem;
            max-width: 90%;
            margin: 1rem auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.4rem;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
            width: 100%;
        }
        .feature-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: white;
            border: 2px solid #1E3A8A;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            font-size: 0.72rem;
            font-weight: bold;
            text-decoration: none;
            color:rgb(16, 22, 208);
        }

        
        .promo-banner {
            background: #2563EB;
            color: white;
            padding: 1rem;
            text-align: center;
            border-radius: 10px;
            max-width: 90%;
            margin: 1rem auto;
        }
        .qr-frame {
            width: 20px;
            height: 20px;
            border: 3px solid white;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            margin-left: -20px;
            cursor: pointer;
        }
        .qr-frame::before {
            content: "";
            position: absolute;
            width: 80%;
            height: 3px;
            background-color: white;
            top: 50%;
            transform: translateY(-50%);
        }

        .nfc-payment-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0.3rem auto; /* Further reduced gap */
    width: 90%; /* Ensures it matches user card & grid */
    max-width: 400px; /* Adjust to match grid menu */
}

.nfc-payment-btn {
    background-color: #ffc107; /* Yellow */
    color: #1E3A8A; /* Blue Text */
    font-size: 1.2rem;
    font-weight: bold;
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    width: 100%; /* Align width with card & grid */
    transition: background 0.3s ease;
}

.nfc-payment-btn:hover {
    background-color: #ffb300;
}


    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Hamburger menu
            const menuToggle = document.getElementById("menu-toggle");
            const menuDropdown = document.createElement("div");
            menuDropdown.className = "menu-dropdown";
            menuDropdown.innerHTML = `
                <a href='profile.php'>Profile</a>
                <a href='security.php'>Security</a>
                <a href='transaction_history.php'>History</a>
                <a href='pay.php'>Payment</a>
                <a href='logout.php' style='color: red;'>Logout</a>
            `;
            menuToggle.appendChild(menuDropdown);
            menuToggle.addEventListener("click", function() {
                menuDropdown.style.display = menuDropdown.style.display === "block" ? "none" : "block";
            });

            // QR scanning icon
            const qrIcon = document.querySelector(".qr-frame");
            qrIcon.addEventListener("click", function() {
                // alert("Opening QR scanner... (requires camera or library like Instascan)");
                // Redirect to a scanning page (placeholder)
                window.location.href = "qr_scan.php";
            });
        });
    </script>

</head>
<body>
    <header class="dashboard-header">
        <div id="menu-toggle">☰</div>
        <h1>NFC Wallet</h1>
        <div class="qr-frame"></div>
    </header>

    <div class="user-card">
        <div class="user-info">
            <h3><?php echo htmlspecialchars($user['username']); ?></h3>
            <p>Form <?php echo $user['form_level']; ?> - Class <?php echo htmlspecialchars($user['class']); ?></p>
            <p><strong>Available Points:</strong> <?php echo number_format($user['balance'], 2); ?> Points</p>
        </div>
        <div class="user-profile">
            <img src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'images/default-profile.png'; ?>" alt="Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;">
            <div class="status-circle" style="background: <?php echo $status_color; ?>; width: 12px; height: 12px; border-radius: 50%;"></div>
        </div>
    </div>

    <!-- NFC Payment Button -->
    <div class="nfc-payment-container">
        <button class="nfc-payment-btn" onclick="window.location.href='nfc_payment.php'">
            📡 Tap to Pay (NFC)
        </button>
    </div>

    <div class="features-container">
        <div class="features-grid">
            <a href="pay.php" class="feature-item">💳 Pay</a>
            <a href="transfer.php" class="feature-item">🔄 Transfer</a>
            <a href="transaction_history.php" class="feature-item">📜 History</a>
            <a href="canteen.php" class="feature-item">🍔 Canteen</a>
            <a href="bookstore.php" class="feature-item">📚 Bookstore</a>
            <a href="profile.php" class="feature-item">🏷 Profile</a>
            <a href="more_features.php" class="feature-item">⚙️ Other</a>
        </div>
    </div>

    <?php if ($is_admin): ?>
    <div class="nfc-payment-container">
        <button class="nfc-payment-btn" onclick="window.location.href='admin.php'">
            🛠 Admin Panel
        </button>
    </div>
    <?php endif; ?>


    <div class="promo-banner">
        <p>🎟️ Special Promotion: Get 10% extra points on top-ups this week!</p>
    </div>

</body>
</html>
