<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$id = intval($_GET['id']);

$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    die("Student not found.");
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

    $stmt = mysqli_prepare($conn, "
        UPDATE students 
        SET name=?, gender=?, roll_no=?, email=?, course=?, semester=?, phone=?, status=? 
        WHERE id=?
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssi",
        $name,
        $gender,
        $roll_no,
        $email,
        $course,
        $semester,
        $phone,
        $status,
        $id
    );

    mysqli_stmt_execute($stmt);

    header("Location: students.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<h2>Edit Student</h2>

<form method="POST">

    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>

    <label>Gender</label>
    <select name="gender" required>
        <option value="Male" <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
        <option value="Female" <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
    </select>

    <label>Roll No</label>
    <input type="text" name="roll_no" value="<?= htmlspecialchars($student['roll_no']) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>

    <label>Course</label>
    <select name="course" required>
        <option value="CSE" <?= $student['course']=='CSE'?'selected':'' ?>>CSE</option>
        <option value="ECE" <?= $student['course']=='ECE'?'selected':'' ?>>ECE</option>
        <option value="EEE" <?= $student['course']=='EEE'?'selected':'' ?>>EEE</option>
    </select>

    <label>Semester</label>
    <input type="text" name="semester" value="<?= htmlspecialchars($student['semester']) ?>" required>

    <label>Phone</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($student['phone']) ?>" required>

    <label>Status</label>
    <select name="status">
        <option value="Active" <?= $student['status']=='Active'?'selected':'' ?>>Active</option>
        <option value="Inactive" <?= $student['status']=='Inactive'?'selected':'' ?>>Inactive</option>
    </select>

    <br><br>
    <button type="submit">Update Student</button>
    <a href="students.php">Cancel</a>

</form>

</div>

</body>
</html>
