<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';

// Fetch user details
$stmt = $conn->prepare("SELECT username, email, mobile, form_level, class FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Update user details if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = $_POST['email'];
    $new_mobile = $_POST['mobile'];
    $new_form_level = $_POST['form_level'];

    // Update user data in the database
    $update_stmt = $conn->prepare("UPDATE users SET email = ?, mobile = ?, form_level = ? WHERE user_id = ?");
    $update_stmt->bind_param("ssii", $new_email, $new_mobile, $new_form_level, $_SESSION['user_id']);
    $update_stmt->execute();
    $update_stmt->close();

    // Redirect to profile page after update
    header("Location: profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - NFC Wallet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #F1F5F9;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .edit-profile-header {
            background-color: #1E3A8A;
            color: white;
            padding: 1rem;
            text-align: center;
            margin: 0;
            width: 100%;
            border-radius: 0 0 20px 20px;
        }
        .edit-profile-container {
            background: white;
            max-width: 400px;
            margin: 1.5rem auto;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-align: left;
        }
        .edit-profile-input {
            width: 100%;
            padding: 0.8rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .btn {
            display: block;
            width: 100%;
            max-width: 300px;
            padding: 0.8rem;
            background: #2563EB;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin-top: 1rem;
        }
        .btn-cancel {
            background: #D32F2F;
            margin-top: 1rem;
        }
    </style>
</head>
<body>

    <header class="edit-profile-header">
        <h1>Edit Profile</h1>
    </header>

    <div class="edit-profile-container">
        <form method="POST" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="edit-profile-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="mobile">Mobile Number:</label>
            <input type="text" id="mobile" name="mobile" class="edit-profile-input" value="<?php echo htmlspecialchars($user['mobile']); ?>" required>

            <label>Form Level:</label>
            <select name="form_level" id="form_level">
                <option value="">Select Form</option>
                <option value="1">Form 1</option>
                <option value="2">Form 2</option>
                <option value="3">Form 3</option>
                <option value="4">Form 4</option>
                <option value="5">Form 5</option>
            </select>

            <button type="submit" class="btn">Update Profile</button>
        </form>

        <a href="profile.php" class="btn btn-cancel">Cancel</a>
    </div>

</body>
</html>
