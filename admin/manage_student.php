<?php
session_start();
if(!isset($_SESSION['admin_id'])){ header("Location: admin_login.php"); exit(); }
require '../student/db.php'; 

$msg = "";

// DELETE STUDENT
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM students WHERE id = $id");
    $msg = "<span style='color:green;'>Student Deleted!</span>";
}

// ADD STUDENT
if(isset($_POST['add_student'])){
    $fullname = $_POST['fullname'];
    $matric = $_POST['matric'];
    $email = $_POST['email'];
    $password = md5($_POST['password']); // use password_hash later

    $check = $conn->prepare("SELECT * FROM students WHERE matric = ?");
    $check->bind_param("s", $matric);
    $check->execute();
    if($check->get_result()->num_rows > 0){
        $msg = "<span style='color:red;'>Matric No already exists!</span>";
    } else {
        $stmt = $conn->prepare("INSERT INTO students (fullname, matric, email, password) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $fullname, $matric, $email, $password);
        $stmt->execute() ? $msg="<span style='color:green;'>Student Added!</span>" : $msg="<span style='color:red;'>Error</span>";
    }
}

$students = $conn->query("SELECT * FROM students ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head><title>Manage Students</title>
<link rel="stylesheet" href="../assets/css/manage_student.css">

</head>
<body>
<?php include 'includes/sidebar.php'; ?> 
<div class="main-content">
    <h1>Manage Students</h1>
    <?php if($msg!="") echo "<p>$msg</p>"; ?>

    <div class="form-box">
        <h3>Add New Student</h3>
        <form method="POST">
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="text" name="matric" placeholder="Matric No" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add_student">Add Student</button>
        </form>
    </div>

    <div class="table-box">
        <h3>All Students [<?php echo $students->num_rows; ?>]</h3>
        <table>
            <tr><th>ID</th><th>Full Name</th><th>Matric No</th><th>Email</th><th>Action</th></tr>
            <?php while($s = $students->fetch_assoc()): ?>
            <tr>
                <td><?php echo $s['id']; ?></td>
                <td><?php echo $s['fullname']; ?></td>
                <td><?php echo $s['matric']; ?></td>
                <td><?php echo $s['email']; ?></td>
                <td><a href="?delete=<?php echo $s['id']; ?>" class="btn-del" onclick="return confirm('Delete this student?')">Delete</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>