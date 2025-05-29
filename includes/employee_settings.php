<?php
$pdo = include '../config/database.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../index.php');
    exit();
}
?>

<html lang="en">

<head>
    <link rel="stylesheet" href="../assets/css/settingStyle.css" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Employee Settings</title>
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
        <div class="settings_form">
            <form action="#" method="post">
                <div class="settings_card">
                    <div class="settings_card_title">
                        <span>⚙️</span> Account Settings
                    </div>
                    <div class="settings_row">
                        <div class="settings_row_info">
                            <span class="settings_row_title">Change Password</span>
                            <span class="settings_row_desc">Update your login password</span>
                        </div>
                        <div class="settings_row_action">
                            <a href="employee_changePass.php" class="settings_btn">Change</a>
                        </div>
                    </div>
                    <div class="settings_row">
                        <div class="settings_row_info">
                            <span class="settings_row_title">Update Profile</span>
                            <span class="settings_row_desc">Modify your personal information</span>
                        </div>
                        <div class="settings_row_action">
                            <a href="updateProfile.php" class="settings_btn">Update</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmLogout() {
            return confirm('Are you sure you want to log out?');
        }
    </script>
</body>

</html>
