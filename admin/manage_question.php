<?php
session_start();
if(!isset($_SESSION['admin_id'])){ header("Location: admin_login.php"); exit(); }
require '../student/db.php'; 

$msg = "";

// DELETE SINGLE QUESTION
if(isset($_GET['del_q'])){
    $qid = intval($_GET['del_q']);
    $stmt = $conn->prepare("DELETE FROM questions WHERE question_id = ?");
    $stmt->bind_param("i", $qid);
    $stmt->execute() ? $msg="<span style='color:green;'>Question Deleted!</span>" : $msg="<span style='color:red;'>Error</span>";
    header("Location: ".$_SERVER['PHP_SELF']); exit(); // redirect to prevent repeat delete
}

// DELETE WHOLE EXAM + ALL ITS QUESTIONS
if(isset($_GET['del_exam'])){
    $exam_id = intval($_GET['del_exam']);
    
    $stmt1 = $conn->prepare("DELETE FROM results WHERE exam_id = ?");
    $stmt1->bind_param("i", $exam_id);
    $stmt1->execute();
    
    $stmt2 = $conn->prepare("DELETE FROM questions WHERE exam_id = ?");
    $stmt2->bind_param("i", $exam_id);
    $stmt2->execute();
    
    $stmt3 = $conn->prepare("DELETE FROM exams WHERE exam_id = ?");
    $stmt3->bind_param("i", $exam_id);
    $stmt3->execute() ? $msg="<span style='color:green;'>Exam and all its questions deleted!</span>" : $msg="<span style='color:red;'>Error</span>";
    header("Location: ".$_SERVER['PHP_SELF']); exit(); // redirect
}

// ADD QUESTION
if(isset($_POST['add_question'])){
    $exam_id = intval($_POST['exam_id']);
    $q = trim($_POST['question_text']);
    $a = trim($_POST['option_a']);
    $b = trim($_POST['option_b']);
    $c = trim($_POST['option_c']);
    $d = trim($_POST['option_d']);
    $correct = $_POST['correct_answer'];

    $check = $conn->prepare("SELECT total_questions_limit FROM exams WHERE exam_id = ?");
    $check->bind_param("i", $exam_id);
    $check->execute();
    $exam = $check->get_result()->fetch_assoc();

    if(!$exam){
        $msg = "<span style='color:red;'>Error: Exam does not exist!</span>";
    } else {
        $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM questions WHERE exam_id = ?");
        $count_stmt->bind_param("i", $exam_id);
        $count_stmt->execute();
        $current_count = $count_stmt->get_result()->fetch_assoc()['total'];

        if($current_count >= $exam['total_questions_limit']){
            $msg = "<span style='color:red;'>Limit reached. Max ".$exam['total_questions_limit']." questions for this exam.</span>";
        } else {
            $stmt = $conn->prepare("INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("issssss", $exam_id, $q, $a, $b, $c, $d, $correct);
            $stmt->execute() ? $msg="<span style='color:green;'>Question Added!</span>" : $msg="<span style='color:red;'>Error: ".$stmt->error."</span>";
        }
    }
}

$exams = $conn->query("SELECT * FROM exams ORDER BY exam_id DESC");
$result = $conn->query("SELECT q.*, e.course_code, e.course_title FROM questions q JOIN exams e ON q.exam_id = e.exam_id ORDER BY e.course_code, q.question_id");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Questions - Admin</title>
  <link rel="stylesheet" href="../assets/css/manage_question.css">
  <style>
    .btn-del{background:#dc3545; color:white; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px;}
    .btn-del:hover{background:#c82333;}
    .btn-del-exam{background:#ff9800; color:white; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px; margin-left:10px;}
  </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?> 
<div class="main-content">
    <h1>Manage Questions</h1>
    <?php if($msg!="") echo "<p>$msg</p>"; ?>

    <div class="form-box">
        <h3>Add New Question to Any Course</h3>
        <form method="POST">
            <label>Select Course/Exam:</label>
            <div style="display:flex; gap:10px; align-items:center;">
                <select name="exam_id" required style="flex:1;">
                    <option value="">-- Select Exam --</option>
                    <?php 
                    while($e = $exams->fetch_assoc()): ?>
                        <option value="<?php echo $e['exam_id']; ?>">
                            <?php echo $e['course_code']." - ".$e['course_title']." | Limit: ".$e['total_questions_limit']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <label>Question:</label>
            <textarea name="question_text" required></textarea>
            <label>Option A:</label><input type="text" name="option_a" required>
            <label>Option B:</label><input type="text" name="option_b" required>
            <label>Option C:</label><input type="text" name="option_c" required>
            <label>Option D:</label><input type="text" name="option_d" required>
            <label>Correct Answer:</label>
            <select name="correct_answer" required>
                <option value="">--Select--</option><option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option>
            </select>
            <button type="submit" name="add_question">Add Question</button>
        </form>
    </div>

    <div class="table-box">
        <h3>All Questions Grouped by Course</h3>
        <table border="1" width="100%">
            <tr><th>Course</th><th>Question</th><th>A</th><th>B</th><th>C</th><th>D</th><th>Ans</th><th>Action</th></tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <b><?php echo $row['course_code']; ?></b>
                    <!-- MOVED DELETE EXAM HERE -->
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?del_exam=<?php echo $row['exam_id']; ?>" 
                       class="btn-del-exam" 
                       onclick="return confirm('Delete this entire exam and all its questions + results?')">
                       Delete Exam
                    </a>
                </td>
                <td><?php echo $row['question_text']; ?></td>
                <td><?php echo $row['option_a']; ?></td>
                <td><?php echo $row['option_b']; ?></td>
                <td><?php echo $row['option_c']; ?></td>
                <td><?php echo $row['option_d']; ?></td>
                <td><?php echo $row['correct_answer']; ?></td>
                <td>
                    <!-- DELETE QUESTION: Use PHP_SELF so filename doesn't matter -->
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?del_q=<?php echo $row['question_id']; ?>" 
                       class="btn-del" 
                       onclick="return confirm('Delete this question?')">
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