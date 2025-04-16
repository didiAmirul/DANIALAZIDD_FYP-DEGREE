<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';
$message = "";

// Check if an admin already exists
$adminExists = false;
$adminCheck = $conn->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1");
if ($adminCheck && $adminCheck->num_rows > 0) {
    $adminExists = true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = trim($_POST['user_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'user';
    $form_level = isset($_POST['form_level']) ? $_POST['form_level'] : null;
    $class = isset($_POST['class']) ? $_POST['class'] : null;

    $password_regex = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&_-])[A-Za-z\d@$!%*?&_-]{8,}$/";

    if (empty($email)) {
        $message = "Email cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (empty($mobile)) {
        $message = "Mobile number cannot be empty.";
    } elseif (!preg_match("/^\\+?[0-9]{8,15}$/", $mobile)) {
        $message = "Invalid mobile number format.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match. Please try again.";
    } elseif (!preg_match($password_regex, $password)) {
        $message = "Password must include an uppercase letter, lowercase letter, number, special character, and be at least 8 characters long.";
    } elseif ($role === 'admin' && $adminExists) {
        $message = "Admin account already exists. Only one admin is allowed.";
    } elseif (!preg_match("/^[0-9]{12}$/", $id)) {
        $message = "IC Number must be exactly 12 digits.";    
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $profile_pic = "";
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $target_file = $target_dir . basename($_FILES['profile_pic']['name']);
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                $profile_pic = $target_file;
            }
        }

        $stmt = $conn->prepare("INSERT INTO users (user_id, username, email, mobile, password, form_level, class, profile_pic, role) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $id, $username, $email, $mobile, $hashed_password, $form_level, $class, $profile_pic, $role);

        if ($stmt->execute()) {
            $message = "<span style='color:#1E3A8A;'>Registration successful! You can now <a href='login.php'>Login</a>.</span>";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - NFC Wallet</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
    function validateForm() {
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirm_password").value;
        const pattern = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&_-])[A-Za-z\d@$!%*?&_-]{8,}$/;

        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            return false;
        }

        if (!pattern.test(password)) {
            alert("Password must include uppercase, lowercase, number, and special character.");
            return false;
        }

        return true;
    }

    function toggleFormOptions() {
        const role = document.getElementById("role").value;
        const formLevel = document.getElementById("form_level");
        const classSelect = document.getElementById("class");

        const isAdmin = role === "admin";

        formLevel.disabled = isAdmin;
        classSelect.disabled = isAdmin;

        if (isAdmin) {
            formLevel.value = "";
            classSelect.value = "";
        }

        document.addEventListener("DOMContentLoaded", function () 
        {
          const icInput = document.querySelector('input[name="user_id"]');

           icInput.addEventListener("input", function () {
          this.value = this.value.replace(/[^0-9]/g, ""); // Hapus semua selain nombor
          });
            });

    }
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === "password") {
            input.type = "text";
            icon.textContent = "👁️";
        } else {
            input.type = "password";
            icon.textContent = "👁️‍🗨️";
        }
    }
</script>

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #F1F5F9;
            margin: 0;
            padding: 0;
        }
        .register-container {
            max-width: 400px;
            margin: 2rem auto;
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
            font-weight: bold;
            margin-bottom: 0.3rem;
        }
        input, select {
            width: 100%;
            padding: 0.7rem;
            border-radius: 5px;
            border: 2px solid #1E3A8A;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 0.9rem;
            font-size: 1.1rem;
            border: none;
            background: #1E3A8A;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 1rem;
        }
        .message {
            padding: 0.7rem;
            margin-bottom: 1rem;
            color: #1E3A8A;
            background: #E0F0FF;
            border-radius: 5px;
            text-align: center;
        }
        .login-link {
            margin-top: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="register-container">
    <form method="post" action="register.php" enctype="multipart/form-data" onsubmit="return validateForm()">
        <h2>Register</h2>
        <p class="register-subtext"><i>Please enter details to register</i></p>

        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

        <div class="form-group">
            <label>Ic Number:</label>
            <input type="text" name="user_id" required pattern="[0-9]{12}" inputmode="numeric" title="IC Number must be 12 digits only">
        </div>

        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
             <label>Password:</label>
             <div style="position: relative;">
             <input type="password" id="password" name="password" required>
             <span onclick="togglePassword('password', 'toggleIcon1')" id="toggleIcon1" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">👁️‍🗨️</span>
         </div>
            </div>

        <div class="form-group">
             <label>Confirm Password:</label>
             <div style="position: relative;">
             <input type="password" id="confirm_password" name="confirm_password" required>
             <span onclick="togglePassword('confirm_password', 'toggleIcon2')" id="toggleIcon2" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">👁️‍🗨️</span>
         </div>
</div>


        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Mobile Number:</label>
            <input type="text" name="mobile" required placeholder="e.g. +60123456789">
        </div>

        <?php if (!$adminExists): ?>
            <div class="form-group">
                <label>Register As:</label>
                <select name="role" id="role" required onchange="toggleFormOptions()">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        <?php else: ?>
            <input type="hidden" name="role" value="user">
        <?php endif; ?>

        <div class="form-group">
            <label>Form Level:</label>
            <select name="form_level" id="form_level">
                <option value="">Select Form</option>
                <option value="1">Form 1</option>
                <option value="2">Form 2</option>
                <option value="3">Form 3</option>
                <option value="4">Form 4</option>
                <option value="5">Form 5</option>
            </select>
        </div>

        <div class="form-group">
            <label>Class:</label>
            <select name="class" id="class">
                <option value="">Select Class</option>
                <option value="A">Class A</option>
                <option value="B">Class B</option>
                <option value="C">Class C</option>
                <option value="D">Class D</option>
            </select>
        </div>

        <div class="form-group">
            <label>Profile Picture:</label>
            <input type="file" name="profile_pic">
        </div>

        <button type="submit">Register</button>
        <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>
</body>
</html>
