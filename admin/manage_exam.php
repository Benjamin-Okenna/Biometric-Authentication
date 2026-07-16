<?php
session_start();
if(!isset($_SESSION['admin_id'])){ header("Location: admin_login.php"); exit(); }
require '../student/db.php'; 

date_default_timezone_set('Africa/Lagos'); 

$msg = "";

// ADD EXAM
if(isset($_POST['add_exam'])){
    $code = $_POST['course_code'];
    $title = $_POST['course_title'];
    $duration = $_POST['duration'];
    $start = $_POST['exam_start'];
    $limit = $_POST['total_questions_limit'];

    $stmt = $conn->prepare("INSERT INTO exams (course_code, course_title, duration, exam_start, total_questions_limit) VALUES (?,?,?,?,?)");
    $stmt->bind_param("ssisi", $code, $title, $duration, $start, $limit);
    $stmt->execute() ? $msg="<span style='color:green;'>Exam Created!</span>" : $msg="<span style='color:red;'>Error: ".$stmt->error."</span>";
}

// DELETE EXAM
if(isset($_GET['delete'])){
    $exam_id = intval($_GET['delete']); // added intval for security
    
    // 1. Delete all results for this exam first
    $stmt1 = $conn->prepare("DELETE FROM results WHERE exam_id = ?");
    $stmt1->bind_param("i", $exam_id);
    $stmt1->execute();
    
    // 2. Delete all questions for this exam
    $stmt2 = $conn->prepare("DELETE FROM questions WHERE exam_id = ?");
    $stmt2->bind_param("i", $exam_id);
    $stmt2->execute();
    
    // 3. Delete the exam itself
    $stmt3 = $conn->prepare("DELETE FROM exams WHERE exam_id = ?");
    $stmt3->bind_param("i", $exam_id);
    $stmt3->execute() ? $msg="<span style='color:green;'>Exam Deleted!</span>" : $msg="<span style='color:red;'>Error: ".$stmt3->error."</span>";

    header("Location: ".$_SERVER['PHP_SELF']); // redirect to clear ?delete= from url
    exit();
}

$exams = $conn->query("SELECT * FROM exams ORDER BY exam_id DESC");
?>
<!DOCTYPE html>
<html>
<head><title>Manage Exams - Admin</title>
<link rel="stylesheet" href="../assets/css/manage_exam.css">
<style>
.btn-del{background:#dc3545; color:white; padding:6px 12px; text-decoration:none; border-radius:4px; font-size:13px;}
.btn-del:hover{background:#c82333;}
</style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?> 
<div class="main-content">
    <h1>Manage Exams / Courses</h1>
    <?php if($msg!="") echo "<p>$msg</p>"; ?>

    <div class="form-box">
        <h3>Create New Exam</h3>
        <form method="POST">
            <label>Course Code:</label><input type="text" name="course_code" placeholder="e.g CSC101" required>
            <label>Course Title:</label><input type="text" name="course_title" placeholder="e.g Introduction to CS" required>
            <label>Duration (Minutes):</label><input type="number" name="duration" placeholder="60" required>
            <label>Exam Start Date & Time:</label><input type="datetime-local" name="exam_start" required>
            <label>Total Questions Limit:</label><input type="number" name="total_questions_limit" placeholder="20" required>
            <button type="submit" name="add_exam">Create Exam</button>
        </form>
    </div>

    <div class="form-box">
    <h3>All Exams</h3>
    <table border="1" width="100%">
        <tr><th>ID</th><th>Code</th><th>Title</th><th>Duration</th><th>Start Time</th><th>Limit</th><th>Action</th></tr>
        <?php while($e = $exams->fetch_assoc()): ?>
        <tr>
            <td><?php echo $e['exam_id']; ?></td>
            <td><?php echo $e['course_code']; ?></td>
            <td><?php echo $e['course_title']; ?></td>
            <td><?php echo $e['duration']; ?> min</td>
            <td><?php echo date("d M Y, H:i", strtotime($e['exam_start'])); ?></td> 
            <td><?php echo $e['total_questions_limit']; ?></td>
            <td>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?delete=<?php echo $e['exam_id']; ?>" 
                   class="btn-del" 
                   onclick="return confirm('Are you sure you want to delete this exam? All questions and results will be deleted too!')">
                   Delete
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</div>
</body>
</html>