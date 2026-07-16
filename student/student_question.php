<?php
session_start();
require 'db.php';

// FIX 1: Check both matric and student_id
if(!isset($_SESSION['matric']) && isset($_SESSION['student_id'])){ 
    $id = $_SESSION['student_id'];
    $s = $conn->query("SELECT matric FROM students WHERE id=$id")->fetch_assoc();
    $_SESSION['matric'] = $s['matric']; // set it
}

if(!isset($_SESSION['matric'])){ 
    header("Location: student_login.php"); 
    exit(); 
}

$matric = $_SESSION['matric'];
$exam_id = $_GET['exam_id'] ?? 0; 

// FIX 2: If no exam_id from sidebar, show list of past exams instead of redirect
if($exam_id == 0){
    // Show all exams this student has written
    $list = $conn->prepare("SELECT r.*, e.course_code, e.course_title FROM results r 
                            JOIN exams e ON r.exam_id = e.exam_id 
                            WHERE r.matric = ? ORDER BY r.submitted_at DESC");
    $list->bind_param("s", $matric);
    $list->execute();
    $past_exams = $list->get_result();
    $show_list = true;
} else {
    $show_list = false;
    // 1. Check if student has result for this exam
    $check = $conn->prepare("SELECT r.*, e.course_code, e.course_title FROM results r 
                             JOIN exams e ON r.exam_id = e.exam_id 
                             WHERE r.exam_id = ? AND r.matric = ?");
    $check->bind_param("is", $exam_id, $matric);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();

    // 2. Get all questions for this exam
    $q_stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY question_id ASC");
    $q_stmt->bind_param("i", $exam_id);
    $q_stmt->execute();
    $questions = $q_stmt->get_result();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Exam Reference</title>
<style>
    body{font-family:Arial; padding:20px; background:#f4f4f4;}
    .header{background:#4facfe; color:#fff; padding:15px; border-radius:8px; text-align:center; margin-bottom:20px;}
    .box{background:#fff; padding:20px; border-radius:8px; margin-bottom:15px; border-left:4px solid #4facfe;}
    .correct{color:green; font-weight:bold;}
    .alert{background:#fff3cd; color:#856404; padding:20px; border-radius:8px; text-align:center;}
    .btn{background:#4facfe; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; display:inline-block; margin:5px;}
    .btn-secondary{background:#6c757d;}
</style>
</head>
<body>

<?php if($show_list): ?>
    <!-- SHOW LIST OF PAST EXAMS -->
    <div class="header"><h1>Your Exam References</h1></div>
    <?php if($past_exams->num_rows == 0): ?>
        <div class="alert"><h2>No Exams Written Yet</h2></div>
    <?php else: ?>
        <?php while($ex = $past_exams->fetch_assoc()): ?>
            <div class="box">
                <h3><?php echo $ex['course_code']." - ".$ex['course_title']; ?></h3>
                <p>Score: <?php echo $ex['score']; ?> / <?php echo $ex['total_questions']; ?></p>
                <a href="student_question.php?exam_id=<?php echo $ex['exam_id']; ?>" class="btn">View Questions</a>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
    
    <!-- BUTTON ADDED HERE -->
    <a href="student_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>

<?php elseif(!$result): ?>
    <!-- IF STUDENT HAS NOT WRITTEN THIS SPECIFIC EXAM -->
    <div class="alert">
        <h2>No Exam Result Yet</h2>
        <p>You can only view questions after you have written and submitted this exam.</p>
        <a href="student_question.php" class="btn">Back to Exam List</a>
        <a href="student_dashboard.php" class="btn btn-secondary">Back to Dashboard</a> <!-- BUTTON ADDED HERE -->
    </div>

<?php else: ?>
    <!-- SHOW REFERENCE -->
    <div class="header">
        <h1><?php echo $result['course_code']." - ".$result['course_title']; ?></h1>
        <p>Your Score: <b><?php echo $result['score']; ?> / <?php echo $result['total_questions']; ?></b></p>
    </div>

    <?php $i=1; while($q = $questions->fetch_assoc()): ?>
        <div class="box">
            <p><b>Q<?php echo $i++; ?>. <?php echo $q['question_text']; ?></b></p>
            <p>A. <?php echo $q['option_a']; ?></p>
            <p>B. <?php echo $q['option_b']; ?></p>
            <p>C. <?php echo $q['option_c']; ?></p>
            <p>D. <?php echo $q['option_d']; ?></p>
            <p>Correct Answer: <span class="correct"><?php echo $q['correct_answer']; ?></span></p>
        </div>
    <?php endwhile; ?>
    
    <a href="student_question.php" class="btn">Back to Exam List</a>
    <a href="student_dashboard.php" class="btn btn-secondary">Back to Dashboard</a> <!-- BUTTON ADDED HERE -->
<?php endif; ?>

</body>
</html>