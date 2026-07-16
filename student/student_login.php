<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Login</title>
  <link rel="stylesheet" href="../assets/css/student_login.css">
  <style>
    .alert-error { padding: 10px; margin-bottom: 15px; background: #ffdddd; color: #d8000c; border: 1px solid #d8000c; border-radius: 5px; text-align: center; }
  </style>
</head>
<body>
  <form action="login_process.php" method="POST"> <!-- send to process file -->
    <header>
     <h1>Espoly Student Login</h1>
  </header>
  
 <div class="inputContainer">
  <div class="boxInput">

    <!-- SHOW ERROR -->
    <?php 
      if(isset($_SESSION['login_error'])){
        echo "<div class='alert-error'>".$_SESSION['login_error']."</div>";
        unset($_SESSION['login_error']);
      }
    ?>

    <label for="matric">Matric Number</label>
    <input type="text" id="matric" name="matric" required> <!-- added name -->

  </div>
  
  <div class="boxInput">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required> <!-- added name -->
  </div>

  <div class="boxInput">
    <button class="submitBtn" type="submit">Login</button>
  </div>
  <p class="selectBtn">OR</p>
  <a class="regBtn" href="student_register.php">Register</a>
  
 </div>
  </form>
</body>
</html>