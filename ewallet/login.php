<?php
session_start();
require_once 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch id, password and role
    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on role
            if ($row['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $message = "Invalid username or password.";
        }
    } else {
        $message = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Wallet - Login</title>
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
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #F1F5F9;
            margin: 0;
            padding: 0;
        }
        .login-container {
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
        input[type="text"], input[type="password"] {
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
        .forgot-password {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #2563EB;
            text-align: right;
        }
        .login-link {
            margin-top: 1rem;
            text-align: center;
        }
        .error-message {
            padding: 0.7rem;
            background: #FFE0E0;
            color: #D32F2F;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form method="post" action="login.php">
            <h2>Sign In</h2>
            <p class="login-subtext"><i>Please enter your credentials</i></p>

            <?php if ($message !== "") { echo "<p class='error-message'>$message</p>"; } ?>

            <div class="form-group">
                <label>User Name:</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <span id="eye-icon" onclick="togglePassword()">👁️‍🗨️</span>
                </div>
            </div>

            <a href="forgot_password.php" class="forgot-password">Forgot password?</a>

            <button type="submit">Sign In</button>

            <p class="login-link">Don't have an account? <a href="register.php">Register</a></p>
        </form>
    </div>
</body>
</html>
