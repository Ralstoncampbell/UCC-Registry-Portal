<!--Name of Enterprise App: ucc_registrar
Developers: Ralston Campbell, Geordi Duncan
Version: 3.0 
Version Date: 06/04/2025
Purpose: A page that allows the admin to edit student details. -->
<?php
session_start();
include "db.php";

// Access control
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$student = [];
$message = "";

// Fetch student data
if (isset($_GET["student_id"])) {
    $student_id = $_GET["student_id"];
    $sql = "SELECT * FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
}

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST["student_id"];
    $first_name = trim($_POST["first_name"]);
    $middle_name = trim($_POST["middle_name"]);
    $last_name = trim($_POST["last_name"]);
    $personal_email = trim($_POST["personal_email"]);
    $student_email = trim($_POST["student_email"]);
    $mobile = trim($_POST["mobile"]);
    $home_number = trim($_POST["home_number"]);
    $work_number = trim($_POST["work_number"]);
    $home_address = trim($_POST["home_address"]);
    $next_of_kin = trim($_POST["next_of_kin"]);
    $next_of_kin_contact = trim($_POST["next_of_kin_contact"]);
    $program = trim($_POST["program"]);
    $gpa = floatval($_POST["gpa"]);

    $sql = "UPDATE students SET first_name=?, middle_name=?, last_name=?, personal_email=?, student_email=?, 
            mobile=?, home_number=?, work_number=?, home_address=?, next_of_kin=?, next_of_kin_contact=?, program=?, gpa=? 
            WHERE student_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssdi", $first_name, $middle_name, $last_name, $personal_email, $student_email,
        $mobile, $home_number, $work_number, $home_address, $next_of_kin, $next_of_kin_contact, $program, $gpa, $student_id);

    if ($stmt->execute()) {
        $message = "✅ Student updated successfully!";
    } else {
        $message = "❌ Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Optional Sidebar Placeholder -->
        <div class="col-md-2 bg-light pt-4">
            <h5 class="text-center">Admin Panel</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="admin_dashboard.php" class="nav-link">Dashboard</a></li>
                <li class="nav-item"><a href="add_student.php" class="nav-link">Add Student</a></li>
                <li class="nav-item"><a href="edit_student.php" class="nav-link active">Edit Student</a></li>
            </ul>
        </div>
        <div class="col-md-10 p-4">
            <h2>Edit Student Record</h2>
            <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?= $message ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['student_id'] ?? '') ?>">

                <?php
                function inputRow($label, $name, $value, $required = false) {
                    $req = $required ? "required" : "";
                    echo '<div class="form-group row">
                            <label class="col-sm-3 col-form-label">' . $label . '</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="' . $name . '" value="' . htmlspecialchars($value ?? '') . '" ' . $req . '>
                            </div>
                          </div>';
                }

                inputRow("First Name", "first_name", $student["first_name"], true);
                inputRow("Middle Name", "middle_name", $student["middle_name"]);
                inputRow("Last Name", "last_name", $student["last_name"], true);
                inputRow("Personal Email", "personal_email", $student["personal_email"]);
                inputRow("Student Email", "student_email", $student["student_email"]);
                inputRow("Mobile Number", "mobile", $student["mobile"]);
                inputRow("Home Number", "home_number", $student["home_number"]);
                inputRow("Work Number", "work_number", $student["work_number"]);
                inputRow("Home Address", "home_address", $student["home_address"]);
                inputRow("Next of Kin", "next_of_kin", $student["next_of_kin"]);
                inputRow("Next of Kin Contact", "next_of_kin_contact", $student["next_of_kin_contact"]);
                inputRow("Program", "program", $student["program"]);
                ?>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">GPA</label>
                    <div class="col-sm-9">
                        <input type="number" step="0.01" class="form-control" name="gpa" value="<?= htmlspecialchars($student['gpa'] ?? '') ?>">
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">Update Student</button>
                    <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
