<?php
session_start();
if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'employee') {
header("Location:/Archube/Archcube_payroll/login.php"); // or employee_login.php, if separate
    exit;
}

$conn = include('../config/database.php');

// Get employee details (optional, for welcome message)
$employeeId = $_SESSION['employeeId'] ?? null;
$stmt = $conn->prepare("SELECT name FROM employees WHERE employeeId = ?");
$stmt->execute([$employeeId]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="/Archube/Archcube_payroll/assets/styles.css"> <!-- Update path if needed -->
</head>
<body>
    <header>
        <h2>Welcome, <?= htmlspecialchars($employee['name'] ?? $_SESSION['username']) ?>!</h2>
        <a href="/Archube/Archcube_payroll/logout.php">Logout</a>
    </header>

    <main>
        <section class="dashboard-section">
            <h3>Quick Actions</h3>
            <ul>
                <li><a href="view_logs.php">📅 View Attendance Logs</a></li>
                <li><a href="request_leave.php">📝 Request Leave</a></li>
                <li><a href="request_advance.php">💰 Request Cash Advance</a></li>
                <li><a href="view_payslips.php">📄 View Payslips</a></li>
                <li><a href="account_settings.php">⚙️ Account Settings</a></li>
            </ul>
        </section>

        <section class="notifications">
            <h3>Notifications</h3>
            <ul>
                <?php
                $notif_stmt = $conn->prepare("
                    SELECT message, notifyDate 
                    FROM notifications 
                    WHERE (employeeId = :eid OR employeeId IS NULL) 
                      AND visibleTo IN ('employee', 'both') 
                    ORDER BY notifyDate DESC 
                    LIMIT 5
                ");
                $notif_stmt->execute(['eid' => $employeeId]);
                $notifs = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($notifs) {
                    foreach ($notifs as $n) {
                        echo "<li><strong>" . date("M d, Y", strtotime($n['notifyDate'])) . ":</strong> " . htmlspecialchars($n['message']) . "</li>";
                    }
                } else {
                    echo "<li>No notifications yet.</li>";
                }
                ?>
            </ul>
        </section>
    </main>
</body>
</html>
