<?php
session_start();
session_destroy(); // kills all session data
header("Location: admin_login.php"); // then go to login
exit();
?>