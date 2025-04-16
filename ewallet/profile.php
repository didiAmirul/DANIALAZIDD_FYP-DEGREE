<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';

// Get user details including mobile number
$stmt = $conn->prepare("SELECT username, email, mobile, form_level, class, balance, profile_pic, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - NFC Wallet</title>
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
        .profile-header {
            background-color: #1E3A8A;
            color: white;
            padding: 1rem;
            flex-grow: 1;
            text-align: center;
            margin: 0;
            width: 100%;
            border-radius: 0 0 20px 20px;
            position: relative;
        }
        .profile-container {
            background: white;
            max-width: 400px;
            margin: 1.5rem auto;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-align: left;
        }
        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: block;
            margin: 0 auto 1rem;
        }
        .profile-info h3 {
            color: #1E3A8A;
            margin-bottom: 0.5rem;
        }
        .profile-info p {
            background: #E3E7F1;
            padding: 0.8rem;
            border-radius: 8px;
            font-size: 1rem;
        }
        .profile-actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            margin-top: 1.5rem;
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
        }
        .btn-logout {
            background: #D32F2F;
        }
    </style>
</head>
<body>

    <header class="profile-header">
        <h1>Profile</h1>
    </header>

    <div class="profile-container">
        <img src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'images/default-profile.png'; ?>" alt="Profile Picture" class="profile-image">

        <div class="profile-info">
            <h3>Username:</h3>
            <p><?php echo htmlspecialchars($user['username']); ?></p>

            <h3>Email:</h3>
            <p><?php echo htmlspecialchars($user['email']); ?></p>

            <h3>Mobile Number:</h3>
            <p><?php echo htmlspecialchars($user['mobile']); ?></p>

            <h3>Form Level:</h3>
            <p>Form <?php echo $user['form_level']; ?> - Class <?php echo htmlspecialchars($user['class']); ?></p>

            <h3>Available Balance:</h3>
            <p><?php echo number_format($user['balance'], 2); ?> Points</p>

            <h3>Account Created:</h3>
            <p><?php echo date("F j, Y", strtotime($user['created_at'])); ?></p>
        </div>

        <div class="profile-actions">
            <a href="edit_profile.php" class="btn">Edit Profile</a>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
    </div>

</body>
</html>
