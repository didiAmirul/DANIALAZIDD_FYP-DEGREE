<?php
session_start();
require_once 'db.php';

$message = "";

// Check if username is in the URL
if (isset($_GET['user'])) {
    $username = urldecode($_GET['user']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_password = $_POST['password'];

        // Validate that password is not empty (you can add more rules)
        if (empty($new_password)) {
            $message = "Password cannot be empty.";
        } else {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password in the database
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->bind_param("ss", $hashed_password, $username);

            if ($stmt->execute()) {
                $message = "✅ Password reset successful! <a href='login.php'>Login Now</a>";
            } else {
                $message = "❌ Error resetting password.";
            }

            $stmt->close();
        }
    }
} else {
    // Redirect if accessed without username
    header("Location: forgot_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #F1F5F9;
            margin: 0;
            padding: 0;
        }
        .reset-container {
            max-width: 400px;
            margin: 3rem auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #1E3A8A;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.3rem;
            font-weight: bold;
        }
        input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #1E3A8A;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .password-container {
            position: relative;
        }
        .password-container span {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }
        button {
            width: 100%;
            padding: 1rem;
            background: #1E3A8A;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 1rem;
        }
        .message {
            padding: 0.7rem;
            background: #E0F7FA;
            color: #006064;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>
    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            var icon = document.getElementById("eye-icon");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.innerText = "👁️";
            } else {
                passwordField.type = "password";
                icon.innerText = "👁️‍🗨️";
            }
        }
    </script>
</head>
<body>
    <div class="reset-container">
    <form method="post" action="reset_password.php" onsubmit="return validateForm()">
            <h2>Reset Password</h2>
            <p><i>Enter a new password for your account.</i></p>

            <?php if (!empty($message)) { echo "<p class='message'>$message</p>"; } ?>

            <div class="form-group">
                <label>New Password:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <span id="eye-icon" onclick="togglePassword()">👁️‍🗨️</span>
                </div>
            </div>

            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
