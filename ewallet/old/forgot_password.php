<?php
session_start();
require_once 'db.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];

    // Check if the user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(50)); // Generate a unique reset token
        $user_id = $user['id'];

        // Store token in database
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
        $stmt->bind_param("si", $token, $user_id);
        $stmt->execute();

        // Create reset link
        $reset_link = "http://localhost/ewallet/reset_password.php?token=$token";

        // Simulating email sending (In real scenario, use mail function)
        $message = "A password reset link has been sent. Click <a href='$reset_link'>here</a> to reset your password.";

        // In real-world: Send $reset_link via email
    } else {
        $message = "Username not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="forgot-container">
        <form method="post" action="forgot_password.php">
            <h2>Forgot Password</h2>
            <?php if ($message !== "") { echo "<p class='info-message'>$message</p>"; } ?>

            <label>Enter your username:</label>
            <input type="text" name="username" required>

            <button type="submit">Send Reset Link</button>

            <p><a href="login.php">Back to Login</a></p>
        </form>
    </div>

</body>
</html>
