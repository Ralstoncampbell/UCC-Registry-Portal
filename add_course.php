<!--
Name of Enterprise App: ucc_registrar
Developers: Ralston Campbell, Geordi Duncan
Version: 4.0 
Version Date: 5/4/2025
Purpose: A php function that adds course to the database.
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
    $course_code = trim($_POST["course_code"] ?? "");
    $title = trim($_POST["title"] ?? "");
    $credits = $_POST["credits"] ?? "";
    $degree_level = $_POST["degree_level"] ?? "";
    $prerequisites = trim($_POST["prerequisites"] ?? "");

    if (empty($course_code) || empty($title) || empty($credits) || empty($degree_level)) {
        $messages[] = ["type" => "error", "text" => "All fields are required!"];
    } else {
        $check_sql = "SELECT course_code FROM courses WHERE course_code = ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("s", $course_code);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $messages[] = ["type" => "error", "text" => "Course code already exists!"];
        } else {
            $sql = "INSERT INTO courses (course_code, title, credits, degree_level, prerequisites) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiss", $course_code, $title, $credits, $degree_level, $prerequisites);

            if ($stmt->execute()) {
                $semester = trim($_POST["semester"] ?? "");
                $year = trim($_POST["year"] ?? "");
                $section = trim($_POST["section"] ?? "");
                $lecturers = trim($_POST["lecturers"] ?? "");
                $day = trim($_POST["day"] ?? "");
                $time = trim($_POST["time"] ?? "");
                $location = trim($_POST["location"] ?? "");

                $schedule_sql = "INSERT INTO course_schedule (course_code, semester, year, section, lecturers, day, time, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_sched = $conn->prepare($schedule_sql);
                $stmt_sched->bind_param("ssisssss", $course_code, $semester, $year, $section, $lecturers, $day, $time, $location);
                $stmt_sched->execute();
                $stmt_sched->close();

                $messages[] = ["type" => "success", "text" => "Course and schedule added successfully!"];
                
                // Clear form data after successful submission
                $course_code = $title = $credits = $prerequisites = $semester = $year = $section = $lecturers = $day = $time = $location = "";
            } else {
                $messages[] = ["type" => "error", "text" => "Error: " . $conn->error];
            }

            $stmt->close();
        }
        $stmt_check->close();
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
    <title>Add Course | UCC Registry</title>
    <link rel="stylesheet" href="modern-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .logo img {
            height: 40px;
            margin-right: 10px;
        }
        
        .logo-text {
            font-size: 20px;
            font-weight: 600;
            color: #2563eb;
            display: flex;
            align-items: center;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .form-section {
            background-color: rgba(23, 162, 184, 0.1);
            border-left: 3px solid var(--primary);
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <a href="admin_dashboard.php" class="logo">
                <img src="ucc_logo.png" alt="UCC Logo">
                <span class="logo-text">UCC Registry</span>
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
                        <i class="fas fa-book"></i>
                        Add New Course
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
                    <h2>Course Information</h2>
                </div>
                <div class="tab-content" style="padding: 1.5rem;">
                    <form method="post" class="mb-4">
                        <div class="form-grid">
                            <div>
                                <label for="course_code" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Course Code:</label>
                                <input type="text" name="course_code" id="course_code" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($course_code ?? '') ?>">
                            </div>
                            
                            <div>
                                <label for="title" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Course Title:</label>
                                <input type="text" name="title" id="title" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($title ?? '') ?>">
                            </div>
                            
                            <div>
                                <label for="credits" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Credits:</label>
                                <input type="number" name="credits" id="credits" min="1" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($credits ?? '') ?>">
                            </div>
                            
                            <div>
                                <label for="degree_level" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Degree Level:</label>
                                <select name="degree_level" id="degree_level" required class="search-input" style="width: 100%;">
                                    <option value="Undergraduate" <?= ($degree_level ?? '') === 'Undergraduate' ? 'selected' : '' ?>>Undergraduate</option>
                                    <option value="Graduate" <?= ($degree_level ?? '') === 'Graduate' ? 'selected' : '' ?>>Graduate</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="prerequisites" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Prerequisites:</label>
                                <input type="text" name="prerequisites" id="prerequisites" class="search-input" style="width: 100%;" value="<?= htmlspecialchars($prerequisites ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3 style="font-size: 1.1rem; margin-bottom: 1rem;">
                                <i class="fas fa-calendar-alt"></i>
                                Course Schedule Information
                            </h3>
                            
                            <div class="form-grid">
                                <div>
                                    <label for="semester" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Semester:</label>
                                    <select name="semester" id="semester" required class="search-input" style="width: 100%;">
                                        <option value="Spring" <?= ($semester ?? '') === 'Spring' ? 'selected' : '' ?>>Spring</option>
                                        <option value="Summer" <?= ($semester ?? '') === 'Summer' ? 'selected' : '' ?>>Summer</option>
                                        <option value="Fall" <?= ($semester ?? '') === 'Fall' ? 'selected' : '' ?>>Fall</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="year" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Year:</label>
                                    <input type="number" name="year" id="year" min="2000" max="2050" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($year ?? date('Y')) ?>">
                                </div>
                                
                                <div>
                                    <label for="section" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Section:</label>
                                    <input type="text" name="section" id="section" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($section ?? '') ?>">
                                </div>
                                
                                <div>
                                    <label for="lecturers" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Lecturer(s):</label>
                                    <input type="text" name="lecturers" id="lecturers" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($lecturers ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="form-grid" style="margin-top: 1rem;">
                                <div>
                                    <label for="day" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Day:</label>
                                    <select name="day" id="day" required class="search-input" style="width: 100%;">
                                        <option value="Monday" <?= ($day ?? '') === 'Monday' ? 'selected' : '' ?>>Monday</option>
                                        <option value="Tuesday" <?= ($day ?? '') === 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                                        <option value="Wednesday" <?= ($day ?? '') === 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                                        <option value="Thursday" <?= ($day ?? '') === 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                                        <option value="Friday" <?= ($day ?? '') === 'Friday' ? 'selected' : '' ?>>Friday</option>
                                        <option value="Saturday" <?= ($day ?? '') === 'Saturday' ? 'selected' : '' ?>>Saturday</option>
                                        <option value="Sunday" <?= ($day ?? '') === 'Sunday' ? 'selected' : '' ?>>Sunday</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="time" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Time:</label>
                                    <input type="text" name="time" id="time" required class="search-input" style="width: 100%;" placeholder="e.g., 9:00 AM - 12:00 PM" value="<?= htmlspecialchars($time ?? '') ?>">
                                </div>
                                
                                <div>
                                    <label for="location" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Location:</label>
                                    <input type="text" name="location" id="location" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($location ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i>
                                Add Course
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
            <p>&copy; <?= date('Y') ?> UCC Registry System | Version 1.0</p>
            <div class="footer-links">
                <a href="grades_reference.php" class="footer-link">Grading System</a>
                <a href="admin_dashboard.php" class="footer-link">Back to Dashboard</a>
            </div>
        </div>
    </footer>
</body>
</html>