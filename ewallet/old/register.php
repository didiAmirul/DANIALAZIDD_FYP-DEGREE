<?php
session_start();
require_once 'db.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Check if the username is already taken
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $message = "Username already exists. Please choose another.";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password, balance) VALUES (?, ?, 0)");
        $stmt->bind_param("ss", $username, $password_hash);
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Error creating account. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Wallet - Register</title>
    <link rel="stylesheet" href="css/style.css">
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

    <div class="register-container">
        <form method="post" action="register.php">
            <img src="images/logo.png" alt="Logo" class="register-logo">
            <h2>Create an Account</h2>

            <?php if ($message !== "") { echo "<p class='error-message'>$message</p>"; } ?>

            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <span id="eye-icon" onclick="togglePassword()">👁️‍🗨️</span>
            </div>

            <button type="submit">Register</button>

            <p>Already have an account? <a href="login.php">Sign In</a></p>
        </form>
    </div>

</body>
</html>
