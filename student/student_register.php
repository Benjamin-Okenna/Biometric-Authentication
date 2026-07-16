<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration</title>
  <link rel="stylesheet" href="../assets/css/student_register.css">
  <style>
    .camera-frame { width: 320px; height: 240px; border: 3px solid #4facfe; border-radius: 10px; background: #000; position: relative; overflow: hidden; }
    #video, #canvas { width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; }
    #canvas { display: none; }
    #captureBtn, #retakeBtn { margin-top: 10px; padding: 8px 15px; background: #4facfe; color: white; border: none; cursor: pointer; border-radius: 5px; }
    #retakeBtn { background: #ff4d4d; display: none; }
    
    /* Error/Success message */
    .alert { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
    .alert-error { background: #ffdddd; color: #d8000c; border: 1px solid #d8000c; }
    .alert-success { background: #ddffdd; color: #270; border: 1px solid #270; }
  </style>
</head>
<body>


<!DOCTYPE html>
<html lang="en">
<head>... </head>
<body>

<form action="register_process.php" method="POST" enctype="multipart/form-data">
  <div class="inputContainer">
    <div class="boxInput">
      <h2>Student Enrollment</h2>

      <!-- ONLY SHOW ERROR HERE -->
      <?php 
      if(isset($_SESSION['error'])){
        echo "<div class='alert alert-error'>".$_SESSION['error']."</div>";
        unset($_SESSION['error']); // clear it after showing
      }
      // REMOVED THE SUCCESS BLOCK FROM HERE
      ?>

      <label for="name">Full Name</label>
      <input type="text" id="name" name="fullname" required value="<?php echo $_SESSION['old']['fullname'] ?? ''; ?>">

      <label for="matric">Matric</label>
      <input type="text" id="matric" name="matric" required value="<?php echo $_SESSION['old']['matric'] ?? ''; ?>">

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required value="<?php echo $_SESSION['old']['email'] ?? ''; ?>">

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
      <button type="submit">Register</button>

      <a class="returnBtn" href="student_login.php">Back</a>
      </div>

      <div class="captureBox">
        <label>Capture Face</label>
        <div class="camera-frame">
          <video id="video" autoplay playsinline muted></video>
          <canvas id="canvas"></canvas>
        </div>
        <button type="button" id="captureBtn">Take Photo</button>
        <button type="button" id="retakeBtn">Retake</button>
        <input type="hidden" name="webcam_image" id="webcam_image" required>
      </div>
  </div>
</form>

<script src="student_register.js"></script>
</body>
</html>