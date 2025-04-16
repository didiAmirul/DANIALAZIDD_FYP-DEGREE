<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>NFC Payment</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .nfc-container {
            text-align: center;
            margin-top: 2rem;
        }

        .nfc-animation {
            position: relative;
            display: inline-block;
            width: 100px;
            height: 100px;
            background: url('images/paynfc.png') no-repeat center;
            background-size: contain;
            animation: nfcTap 1.5s infinite alternate ease-in-out;
        }

        @keyframes nfcTap {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(-10px);
            }
        }

        .tap-instruction {
            font-size: 1rem;
            color: #1E3A8A;
            margin-top: 10px;
            font-weight: bold;
        }

        .cancel-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .cancel-btn {
            width: 100%;
            max-width: 200px;
            padding: 0.8rem;
            text-align: center;
            background: #D32F2F;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="nfc-container">
        <div class="nfc-animation"></div>
        <p class="tap-instruction">Tap your phone on the NFC reader</p>
    </div>

    <div class="cancel-container">
        <button class="cancel-btn" onclick="window.location.href='dashboard.php'">Cancel</button>
    </div>

</body>
</html>
