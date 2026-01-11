<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$where = "status != 'Deleted'";

if (!empty($_GET['search'])) {
    $s = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (name LIKE '%$s%' OR email LIKE '%$s%')";
}

if (!empty($_GET['course'])) {
    $c = mysqli_real_escape_string($conn, $_GET['course']);
    $where .= " AND course = '$c'";
}

if (!empty($_GET['gender'])) {
    $g = mysqli_real_escape_string($conn, $_GET['gender']);
    $where .= " AND gender = '$g'";
}

$result = mysqli_query($conn, "SELECT * FROM students WHERE $where ORDER BY student_code ASC LIMIT $limit OFFSET $offset");

$countRes = mysqli_query($conn, "SELECT COUNT(*) as total FROM students WHERE $where");
$total = mysqli_fetch_assoc($countRes)['total'];
$totalPages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Students</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<div class="top-bar">
    <h2>Students</h2>
    <div>
        <a href="add_student.php">Add Student</a> |
        <a href="logout.php">Logout</a>
    </div>
</div>

<form method="GET" class="filters">
    <input type="text" name="search" placeholder="Search..." value="<?= $_GET['search'] ?? '' ?>">
    <input type="text" name="course" placeholder="Course..." value="<?= $_GET['course'] ?? '' ?>">

    <select name="gender">
        <option value="">All Genders</option>
        <option value="Male" <?= ($_GET['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
        <option value="Female" <?= ($_GET['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
    </select>

    <button type="submit">Filter</button>
    <a href="students.php">Reset</a>
</form>

<a href="export.php?search=<?= $_GET['search'] ?? '' ?>&course=<?= $_GET['course'] ?? '' ?>&gender=<?= $_GET['gender'] ?? '' ?>">Export CSV</a>

<table>
<tr>
    <th>Student Code</th>
    <th>Name</th>
    <th>Gender</th>
    <th>Roll No</th>
    <th>Email</th>
    <th>Course</th>
    <th>Semester</th>
    <th>Phone</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?= $row['student_code'] ?></td>
    <td><?= $row['name'] ?></td>
    <td><?= $row['gender'] ?></td>
    <td><?= $row['roll_no'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['course'] ?></td>
    <td><?= $row['semester'] ?></td>
    <td><?= $row['phone'] ?></td>
    <td class="<?= $row['status'] === 'Active' ? 'status-active' : 'status-inactive' ?>">
        <?= $row['status'] ?>
    </td>
    <td class="actions">
        <a href="edit_student.php?id=<?= $row['id'] ?>">Edit</a> |
        <a href="delete_student.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this student?')">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</table>

<div class="pagination">
<?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?= $i ?>&search=<?= $_GET['search'] ?? '' ?>&course=<?= $_GET['course'] ?? '' ?>&gender=<?= $_GET['gender'] ?? '' ?>">
        <?= $i ?>
    </a>
<?php endfor; ?>
</div>

</div>

</body>
</html>
