<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'db.php';

if(!isset($_SESSION['temp_student_id']) || $_POST['is_verified']!= "1"){
    $_SESSION['login_error'] = "Face verification failed";
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['temp_student_id'];
unset($_SESSION['temp_student_id']);
$_SESSION['student_id'] = $student_id;
$_SESSION['success'] = "Face Verified. Login Successful";
header("Location: student_dashboard.php");
exit();
?>