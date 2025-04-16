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
  <title>QR Scan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- jsQR from CDN -->
  <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>

  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      margin: 0; 
      padding: 0;
    }
    .container {
      margin-top: 2rem;
    }
    #preview {
      width: 300px;
      height: 300px;
      border: 3px solid #1E3A8A;
      border-radius: 10px;
      object-fit: cover;
    }
    #scan-result {
      margin-top: 1rem;
      font-size: 1.2rem;
      color: #1E3A8A;
      font-weight: bold;
    }
    .btn-cancel {
      display: inline-block;
      margin-top: 1rem;
      padding: 0.8rem 1.5rem;
      color: #fff;
      background: #d9534f;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      border: none;
    }
    .btn-cancel:hover {
      background: #c9302c;
    }
  </style>
</head>
<body>

<div class="container">
  <h1>Scanning QR Code...</h1>
  <!-- Video feed from camera -->
  <video id="preview" autoplay></video>
  <!-- Hidden canvas for decoding frames -->
  <canvas id="qr-canvas" hidden></canvas>

  <!-- Cancel Button -->
  <button class="btn-cancel" onclick="cancelScan()">Cancel</button>
</div>

<script>
  const video = document.getElementById('preview');
  const canvas = document.getElementById('qr-canvas');
  const ctx = canvas.getContext('2d');

  navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
    .then((stream) => {
      video.srcObject = stream;
      video.setAttribute('playsinline', true);
      video.play();
      requestAnimationFrame(scanFrame);
    })
    .catch((err) => {
      console.error('Camera error: ', err);
      alert('Error accessing camera: ' + err);
    });

  function scanFrame() {
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

      const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
      const code = jsQR(imageData.data, imageData.width, imageData.height);

      if (code) {
        const scannedData = code.data;
        if (scannedData.startsWith("http") || scannedData.startsWith("https")) {
          window.location.href = scannedData; // Redirect immediately
        }
      }
    }
    requestAnimationFrame(scanFrame);
  }

  function cancelScan() {
    window.location.href = "dashboard.php";
  }
</script>

</body>
</html>
