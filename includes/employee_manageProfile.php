<?php
$pdo = include '../config/database.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../index.php');
    exit();
}

$employeeId = $_SESSION['userId'];
$success = false;

// Fetch employee profile
$stmt = $pdo->prepare("SELECT name, email, phoneNumber, address, profileImage FROM employees WHERE employeeId = :employeeId");
$stmt->execute(['employeeId' => $employeeId]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    echo "<p style='color: red;'>Employee profile not found. Please contact admin.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    // Handle profile picture upload if provided
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = '../uploads/';
        $fileName = basename($_FILES['photo']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            $photoPath = 'uploads/' . $fileName;
            $update = $pdo->prepare("UPDATE employees SET name = :name, email = :email, phoneNumber = :phone, address = :address, profileImage = :photo WHERE employeeId = :employeeId");
            $update->execute(['name' => $name, 'email' => $email, 'phone' => $phone, 'address' => $address, 'photo' => $photoPath, 'employeeId' => $employeeId]);
        }
    } else {
        $update = $pdo->prepare("UPDATE employees SET name = :name, email = :email, phoneNumber = :phone, address = :address WHERE employeeId = :employeeId");
        $update->execute(['name' => $name, 'email' => $email, 'phone' => $phone, 'address' => $address, 'employeeId' => $employeeId]);
    }

    $success = true;
    // Re-fetch updated info
    $stmt->execute(['employeeId' => $employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
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
                <a href="logout.php" class="logout" onclick="return confirm('Are you sure you want to log out?');">Log Out</a>
            </div>
        </div>
    </div>

    <div class="main_content">
        <h2>Update Profile</h2>
        <?php if ($success): ?>
            <p style="color: green; font-weight: bold;">Profile updated successfully!</p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Name:</label><br>
            <input type="text" name="name" value="<?= htmlspecialchars($employee['name']) ?>" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required><br><br>

            <label>Phone:</label><br>
            <input type="text" name="phone" value="<?= htmlspecialchars($employee['phoneNumber']) ?>" required><br><br>

            <label>Address:</label><br>
            <input type="text" name="address" value="<?= htmlspecialchars($employee['address']) ?>" required><br><br>

            <label>Profile Picture:</label><br>
            <?php if (!empty($employee['profileImage'])): ?>
                <img src="../<?= htmlspecialchars($employee['profileImage']) ?>" width="100" alt="Profile Picture"><br>
            <?php endif; ?>
            <input type="file" name="photo" accept="image/*"><br><br>

            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>

</html>
