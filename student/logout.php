<?php
session_start();
session_destroy(); // kills all session data
header("Location: student_login.php"); // then go to login
exit();
?>