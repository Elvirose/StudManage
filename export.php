<?php
require 'config.php';

$where = "status != 'Deleted'";

if (!empty($_GET['search'])) {
    $s = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (name LIKE '%$s%' OR email LIKE '%$s%')";
}

if (!empty($_GET['course'])) {
    $c = mysqli_real_escape_string($conn, $_GET['course']);
    $where .= " AND course = '$c'";
}

$result = mysqli_query($conn, "SELECT student_code,name,roll_no,email,course,semester,phone,status FROM students WHERE $where");

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="students.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Code','Name','Roll','Email','Course','Semester','Phone','Status']);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($out, $row);
}

fclose($out);
exit();
