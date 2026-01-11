<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students WHERE status!='Deleted'"))['c'];
$active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students WHERE status='Active'"))['c'];
$inactive = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students WHERE status='Inactive'"))['c'];

$logs = mysqli_query($conn, "
    SELECT a.action, a.created_at, s.name 
    FROM audit_logs a 
    LEFT JOIN students s ON a.student_id = s.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");

$genderData = mysqli_query($conn, "
    SELECT gender, COUNT(*) as count 
    FROM students 
    WHERE status!='Deleted' 
    GROUP BY gender
");

$male = 0;
$female = 0;
while ($g = mysqli_fetch_assoc($genderData)) {
    if ($g['gender'] === 'Male') $male = $g['count'];
    if ($g['gender'] === 'Female') $female = $g['count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; }
        .container { max-width: 1100px; margin: auto; padding: 20px; }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 18px;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
            text-align: center;
        }

        .card h3 {
            margin: 0;
            font-size: 14px;
            color: #555;
        }

        .card p {
            font-size: 26px;
            font-weight: bold;
            margin-top: 6px;
        }

        .chart-box {
            width: 220px;
            height: 220px;
            margin-bottom: 25px;
        }

        #genderChart {
            width: 100% !important;
            height: 100% !important;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .activity-table th {
            background: #2563eb;
            color: white;
            padding: 10px;
            text-align: left;
        }

        .activity-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="top-bar">
        <h2>Dashboard</h2>
        <div>
            <a href="students.php">Manage Students</a> |
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h3>Total Students</h3>
            <p><?= $total ?></p>
        </div>
        <div class="card">
            <h3>Active</h3>
            <p><?= $active ?></p>
        </div>
        <div class="card">
            <h3>Inactive</h3>
            <p><?= $inactive ?></p>
        </div>
    </div>

    <h3>Gender Distribution</h3>
    <div class="chart-box">
        <canvas id="genderChart"></canvas>
    </div>

    <h3>Recent Activity</h3>
    <table class="activity-table">
        <tr>
            <th>Action</th>
            <th>Student</th>
            <th>Time</th>
        </tr>
        <?php while ($log = mysqli_fetch_assoc($logs)): ?>
        <tr>
            <td><?= htmlspecialchars($log['action']) ?></td>
            <td><?= htmlspecialchars($log['name'] ?? 'N/A') ?></td>
            <td><?= $log['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

</div>

<script>
const ctx = document.getElementById('genderChart').getContext('2d');
new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Male', 'Female'],
        datasets: [{
            data: [<?= $male ?>, <?= $female ?>],
            backgroundColor: ['#60a5fa', '#f472b6']
        }]
    },
    options: {
        responsive: false,
        maintainAspectRatio: false
    }
});
</script>

</body>
</html>
