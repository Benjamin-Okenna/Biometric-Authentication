<?php
session_start();
$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hardcoded check: username=admin, password=pass123
    if($username === "admin" && $password === "faith99"){
        $_SESSION['admin_id'] = 1; // set session so we know they're logged in
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid Username or Password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - ESPOLY IWOLLO</title>
  <link rel="stylesheet" href="../assets/css/admin_login.css">
</head>
<body>
  <form method="POST" action=""> <!-- POST to same page -->
    <h1>Welcome To Admin Portal</h1>

    <?php if($error != ""): ?>
      <p style="color:red; text-align:center;"><?php echo $error; ?></p>
    <?php endif; ?>

    <div class="login-box">
      <div class="textBox">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required> <!-- added name -->
      </div>
      
      <div class="passBox">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required> <!-- added name -->
      </div>
      
      <button class="submitBtn" type="submit" name="login">Login</button>
    </div>
  </form>
</body>
</html>