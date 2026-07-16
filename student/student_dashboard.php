<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'db.php';

date_default_timezone_set('Africa/Lagos'); // ADD THIS LINE HERE

if(!isset($_SESSION['student_id'])){ 
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$msg = "";

// Get student data
$stmt = $conn->prepare("SELECT fullname, matric, email, profile_image FROM students WHERE id =?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$profile_img = !empty($student['profile_image']) 
    ? "student/" . $student['profile_image'] 
    : "../assets/img/default-avatar.png";

// Handle Start Exam
$now = time(); // CHANGED: use timestamp instead of date string
if(isset($_POST['start_exam'])){
    $exam_id = intval($_POST['exam_id']); // added intval
    $stmt_exam = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?"); // use prepared stmt
    $stmt_exam->bind_param("i", $exam_id);
    $stmt_exam->execute();
    $exam = $stmt_exam->get_result()->fetch_assoc();
    
    if(!$exam){ $msg = "<p style='color:red; text-align:center;'>Exam not found.</p>"; }
    else {
        // Check if already written
        $check = $conn->prepare("SELECT * FROM results WHERE exam_id = ? AND matric = ?");
        $check->bind_param("is", $exam_id, $student['matric']);
        $check->execute();
        if($check->get_result()->num_rows > 0){
            $msg = "<p style='color:red; text-align:center;'>You have already written this exam.</p>";
        } 
        else {
            $start_time = strtotime($exam['exam_start']);
            $end_time = $start_time + ($exam['duration'] * 60);
            
            // Check Time
            if($now < $start_time){
                $msg = "<p style='color:red; text-align:center;'>Exam has not started yet. Starts: ".date("d M Y h:i A", $start_time)."</p>";
            }
            elseif($now > $end_time){
                $msg = "<p style='color:red; text-align:center;'>Exam has ended. Ended: ".date("d M Y h:i A", $end_time)."</p>";
            }
            else {
                $_SESSION['exam_id'] = $exam_id;
                header("Location: student_question.php");
                exit();
            }
        }
    }
}

// Get all exams for dropdown
$exams = $conn->query("SELECT * FROM exams ORDER BY exam_start ASC");
$exams_json = json_encode($exams->fetch_all(MYSQLI_ASSOC));
$exams->data_seek(0); // reset pointer
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="../assets/css/student_dashboard.css">
  <style>
    .profileCard { text-align: center; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .profilePic { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #4facfe; margin-bottom: 15px; }
    .profileList { list-style: none; padding: 0; text-align: left; margin-top: 10px; }
    .profileList li { padding: 8px 0; border-bottom: 1px solid #eee; }
    .profileList li b { color: #333; }
    .welcome { font-size: 14px; color: #555; }
    .headTitle { display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; background: #4facfe; color: white; }
    .examOutline p { margin: 0 0 10px 0; font-size: 18px; font-weight: bold; color: #4facfe; }
    .selectContainer select, .selectContainer input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; }
    .examStat { width: 100%; padding: 12px; background: #4facfe; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
    .examStat:disabled { background: #ccc; cursor: not-allowed; }
  </style>
  <script>
    const exams = <?php echo $exams_json; ?>;
    const serverNow = <?php echo time(); ?> * 1000; // Pass PHP server time to JS
    
    function updateDetails(examId){
        const exam = exams.find(e => e.exam_id == examId);
        if(exam){
            document.getElementById('total').innerText = exam.total_questions_limit;
            document.getElementById('duration').innerText = exam.duration + " Minutes";
            document.getElementById('course_title').value = exam.course_title;
            
            const now = serverNow; // Use server time, not browser time
            const start = new Date(exam.exam_start).getTime();
            const end = start + exam.duration * 60000;
            
            let status = "Not Started";
            if(now >= start && now <= end) status = "Available Now";
            if(now > end) status = "Ended";
            
            document.getElementById('status').innerText = status;
            document.getElementById('startBtn').disabled = (status !== "Available Now");
        }
    }
  </script>
</head>
<body>

  <aside>
    <nav>
      <a href="student_dashboard.php">Dashboard</a>
      <a href="student_question.php">Question</a>
      <a href="student_result.php">Result</a>
      <a href="logout.php">Logout</a>
    </nav>
  </aside>

  <div class="headTitle">
    <h2>CBT BIOMETRIC AUTHENTICATION SYSTEM</h2>
    <span class="welcome">Logged in: <b><?php echo $student['fullname']; ?></b></span>
  </div>
    
  <h3 style="padding: 20px 30px 0;">Candidate Profile</h3>
  <main>
    <?php if($msg!="") echo $msg; ?>
    <section class="leftSection">
      <div class="profileCard">
        <h4>Enugu State Polytechnic, Iwollo</h4>
        <img src="http://localhost/biometric-authentication/<?php echo $profile_img; ?>" alt="Profile" class="profilePic">
        
        <ul class="profileList">
          <li><b>Full Name:</b> <?php echo $student['fullname']; ?></li>
          <li><b>Matric Number:</b> <?php echo $student['matric']; ?></li>
          <li><b>Email:</b> <?php echo $student['email']; ?></li>
        </ul>
      </div>
    </section>

    <section class="rightSection">
      <div class="examSchedule">
        <h4>Exam Details</h4>
        <ul class="examOutline">
          <li><b>Total Question</b></li>
          <p id="total">--</p>
          <li><b>Exam Duration</b></li>
          <p id="duration">--</p>
          <li><b>Exam Status</b></li>
          <p id="status">--</p>
        </ul>
      </div>

      <form method="GET" action="Student_exam.php" class="selectContainer">
  <div class="CodeBox">
    <h2>Select Course Code</h2>
    <select name="exam_id" onchange="updateDetails(this.value)" required>
      <option value="">Select Course Code</option>
      <?php while($e = $exams->fetch_assoc()): 
        $exam_time = new DateTime($e['exam_start'], new DateTimeZone('Africa/Lagos'));
      ?>
      <option value="<?php echo $e['exam_id']; ?>">
          <?php echo $e['course_code']." - ".$exam_time->format("d M h:i A"); ?>
      </option>
      <?php endwhile; ?>
    </select>
  </div>
  
  <div class="titleBox">
    <h2>Select Course Title</h2>
    <input type="text" id="course_title" readonly placeholder="Auto fills when you select course code">
  </div>
  
  <button type="submit" name="start_exam" id="startBtn" class="examStat" disabled>Start Examination</button>
</form>
    </section>
  </main>
   
</body>
</html>