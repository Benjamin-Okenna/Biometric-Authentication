<?php
session_start();
include 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $matric = $_POST['matric'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, fullname, password FROM students WHERE matric = ?");
    $stmt->bind_param("s", $matric);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $student = $result->fetch_assoc();
        
        // Verify password
        if(password_verify($password, $student['password'])){
            // Step 1 passed. Save temp data for face check
            $_SESSION['temp_student_id'] = $student['id'];
            $_SESSION['temp_fullname'] = $student['fullname'];
            
            // Go to face verification
            header("Location: face_verify.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid Password";
        }
    } else {
        $_SESSION['login_error'] = "Matric Number not found";
    }

    $stmt->close();
    $conn->close();
    header("Location: student_login.php");
    exit();
}
?>