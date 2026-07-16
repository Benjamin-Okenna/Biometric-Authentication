<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'db.php';

if(!isset($_SESSION['temp_student_id'])){
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['temp_student_id'];
$stmt = $conn->prepare("SELECT fullname, profile_image FROM students WHERE id =?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Face Verification</title>
  <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
  <style>
    body { font-family: Arial; text-align: center; background: #f4f4f4; }
   .box { margin-top: 40px; }
   .camera-frame { width: 400px; height: 300px; border: 3px solid #4facfe; border-radius: 10px; background: #000; margin: 20px auto; position: relative; overflow: hidden; }
    #video, #overlay { width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; transform: scaleX(-1); }
    #overlay { pointer-events: none; }
    #verifyBtn { padding: 12px 30px; background: #28a745; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; }
    #verifyBtn:disabled { background: #ccc; cursor: not-allowed; }
    #status { margin-top: 10px; font-weight: bold; color: #333; }
  </style>
</head>
<body>
<div class="box">
  <h2>Hello <?php echo $student['fullname'];?>, Verify Your Face</h2>
  <!-- FIXED: Added http://localhost so face-api can fetch it -->
  <img id="savedImage" src="http://localhost/biometric-authentication/student/<?php echo $student['profile_image'];?>" style="display:none;" crossorigin="anonymous">

  <div class="camera-frame">
    <video id="video" autoplay playsinline muted></video>
    <canvas id="overlay"></canvas>
  </div>

  <form action="verify_process.php" method="POST" id="verifyForm">
    <input type="hidden" name="is_verified" id="is_verified" value="0">
    <button type="button" id="verifyBtn" disabled>Loading Models...</button>
    <p id="status">Please wait while we load face models</p>
  </form>
</div>

<script>
const video = document.getElementById('video');
const overlay = document.getElementById('overlay');
const verifyBtn = document.getElementById('verifyBtn');
const status = document.getElementById('status');
const savedImage = document.getElementById('savedImage');
let savedDescriptor = null;
let stream = null;

const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model';

// 1. Load Models
Promise.all([
  faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
  faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
  faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
]).then(start).catch(err => {
  status.innerText = "Failed to load models. Error: " + err.message;
  console.error("Model Load Error:", err);
});

// 2. Start Camera + Get Saved Face Descriptor
async function start() {
  status.innerText = "Loading your saved face...";

  try {
    const img = await faceapi.fetchImage(savedImage.src);
    const detection = await faceapi.detectSingleFace(img, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();

    if(!detection){
      status.innerText = "Error: No face found in your registered photo. Re-register.";
      return;
    }
    savedDescriptor = detection.descriptor;

    // Start webcam
    stream = await navigator.mediaDevices.getUserMedia({ video: { width: 400, height: 300 } });
    video.srcObject = stream;
    verifyBtn.innerText = "Verify Face";
    verifyBtn.disabled = false;
    status.innerText = "Position your face and click Verify";

  } catch(err) {
    status.innerText = "Camera/Photo Error: " + err.message + ". Check image path or camera permission.";
    console.error(err);
  }
}

video.addEventListener('play', () => {
  const displaySize = { width: 400, height: 300 }
  faceapi.matchDimensions(overlay, displaySize);

  setInterval(async () => {
    const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks();
    const resizedDetections = faceapi.resizeResults(detections, displaySize);
    overlay.getContext('2d').clearRect(0, 0, overlay.width, overlay.height);
    faceapi.drawDetections(overlay, resizedDetections);
    faceapi.drawFaceLandmarks(overlay, resizedDetections);
  }, 100)
})

// 3. On Click: Compare faces
verifyBtn.addEventListener('click', async () => {
  verifyBtn.disabled = true;
  status.innerText = "Verifying... Hold still";

  const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();

  if(!detection){
    status.innerText = "No face detected. Try again";
    verifyBtn.disabled = false;
    return;
  }

  const distance = faceapi.euclideanDistance(savedDescriptor, detection.descriptor);
  
  if(distance > 0.6){
    status.innerText = "Face does not match. Access Denied. Distance: " + distance.toFixed(2);
    verifyBtn.disabled = false;
  } else {
    status.innerText = "Face Matched! Logging in...";
    document.getElementById('is_verified').value = "1";
    document.getElementById('verifyForm').submit();
  }
});
</script>
</body>
</html>