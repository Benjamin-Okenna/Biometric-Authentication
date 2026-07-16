<?php
session_start(); // must be first line
include 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    // Save old input so we can refill form if error
    $_SESSION['old'] = $_POST;

    $fullname = $_POST['fullname'];
    $matric = $_POST['matric'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if(empty($_POST['webcam_image'])){
        $_SESSION['error'] = "Please capture your face photo first";
        header("Location: student_register.php");
        exit();
    }

    // 1. Check if email or matric already exists
    $check = $conn->prepare("SELECT id FROM students WHERE email = ? OR matric = ?");
    $check->bind_param("ss", $email, $matric);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){
        $_SESSION['error'] = "This Email or Matric Number is already registered";
        header("Location: student_register.php");
        exit();
    }
    $check->close();

    // 2. Handle webcam image
    $img = $_POST['webcam_image'];
    $img = str_replace('data:image/png;base64,', '', $img);
    $img = str_replace(' ', '+', $img);
    $data = base64_decode($img);
    
    $safe_matric = str_replace('/', '-', $matric); 
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

    $fileName = $uploadDir . 'face-' . $safe_matric . '_' . time() . '.png';
    file_put_contents($fileName, $data);

    // 3. Insert to database
    $sql = "INSERT INTO students (fullname, matric, email, password, profile_image) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $fullname, $matric, $email, $password, $fileName);

    if($stmt->execute()){
        unset($_SESSION['old']); // clear old data
        header("Location: student_login.php"); // REDIRECT ON SUCCESS
        exit();
    } else {
        unlink($fileName); 
        $_SESSION['error'] = "Registration Failed: " . $stmt->error;
        header("Location: student_register.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>