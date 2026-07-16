<?php
session_start();
if(!isset($_SESSION['matric'])){ header("Location: student_login.php"); exit(); }
require 'db.php';

$matric = $_SESSION['matric'];

// Get all results for logged in student
$sql = "SELECT r.*, e.course_code, e.course_title 
        FROM results r 
        LEFT JOIN exams e ON r.exam_id = e.exam_id 
        WHERE r.matric = '$matric' 
        ORDER BY r.submitted_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Student Result</title>
<style>
body{font-family:Arial; background:#f4f4f4; padding:30px;}
.card{background:white; padding:20px; margin:15px auto; border-radius:10px; max-width:600px; box-shadow:0 2px 5px rgba(0,0,0,0.1);}
h2{text-align:center; color:#007bff;}
.btn{padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px; display:inline-block;}
</style>
</head>
<body>

<h2>Your Exam Results</h2>

<?php if($result->num_rows == 0): ?>
    <div class='card' style='text-align:center;'>
        <h3 style='color:red;'>No Exam Result Yet</h3>
        <p>You have not written any exam yet.</p>
    </div>
<?php else: ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <div class='card'>
            <h3><?php echo $row['course_code']; ?> - <?php echo $row['course_title']; ?></h3>
            <p><b>Matric:</b> <?php echo $row['matric']; ?></p>
            <p><b>Score:</b> <?php echo $row['score']; ?> / <?php echo $row['total_questions']; ?></p>
            <p><b>Percentage:</b> <?php echo $row['percentage']; ?>%</p>
            <p><b>Date:</b> <?php echo $row['submitted_at']; ?></p>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

<div style='text-align:center; margin-top:20px;'>
    <a href='student_dashboard.php' class='btn' style='background:gray;'>Back to Dashboard</a>
</div>

</body>
</html>