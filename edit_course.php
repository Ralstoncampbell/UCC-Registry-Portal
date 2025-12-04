<!--
Name of Enterprise App: UCC Registrar
Developers: Ralston Campbell
Version: 4.0 
Version Date: Dec/2/2025
Purpose: A php function that updates edits to a selected course to the database.
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
$course = [];
$schedule = [];

if (isset($_GET["course_code"])) {
    $course_code = $_GET["course_code"];

    $sql = "SELECT * FROM courses WHERE course_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $course_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();

    $sql_sched = "SELECT * FROM course_schedule WHERE course_code = ?";
    $stmt_sched = $conn->prepare($sql_sched);
    $stmt_sched->bind_param("s", $course_code);
    $stmt_sched->execute();
    $result_sched = $stmt_sched->get_result();
    $schedule = $result_sched->fetch_assoc();
    $stmt_sched->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_code = $_POST["course_code"];
    $title = trim($_POST["title"]);
    $credits = (int)$_POST["credits"];
    $degree_level = $_POST["degree_level"];
    $prerequisites = trim($_POST["prerequisites"]);

    $semester = trim($_POST["semester"]);
    $year = (int)$_POST["year"];
    $section = trim($_POST["section"]);
    $lecturers = trim($_POST["lecturers"]);
    $day = trim($_POST["day"]);
    $time = trim($_POST["time"]);
    $location = trim($_POST["location"]);

    if (empty($course_code) || empty($title) || empty($credits) || empty($degree_level)) {
        $messages[] = ["type" => "error", "text" => "All fields are required!"];
    } else {
        $sql = "UPDATE courses SET title=?, credits=?, degree_level=?, prerequisites=? WHERE course_code=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisss", $title, $credits, $degree_level, $prerequisites, $course_code);
        
        if ($stmt->execute()) {
            $check_sql = "SELECT course_code FROM course_schedule WHERE course_code = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $course_code);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $update_sql = "UPDATE course_schedule SET semester=?, year=?, section=?, lecturers=?, day=?, time=?, location=? WHERE course_code=?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sissssss", $semester, $year, $section, $lecturers, $day, $time, $location, $course_code);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                $insert_sql = "INSERT INTO course_schedule (course_code, semester, year, section, lecturers, day, time, location)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ssisssss", $course_code, $semester, $year, $section, $lecturers, $day, $time, $location);
                $insert_stmt->execute();
                $insert_stmt->close();
            }

            $check_stmt->close();
            $messages[] = ["type" => "success", "text" => "Course and schedule updated successfully!"];
            
            // Refresh the course and schedule data to reflect changes
            $sql = "SELECT * FROM courses WHERE course_code = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $course_code);
            $stmt->execute();
            $result = $stmt->get_result();
            $course = $result->fetch_assoc();
            $stmt->close();

            $sql_sched = "SELECT * FROM course_schedule WHERE course_code = ?";
            $stmt_sched = $conn->prepare($sql_sched);
            $stmt_sched->bind_param("s", $course_code);
            $stmt_sched->execute();
            $result_sched = $stmt_sched->get_result();
            $schedule = $result_sched->fetch_assoc();
            $stmt_sched->close();
        } else {
            $messages[] = ["type" => "error", "text" => "Error updating course: " . $conn->error];
            $stmt->close();
        }
       
    }
}

// Function to get the first letter of the name for avatar
function getInitial($name) {
    return strtoupper(substr($name, 0, 1));
}

// Determine if course exists
$courseExists = !empty($course);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course | UCC Registry</title>
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
        
        .course-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .course-badge {
            background-color: #e2e8f0;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 1rem;
            font-weight: 600;
            margin-left: 1rem;
        }
        
        .no-course {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .no-course i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
        
        .no-course p {
            font-size: 1.125rem;
            color: #64748b;
            margin-bottom: 1.5rem;
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
                    <div class="course-header">
                        <h2>
                            <i class="fas fa-edit"></i>
                            Edit Course
                        </h2>
                        <?php if ($courseExists): ?>
                            <span class="course-badge"><?= htmlspecialchars($course_code) ?></span>
                        <?php endif; ?>
                    </div>
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
            
            <?php if ($courseExists): ?>
                <div class="data-table-container">
                    <div class="data-table-header">
                        <h2>Course Information</h2>
                    </div>
                    <div class="tab-content" style="padding: 1.5rem;">
                        <form method="post" class="mb-4">
                            <input type="hidden" name="course_code" value="<?= htmlspecialchars($course['course_code']) ?>">
                            
                            <div class="form-grid">
                                <div>
                                    <label for="course_code_display" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Course Code:</label>
                                    <input type="text" id="course_code_display" value="<?= htmlspecialchars($course['course_code']) ?>" disabled class="search-input" style="width: 100%; background-color: #f1f5f9;">
                                </div>
                                
                                <div>
                                    <label for="title" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Course Title:</label>
                                    <input type="text" name="title" id="title" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($course['title']) ?>">
                                </div>
                                
                                <div>
                                    <label for="credits" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Credits:</label>
                                    <input type="number" name="credits" id="credits" min="1" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($course['credits']) ?>">
                                </div>
                                
                                <div>
                                    <label for="degree_level" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Degree Level:</label>
                                    <select name="degree_level" id="degree_level" required class="search-input" style="width: 100%;">
                                        <option value="Undergraduate" <?= ($course['degree_level'] === 'Undergraduate') ? 'selected' : '' ?>>Undergraduate</option>
                                        <option value="Graduate" <?= ($course['degree_level'] === 'Graduate') ? 'selected' : '' ?>>Graduate</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="prerequisites" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Prerequisites:</label>
                                    <input type="text" name="prerequisites" id="prerequisites" class="search-input" style="width: 100%;" value="<?= htmlspecialchars($course['prerequisites']) ?>">
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
                                            <option value="Spring" <?= ($schedule['semester'] ?? '') === 'Spring' ? 'selected' : '' ?>>Spring</option>
                                            <option value="Summer" <?= ($schedule['semester'] ?? '') === 'Summer' ? 'selected' : '' ?>>Summer</option>
                                            <option value="Fall" <?= ($schedule['semester'] ?? '') === 'Fall' ? 'selected' : '' ?>>Fall</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="year" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Year:</label>
                                        <input type="number" name="year" id="year" min="2000" max="2050" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($schedule['year'] ?? date('Y')) ?>">
                                    </div>
                                    
                                    <div>
                                        <label for="section" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Section:</label>
                                        <input type="text" name="section" id="section" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($schedule['section'] ?? '') ?>">
                                    </div>
                                    
                                    <div>
                                        <label for="lecturers" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Lecturer(s):</label>
                                        <input type="text" name="lecturers" id="lecturers" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($schedule['lecturers'] ?? '') ?>">
                                    </div>
                                </div>
                                
                                <div class="form-grid" style="margin-top: 1rem;">
                                    <div>
                                        <label for="day" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Day:</label>
                                        <select name="day" id="day" required class="search-input" style="width: 100%;">
                                            <option value="Monday" <?= ($schedule['day'] ?? '') === 'Monday' ? 'selected' : '' ?>>Monday</option>
                                            <option value="Tuesday" <?= ($schedule['day'] ?? '') === 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                                            <option value="Wednesday" <?= ($schedule['day'] ?? '') === 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                                            <option value="Thursday" <?= ($schedule['day'] ?? '') === 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                                            <option value="Friday" <?= ($schedule['day'] ?? '') === 'Friday' ? 'selected' : '' ?>>Friday</option>
                                            <option value="Saturday" <?= ($schedule['day'] ?? '') === 'Saturday' ? 'selected' : '' ?>>Saturday</option>
                                            <option value="Sunday" <?= ($schedule['day'] ?? '') === 'Sunday' ? 'selected' : '' ?>>Sunday</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="time" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Time:</label>
                                        <input type="text" name="time" id="time" required class="search-input" style="width: 100%;" placeholder="e.g., 9:00 AM - 12:00 PM" value="<?= htmlspecialchars($schedule['time'] ?? '') ?>">
                                    </div>
                                    
                                    <div>
                                        <label for="location" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Location:</label>
                                        <input type="text" name="location" id="location" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($schedule['location'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div style="margin-top: 1.5rem;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Update Course
                                </button>
                                <a href="admin_dashboard.php#courses" class="btn btn-secondary" style="margin-left: 0.5rem;">
                                    <i class="fas fa-times"></i>
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <div class="no-course">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>No course found with the provided course code.</p>
                        <a href="admin_dashboard.php#courses" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Courses
                        </a>
                    </div>
                </div>
            <?php endif; ?>
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