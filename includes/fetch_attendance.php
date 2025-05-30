<?php
$pdo = include '../config/database.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    http_response_code(403);
    exit('Unauthorized access');
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

foreach ($records as $row): ?>
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
