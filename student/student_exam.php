<?php
session_start();
if(!isset($_SESSION['matric'])){ header("Location: student_login.php"); exit(); }
require 'db.php';

date_default_timezone_set('Africa/Lagos'); // ADD THIS HERE

$exam_id = $_GET['exam_id']; // link will be like take_exam.php?exam_id=7

// 1. Check if exam is active based on time
$exam = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
$exam->bind_param("i", $exam_id);
$exam->execute();
$exam_data = $exam->get_result()->fetch_assoc();

if(!$exam_data){
    die("Exam not found");
}

$start_time = strtotime($exam_data['exam_start']);
$end_time = $start_time + ($exam_data['duration'] * 60); // duration in minutes
$now = time();

if($now < $start_time){
    die("<h2>Exam has not started yet</h2><p>Starts: ".date("d M Y h:i A", $start_time)."</p><a href='student_dashboard.php'>Back</a>");
}
if($now > $end_time){
    die("<h2>Exam has ended</h2><p>Ended: ".date("d M Y h:i A", $end_time)."</p><a href='student_dashboard.php'>Back</a>");
}

// 2. Get all questions for this exam
$q_stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY RAND()"); // RAND = shuffle
$q_stmt->bind_param("i", $exam_id);
$q_stmt->execute();
$questions = $q_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Take Exam: <?php echo $exam_data['course_code']; ?></title>
<style>
    body{font-family:Arial; padding:20px; background:#f4f4f4;}
    .box{background:#fff; padding:20px; border-radius:8px; margin-bottom:15px;}
    .timer{position:fixed; top:10px; right:10px; background:red; color:white; padding:10px; font-size:18px;}
</style>
</head>
<body>
<div class="timer" id="timer"></div>
<h1><?php echo $exam_data['course_code']." - ".$exam_data['course_title']; ?></h1>
<p><b>Duration:</b> <?php echo $exam_data['duration']; ?> minutes</p>

<form method="POST" action="submit_exam.php">
<input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
<?php $i=1; while($q = $questions->fetch_assoc()): ?>
    <div class="box">
        <p><b>Q<?php echo $i++; ?>. <?php echo $q['question_text']; ?></b></p>
        <input type="radio" name="q<?php echo $q['question_id']; ?>" value="A" required> <?php echo $q['option_a']; ?><br>
        <input type="radio" name="q<?php echo $q['question_id']; ?>" value="B"> <?php echo $q['option_b']; ?><br>
        <input type="radio" name="q<?php echo $q['question_id']; ?>" value="C"> <?php echo $q['option_c']; ?><br>
        <input type="radio" name="q<?php echo $q['question_id']; ?>" value="D"> <?php echo $q['option_d']; ?>
    </div>
<?php endwhile; ?>
<button type="submit" name="submit_exam">Submit Exam</button>
</form>

<script>
// Countdown timer
var end = <?php echo $end_time; ?> * 1000;
var x = setInterval(function() {
  var distance = end - new Date().getTime();
  var m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  var s = Math.floor((distance % (1000 * 60)) / 1000);
  document.getElementById("timer").innerHTML = m + "m " + s + "s ";
  if (distance < 0) { clearInterval(x); document.getElementById("timer").innerHTML = "TIME UP"; document.querySelector("form").submit(); }
}, 1000);
</script>
</body>
</html>