<?php
session_start();
if(!isset($_SESSION['admin_id'])){ header("Location: admin_login.php"); exit(); }
require '../student/db.php'; 

// Get Counts
$total_students = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
$total_exams = $conn->query("SELECT COUNT(*) as c FROM exams")->fetch_assoc()['c'];
$total_questions = $conn->query("SELECT COUNT(*) as c FROM questions")->fetch_assoc()['c'];
$total_results = $conn->query("SELECT COUNT(*) as c FROM results")->fetch_assoc()['c'];

// Ongoing exams: now between start and end
$now = date("Y-m-d H:i:s");
$ongoing = $conn->query("SELECT COUNT(*) as c FROM exams WHERE exam_start <= '$now' AND DATE_ADD(exam_start, INTERVAL duration MINUTE) >= '$now'")->fetch_assoc()['c'];

// Upcoming exams
$upcoming = $conn->query("SELECT * FROM exams WHERE exam_start > '$now' ORDER BY exam_start ASC LIMIT 5");

// Recent Results
$recent = $conn->query("SELECT r.*, e.course_code FROM results r JOIN exams e ON r.exam_id = e.exam_id ORDER BY r.submitted_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?> 
<div class="main-content">
    <h1>Welcome Admin</h1>

    <!-- STAT CARDS -->
    <div class="cards">
        <div class="card"><h2><?php echo $total_students; ?></h2><p>Total Students</p></div>
        <div class="card"><h2><?php echo $total_exams; ?></h2><p>Total Exams</p></div>
        <div class="card"><h2><?php echo $total_questions; ?></h2><p>Total Questions</p></div>
        <div class="card"><h2><?php echo $total_results; ?></h2><p>Results Submitted</p></div>
        <div class="card" style="background:#16a34a;"><h2><?php echo $ongoing; ?></h2><p>Ongoing Exams</p></div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="section">
        <h3>Quick Actions</h3>
        <div class="quickSections">
        <a href="manage_exam.php" class="btn">+ Create Exam</a>
        <a href="manage_question.php" class="btn">+ Add Question</a>
        <a href="manage_result.php" class="btn">View Results</a>
        </div>
        
    </div>

    <!-- UPCOMING EXAMS -->
    <div class="section">
        <h3>Upcoming Exams</h3>
        <table>
            <tr><th>Course Code</th><th>Title</th><th>Start Time</th><th>Duration</th></tr>
            <?php while($e = $upcoming->fetch_assoc()): ?>
            <tr>
                <td><?php echo $e['course_code']; ?></td>
                <td><?php echo $e['course_title']; ?></td>
                <td><?php echo date("d M Y h:i A", strtotime($e['exam_start'])); ?></td>
                <td><?php echo $e['duration']; ?> min</td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- RECENT RESULTS -->
    <div class="section">
        <h3>Recent Submissions</h3>
        <table>
            <tr><th>Student</th><th>Matric</th><th>Exam</th><th>Score</th><th>Time</th></tr>
            <?php while($r = $recent->fetch_assoc()): ?>
            <tr>
                <td><?php echo $r['fullname']; ?></td>
                <td><?php echo $r['matric']; ?></td>
                <td><?php echo $r['course_code']; ?></td>
                <td><b><?php echo $r['score']; ?></b></td>
                <td><?php echo date("h:i A", strtotime($r['submitted_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

</div>
</body>
</html>