<?php
$pdo = include '../config/database.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../index.php');
    exit();
}

$employeeId = $_SESSION['userId'];
$filter = $_GET['filter'] ?? 'all';

$sql = "SELECT a.attendanceDate, a.timeIn, a.timeOut, a.status, a.remarks,
               s.siteName AS site, e.name,
               IFNULL(SUM(b.amount), 0) AS totalBenefits,
               IFNULL(SUM(d.amount), 0) AS totalDeductions
        FROM attendance a
        JOIN employees e ON a.employeeId = e.employeeId
        LEFT JOIN sites s ON e.siteId = s.siteId
        LEFT JOIN payrollPeriod pp ON a.attendanceDate BETWEEN pp.cutOffFrom AND pp.cutOffTo
        LEFT JOIN benefits b ON b.employeeId = e.employeeId AND b.payrollPeriodId = pp.payrollPeriodId
        LEFT JOIN deductions d ON d.employeeId = e.employeeId AND d.payrollPeriodId = pp.payrollPeriodId
        WHERE a.employeeId = :employeeId";

switch ($filter) {
    case 'last7':
        $sql .= " AND a.attendanceDate >= CURDATE() - INTERVAL 7 DAY";
        break;
    case 'thisMonth':
        $sql .= " AND MONTH(a.attendanceDate) = MONTH(CURDATE()) AND YEAR(a.attendanceDate) = YEAR(CURDATE())";
        break;
    default:
        // all records
}

$sql .= " GROUP BY a.attendanceId ORDER BY a.attendanceDate DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['employeeId' => $employeeId]);
$records = $stmt->fetchAll();
?>

<html lang="en">

<head>
    <link rel="stylesheet" href="../assets/css/settingStyle.css" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Attendance</title>
</head>

<body>
    <div class="side_bar">
        <h1>Archcube Payroll</h1>
        <div class="side_bar_container">
            <div class="side_bar_item">
                <a href="empDash.php">Dashboard</a>
            </div>
            <div class="side_bar_item">
                <a href="employeeAttendanceView.php">My Attendance</a>
            </div>
            <div class="side_bar_item">
                <a href="payslip.php">My Payslip</a>
            </div>
            <div class="side_bar_item">
                <a href="leaveRequest.php">Request Leave</a>
            </div>
            <div class="side_bar_item">
                <a href="advanceRequest.php">Request Advance</a>
            </div>
            <div class="side_bar_item">
                <a href="employee_settings.php">Settings</a>
            </div>
            <div class="side_bar_item">
                <a href="logout.php" class="logout" onclick="return confirmLogout();">Log Out</a>
            </div>
        </div>
    </div>

    <div class="main_content">
        <h2>My Attendance</h2>

        <form method="get" style="margin-bottom: 20px;" id="filterForm">
            <label for="filter">Filter by:</label>
            <select name="filter" id="filter">
                <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
                <option value="last7" <?= $filter === 'last7' ? 'selected' : '' ?>>Last 7 Days</option>
                <option value="thisMonth" <?= $filter === 'thisMonth' ? 'selected' : '' ?>>This Month</option>
            </select>
        </form>

        <table border="1" cellpadding="10">
            <tr>
                <th>Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Site</th>
                <th>Name</th>
                <th>Total Benefits</th>
                <th>Total Deductions</th>
            </tr>
            <tbody id="attendanceTableBody">
                <?php foreach ($records as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['attendanceDate']) ?></td>
                        <td><?= htmlspecialchars($row['timeIn']) ?></td>
                        <td><?= htmlspecialchars($row['timeOut']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['remarks']) ?></td>
                        <td><?= htmlspecialchars($row['site']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['totalBenefits']) ?></td>
                        <td><?= htmlspecialchars($row['totalDeductions']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function confirmLogout() {
            return confirm('Are you sure you want to log out?');
        }

        document.getElementById('filter').addEventListener('change', function () {
            const filterValue = this.value;

            fetch('fetch_attendance.php?filter=' + filterValue)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok.');
                    return response.text();
                })
                .then(data => {
                    document.getElementById('attendanceTableBody').innerHTML = data;
                })
                .catch(error => {
                    alert('Error fetching data: ' + error.message);
                });
        });
    </script>
</body>

</html>
