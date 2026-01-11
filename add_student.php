<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $gender = trim($_POST['gender']);
    $roll_no = trim($_POST['roll_no']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $semester = trim($_POST['semester']);
    $phone = trim($_POST['phone']);
    $status = trim($_POST['status']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: add_student.php?error=invalid_email"); exit();
    }

    if (!preg_match("/^[A-Z0-9]+$/", $roll_no)) {
        header("Location: add_student.php?error=invalid_roll"); exit();
    }

    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        header("Location: add_student.php?error=invalid_phone"); exit();
    }

    $check = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ?");
    mysqli_stmt_bind_param($check, "s", $email);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        header("Location: add_student.php?error=duplicate_email"); exit();
    }

    $res = mysqli_query($conn, "SELECT student_code FROM students ORDER BY id DESC LIMIT 1");
    $row = mysqli_fetch_assoc($res);
    $next = ($row && $row['student_code']) ? intval(substr($row['student_code'], 3)) + 1 : 1;
    $student_code = "STU" . str_pad($next, 3, "0", STR_PAD_LEFT);

    $stmt = mysqli_prepare($conn, "
        INSERT INTO students (student_code, name, gender, roll_no, email, course, semester, phone, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "sssssssss",
        $student_code,
        $name,
        $gender,
        $roll_no,
        $email,
        $course,
        $semester,
        $phone,
        $status
    );

    if (!mysqli_stmt_execute($stmt)) {
        die("Student insert failed: " . mysqli_error($conn));
    }

    $student_id = mysqli_insert_id($conn);
    $admin_id = $_SESSION['admin_id'];

    if (!mysqli_query($conn, "
        INSERT INTO audit_logs (admin_id, action, student_id)
        VALUES ($admin_id, 'ADD_STUDENT', $student_id)
    ")) {
        die("Audit log insert failed: " . mysqli_error($conn));
    }

    header("Location: students.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<h2>Add Student</h2>

<?php if (isset($_GET['error'])): ?>
<p class="alert">
<?php
if ($_GET['error'] === 'invalid_email') echo "Invalid email format.";
elseif ($_GET['error'] === 'invalid_roll') echo "Roll number must be uppercase alphanumeric.";
elseif ($_GET['error'] === 'invalid_phone') echo "Phone must be exactly 10 digits.";
elseif ($_GET['error'] === 'duplicate_email') echo "This email already exists.";
?>
</p>
<?php endif; ?>

<form method="POST">
    <label>Name</label>
    <input type="text" name="name" required>

    <label>Gender</label>
    <select name="gender" required>
        <option value="" disabled selected>Select gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
    </select>

    <label>Roll No</label>
    <input type="text" name="roll_no" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Course</label>
    <select name="course" required>
        <option value="" disabled selected>Select course</option>
        <option value="CSE">CSE</option>
        <option value="ECE">ECE</option>
        <option value="EEE">EEE</option>
    </select>

    <label>Semester</label>
    <input type="text" name="semester" required>

    <label>Phone</label>
    <input type="text" name="phone" required>

    <label>Status</label>
    <select name="status">
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
    </select>

    <br><br>
    <button type="submit">Add Student</button>
</form>

<br>
<a href="students.php">View Students</a>

</div>

</body>
</html>
