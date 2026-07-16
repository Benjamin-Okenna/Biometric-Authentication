<?php
session_start();
if(!isset($_SESSION['admin_id'])){ header("Location: admin_login.php"); exit(); }
require '../student/db.php'; 

// Get all results with student and exam info
$results = $conn->query("SELECT r.*, e.course_code, e.course_title FROM results r 
                         JOIN exams e ON r.exam_id = e.exam_id 
                         ORDER BY r.submitted_at DESC");
?>
<!DOCTYPE html>
<html>
<head><title>Manage Results</title>
<link rel="stylesheet" href="../assets/css/manage_result.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?> 
<div class="main-content">
    <h1>Manage Results</h1>

    <div class="table-box">
        <h3>All Submitted Results [<?php echo $results->num_rows; ?>]</h3>
        <table>
            <tr>
                <th>S/N</th><th>Student Name</th><th>Matric No</th>
                <th>Course</th><th>Score</th><th>Status</th><th>Submitted At</th>
            </tr>
            <?php $i=1; while($r = $results->fetch_assoc()): 
                $status = $r['score'] >= 40 ? "<span class='pass'>PASS</span>" : "<span class='fail'>FAIL</span>";
            ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo $r['fullname']; ?></td>
                <td><?php echo $r['matric']; ?></td>
                <td><?php echo $r['course_code']; ?></td>
                <td><b><?php echo $r['score']; ?></b></td>
                <td><?php echo $status; ?></td>
                <td><?php echo date("d M Y h:i A", strtotime($r['submitted_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>