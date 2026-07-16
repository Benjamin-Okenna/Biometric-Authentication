<?php
session_start();
if(!isset($_SESSION['matric'])){ header("Location: student_login.php"); exit(); }
require 'db.php';
date_default_timezone_set('Africa/Lagos');

if(isset($_POST['submit_exam'])){
    $exam_id = intval($_POST['exam_id']);
    $matric = $_SESSION['matric'];

    // Get fullname too since your table has it
    $student = $conn->query("SELECT fullname FROM students WHERE matric = '$matric'")->fetch_assoc();
    $fullname = $student['fullname'] ?? '';

    $check = $conn->query("SELECT * FROM results WHERE exam_id = $exam_id AND matric = '$matric'");
    if($check->num_rows > 0){
        die("<h2 style='text-align:center; color:red;'>You have already submitted this exam</h2><a href='student_dashboard.php'>Back</a>");
    }

    $questions = $conn->query("SELECT question_id, correct_answer FROM questions WHERE exam_id = $exam_id");

    $total = $questions->num_rows;
    if($total == 0) die("No questions found for this exam");
    $score = 0;

    while($q = $questions->fetch_assoc()){
        $qid = $q['question_id'];
        $correct = $q['correct_answer'];
        $student_answer = $conn->real_escape_string($_POST['q'.$qid] ?? '');

        if($student_answer == $correct){ $score++; }

        $conn->query("INSERT INTO student_answers (matric, exam_id, question_id, answer) VALUES ('$matric', $exam_id, $qid, '$student_answer')");
    }

    $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;
    // MATCHES YOUR TABLE COLUMNS NOW
    $conn->query("INSERT INTO results (exam_id, fullname, matric, score, total_questions, percentage, submitted_at) VALUES ($exam_id, '$fullname', '$matric', $score, $total, $percentage, NOW())");

    echo "<div style='text-align:center; padding:50px; font-family:Arial;'>";
    echo "<h2 style='color:green;'>Exam Submitted Successfully!</h2>";
    echo "<h3>Score: $score / $total = $percentage%</h3>";
    echo "<p><a href='student_result.php'>View Result</a> | <a href='student_dashboard.php'>Dashboard</a></p>";
    echo "</div>";

} else {
    header("Location: student_dashboard.php");
}
?>