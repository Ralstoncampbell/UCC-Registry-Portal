<!--
Name of Enterprise App: UCC Registrar
Developers: Ralston Campbell
Version: 4.0 
Version Date: Dec/2/2025
Purpose: A php function that adds students to the database.
-->

<?php
session_start();
include "db.php";

// Check if the user is logged in as admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Initialize messages array
$messages = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST["student_id"] ?? "";
    $first_name = trim($_POST["first_name"] ?? "");
    $middle_name = trim($_POST["middle_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $personal_email = trim($_POST["personal_email"] ?? "");
    $student_email = trim($_POST["student_email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $home_number = trim($_POST["home_number"] ?? "");
    $work_number = trim($_POST["work_number"] ?? "");
    $home_address = trim($_POST["home_address"] ?? "");
    $next_of_kin = trim($_POST["next_of_kin"] ?? "");
    $next_of_kin_contact = trim($_POST["next_of_kin_contact"] ?? "");
    $program = trim($_POST["program_of_study"] ?? "");
    $gpa = $_POST["gpa"] ?? "0";

    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($student_email) || empty($program)) {
        $messages[] = ["type" => "error", "text" => "Required fields are missing!"];
    } else {
        $check_email_sql = "SELECT student_id FROM students WHERE student_email = ? AND student_id != ?";
        $stmt_email = $conn->prepare($check_email_sql);
        $stmt_email->bind_param("si", $student_email, $student_id);
        $stmt_email->execute();
        $stmt_email->store_result();

        if ($stmt_email->num_rows > 0) {
            $messages[] = ["type" => "error", "text" => "This email is already in use by another student!"];
        } else {
            $stmt_email->close();

            $check_id_sql = "SELECT student_id FROM students WHERE student_id = ?";
            $stmt_check = $conn->prepare($check_id_sql);
            $stmt_check->bind_param("i", $student_id);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $stmt_check->close();

                $update_sql = "UPDATE students SET first_name=?, middle_name=?, last_name=?, personal_email=?, student_email=?, 
                           mobile=?, home_number=?, work_number=?, home_address=?, next_of_kin=?, next_of_kin_contact=?, program=?, gpa=? 
                           WHERE student_id=?";
                $stmt_update = $conn->prepare($update_sql);
                $stmt_update->bind_param("ssssssssssssdi", $first_name, $middle_name, $last_name, $personal_email, $student_email, 
                                     $mobile, $home_number, $work_number, $home_address, $next_of_kin, $next_of_kin_contact, 
                                     $program, $gpa, $student_id);

                if ($stmt_update->execute()) {
                    $messages[] = ["type" => "success", "text" => "Student record updated successfully!"];
                } else {
                    $messages[] = ["type" => "error", "text" => "Error updating record: " . $conn->error];
                }
                $stmt_update->close();
            } else {
                $stmt_check->close();

                $insert_sql = "INSERT INTO students (student_id, first_name, middle_name, last_name, personal_email, student_email, 
                            mobile, home_number, work_number, home_address, next_of_kin, next_of_kin_contact, program, gpa) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($insert_sql);
                $stmt_insert->bind_param("issssssssssssd", $student_id, $first_name, $middle_name, $last_name, $personal_email, 
                                     $student_email, $mobile, $home_number, $work_number, $home_address, $next_of_kin, 
                                     $next_of_kin_contact, $program, $gpa);

                if ($stmt_insert->execute()) {
                    $messages[] = ["type" => "success", "text" => "Student added successfully!"];
                } else {
                    $messages[] = ["type" => "error", "text" => "Error adding student: " . $conn->error];
                }
                $stmt_insert->close();
            }
        }
    }
}

// Function to get the first letter of the name for avatar
function getInitial($name) {
    return strtoupper(substr($name, 0, 1));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student | UCC Registry</title>
    <link rel="stylesheet" href="modern-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <a href="admin_dashboard.php" class="logo">
                <img src="ucc_logo.png" alt="UCC Logo" style="height: 40px; margin-right: 10px;">
                <span style="font-size: 20px; font-weight: 600; color: #2563eb; display: flex; align-items: center;">UCC Registry</span>
            </a>
            <div class="header-actions">
                <div class="user-menu">
                    <div class="user-avatar">
                        <?= getInitial($_SESSION['user']) ?>
                    </div>
                    <span><?= htmlspecialchars($_SESSION['user']) ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="dashboard">
        <div class="container">
            <div class="search-section mb-4">
                <div class="flex justify-between items-center">
                    <h2>
                        <i class="fas fa-user-plus"></i>
                        Add New Student
                    </h2>
                </div>
            </div>
            
            <!-- Messages -->
            <?php foreach ($messages as $message) : ?>
                <div class="search-section <?= $message['type'] === 'success' ? 'bg-success-light' : 'bg-danger-light' ?>" style="margin-bottom: 1rem; padding: 1rem;">
                    <div class="flex items-center gap-2">
                        <i class="fas <?= $message['type'] === 'success' ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger' ?>"></i>
                        <p class="<?= $message['type'] === 'success' ? 'text-success' : 'text-danger' ?>"><?= $message['text'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="data-table-container">
                <div class="data-table-header">
                    <h2>Student Information</h2>
                </div>
                <div class="tab-content" style="padding: 1.5rem;">
                    <form method="post" class="mb-4">
                        <div class="form-grid">
                            <div>
                                <label for="student_id" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Student ID:</label>
                                <input type="number" name="student_id" id="student_id" required class="search-input" style="width: 100%;">
                            </div>
                            
                            <div>
                                <label for="first_name" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">First Name:</label>
                                <input type="text" name="first_name" id="first_name" required class="search-input" style="width: 100%;">
                            </div>
                            
                            <div>
                                <label for="middle_name" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Middle Name:</label>
                                <input type="text" name="middle_name" id="middle_name" class="search-input" style="width: 100%;">
                            </div>
                            
                            <div>
                                <label for="last_name" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Last Name:</label>
                                <input type="text" name="last_name" id="last_name" required class="search-input" style="width: 100%;">
                            </div>
                            
                            <div>
                                <label for="personal_email" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Personal Email:</label>
                                <input type="email" name="personal_email" id="personal_email" class="search-input" style="width: 100%;">
                            </div>
                            
                            <div>
                                <label for="student_email" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Student Email:</label>
                                <input type="email" name="student_email" id="student_email" required class="search-input" style="width: 100%;">
                            </div>
                            
                            <div>
                                <label for="mobile" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Mobile:</label>
                                <input type="text" name="mobile" id="mobile" class="search-input" style="width: 100%;">
                            </div>
                            
                            <div>
                                <label for="home_number" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Home Number:</label>
                                <input type="text" name="home_number" id="home_number" class="search-input" style="width: 100%;">
                            </div>
                            
                            <div>
                                <label for="work_number" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Work Number:</label>
                                <input type="text" name="work_number" id="work_number" class="search-input" style="width: 100%;">
                            </div>
                            
                            <div class="form-grid-span-2" style="grid-column: span 2;">
                                <label for="home_address" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Home Address:</label>
                                <input type="text" name="home_address" id="home_address" class="search-input" style="width: 100%;">
                            </div>
                            
                            <div>
                                <label for="next_of_kin" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Next of Kin:</label>
                                <input type="text" name="next_of_kin" id="next_of_kin" class="search-input" style="width: 100%;">
                            </div>
                            
                            <div>
                                <label for="next_of_kin_contact" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Next of Kin Contact:</label>
                                <input type="text" name="next_of_kin_contact" id="next_of_kin_contact" class="search-input" style="width: 100%;">
                            </div>
                            
                            <div>
                                <label for="program_of_study" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Program of Study:</label>
                                <select name="program_of_study" id="program_of_study" required class="search-input" style="width: 100%;">
                                    <option value="">-- Select Program --</option>
                                    <optgroup label="Associate Degree (ASC)">
                                        <option value="ASC Business Studies">ASC Business Studies</option>
                                        <option value="ASC Management Information Systems">ASC Management Information Systems</option>
                                        <option value="ASC Hospitality and Tourism Management">ASC Hospitality and Tourism Management</option>
                                        <option value="ASC Criminal Justice">ASC Criminal Justice</option>
                                    </optgroup>
                                    <optgroup label="Bachelor Degree (BSc)">
                                        <option value="BSc Information Technology">BSc Information Technology</option>
                                        <option value="BSc Business Administration">BSc Business Administration</option>
                                        <option value="BSc Computer Science">BSc Computer Science</option>
                                        <option value="BSc Hospitality and Tourism Management">BSc Hospitality and Tourism Management</option>
                                        <option value="BSc Nursing">BSc Nursing</option>
                                        <option value="BSc Accounting">BSc Accounting</option>
                                        <option value="BSc Marketing">BSc Marketing</option>
                                        <option value="BSc Human Resource Management">BSc Human Resource Management</option>
                                    </optgroup>
                                </select>
                            </div>
                            
                            <div>
                                <label for="gpa" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">GPA:</label>
                                <input type="number" name="gpa" id="gpa" step="0.01" min="0" max="4.0" class="search-input" style="width: 100%;" value="0">
                            </div>
                        </div>
                        
                        <div style="margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Student
                            </button>
                            <button type="reset" class="btn btn-secondary" style="margin-left: 0.5rem;">
                                <i class="fas fa-undo"></i>
                                Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-content">
            <p>University of the Commonwealth Caribbean - Registry Department</p>
            <p>&copy; <?= date('Y') ?> UCC Registry System | Version 4.0</p>
            <p>Website designed by Ralston Campbell
            <img src="Ralston_logo_icon.jpg" 
                 alt="logo" 
                 style="width:25px; height:25px; margin-left:6px; vertical-align:middle; border-radius:50%;"></p>
            <div class="footer-links">
                <a href="grades_reference.php" class="footer-link">Grading System</a>
                <a href="admin_dashboard.php" class="footer-link">Back to Dashboard</a>
            </div>
        </div>
    </footer>
</body>
</html>