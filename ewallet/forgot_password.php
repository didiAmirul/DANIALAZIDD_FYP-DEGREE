<?php
require_once 'db.php';
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);

    if (!empty($username)) {
        $stmt = $conn->prepare("SELECT email FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $email = $row['email'];
        } else {
            $message = "Username not found.";
            $stmt->close();
        }
    }

    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $username = $row['username'];
            $reset_link = "http://192.168.56.1/ewallet/reset_password.php?user=" . urlencode($username);

            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                // SMTP settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ddamirul10@gmail.com'; // Your Gmail
                $mail->Password = 'dncu vkam ysgh gnae';  // Your Gmail App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Bypass SSL verification (FOR TESTING ONLY)
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                // Email content
                $mail->setFrom('hhharith555@gmail.com', 'E-Wallet Support');
                $mail->addAddress($email);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "Hello $username,\n\nClick the link below to reset your password:\n$reset_link\n\nIf you didn't request this, please ignore this email.";

                $mail->send();
                $message = "A password reset link has been sent to your email.";
            } catch (Exception $e) {
                $message = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Email not found.";
        }
        $stmt->close();
    } else {
        $message = "Please enter your username or email.";
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
            <p><i>Enter your username or email to receive a password reset link.</i></p>

            <?php if (!empty($message)) { echo "<p class='message'>$message</p>"; } ?>

            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username">
            </div>

            <p>OR</p>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email">
            </div>

            <button type="submit">Send Reset Link</button>

            <p><a href="login.php">Back to Login</a></p>
        </form>
    </div>
</body>
</html>
