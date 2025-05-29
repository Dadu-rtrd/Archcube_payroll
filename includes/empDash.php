<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Manila');
$pdo = include '../config/database.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../index.php');
    exit();
}

$userId = $_SESSION['userId'];
$username = htmlspecialchars($_SESSION['username']);

// Fetch notifications for employee
$stmt = $pdo->prepare("SELECT * FROM notifications 
    WHERE employeeId = ? AND (visibleTo = 'employee' OR visibleTo = 'both') AND isRead = FALSE 
    ORDER BY notifyDate DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

// Fetch upcoming birthdays for everyone (optional)
$birthdays = $pdo->query("
    SELECT name, DATE_FORMAT(birthDate, '%M %d') AS birthdate 
    FROM employees 
    WHERE MONTH(birthDate) = MONTH(CURDATE()) 
      AND DAY(birthDate) >= DAY(CURDATE())
    ORDER BY DAY(birthDate)
    LIMIT 5
")->fetchAll();

// Fetch leave requests count for this employee (pending)
$pendingLeaves = $pdo->prepare("SELECT COUNT(*) FROM leave_requests WHERE employeeId = ? AND status = 'pending'");
$pendingLeaves->execute([$userId]);
$pendingLeavesCount = $pendingLeaves->fetchColumn();

// Fetch advance requests count for this employee (pending)
$pendingAdvance = $pdo->prepare("SELECT COUNT(*) FROM advancePayments WHERE employeeId = ? AND status = 'pending'");
$pendingAdvance->execute([$userId]);
$pendingAdvanceCount = $pendingAdvance->fetchColumn();
?>

<html lang="en">

<head>
    <link rel="stylesheet" href="../assets/css/dashboardStyle.css" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Employee Dashboard</title>
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
                <a href="employee_payslip.php">My Payslip</a>
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
        <h1 style="font-size:2em; color:#074799; margin-bottom:10px;">
            Welcome, <?= $username ?>!
        </h1>

        <div class="card_container">

            <div class="card card_pendingLeaves">
                <h2>Pending Leave Requests</h2>
                <p><?= $pendingLeavesCount ?></p>
                <span>Leaves awaiting approval.</span>
            </div>

            <div class="card card_pendingAdvance">
                <h2>Pending Advance Requests</h2>
                <p><?= $pendingAdvanceCount ?></p>
                <span>Advance requests awaiting approval.</span>
            </div>

            <div class="card card_birthdays">
                <h2>Upcoming Birthdays</h2>
                <ul>
                    <?php foreach (array_slice($birthdays, 0, 3) as $b): ?>
                        <li><?= htmlspecialchars($b['name']) ?> - <?= $b['birthdate'] ?></li>
                    <?php endforeach; ?>
                    <?php if (count($birthdays) > 3): ?>
                        <li style="color:#888;">...and more</li>
                    <?php endif; ?>
                </ul>
                <span>Celebrate your coworkers' birthdays!</span>
            </div>
        </div>

        <?php if (!empty($notifications)): ?>
            <div class="dashboard-notification" style="margin-top: 20px;">
                <span style="font-size:1.3em; margin-right:8px;">🔔 Notifications</span>
                <?php foreach ($notifications as $notif): ?>
                    <div><?= htmlspecialchars($notif['message']) ?></div>
                <?php endforeach; ?>
            </div>
            <?php
            // Mark notifications as read
            $notifIds = array_column($notifications, 'notificationId');
            if (!empty($notifIds)) {
                $ids = implode(',', array_map('intval', $notifIds));
                $pdo->exec("UPDATE notifications SET isRead = 1 WHERE notificationId IN ($ids)");
            }
            ?>
        <?php endif; ?>

    </div>

    <script>
        function confirmLogout() {
            return confirm('Are you sure you want to log out?');
        }
    </script>
</body>

</html>
