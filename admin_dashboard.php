<!--
Name of Enterprise App: UCC Registrar
Developers: Ralston Campbell
Version: 4.0 
Version Date: Dec/2/2025
Purpose:The main php platform for admin users dashboard.
-->

<?php
session_start();
include "db.php";

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get quick summary stats for dashboard
$students_count = $conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'];
$lecturers_count = $conn->query("SELECT COUNT(*) AS count FROM lecturers")->fetch_assoc()['count'];
$courses_count = $conn->query("SELECT COUNT(*) AS count FROM courses")->fetch_assoc()['count'];
$enrollments_count = $conn->query("SELECT COUNT(*) AS count FROM course_enrollment")->fetch_assoc()['count'];

// Set default search parameters
$search_keyword = '';
$search_type = 'all';
$search_results = [];
$has_searched = false;

// Process search form if submitted
if (isset($_GET['search']) && !empty($_GET['keyword'])) {
    $has_searched = true;
    $search_keyword = trim($_GET['keyword']);
    $search_type = $_GET['type'] ?? 'all';
    
    // Build the search query based on the search type
    switch ($search_type) {
        case 'id':
            $query = "SELECT * FROM students WHERE student_id LIKE ?";
            $param = "%{$search_keyword}%";
            break;
        case 'name':
            $query = "SELECT * FROM students WHERE 
                      first_name LIKE ? OR 
                      last_name LIKE ? OR 
                      CONCAT(first_name, ' ', last_name) LIKE ?";
            $param = "%{$search_keyword}%";
            break;
        case 'email':
            $query = "SELECT * FROM students WHERE 
                      student_email LIKE ? OR 
                      personal_email LIKE ?";
            $param = "%{$search_keyword}%";
            break;
        case 'program':
            $query = "SELECT * FROM students WHERE program LIKE ?";
            $param = "%{$search_keyword}%";
            break;
        default: // 'all'
            $query = "SELECT * FROM students WHERE 
                      student_id LIKE ? OR 
                      first_name LIKE ? OR 
                      last_name LIKE ? OR 
                      CONCAT(first_name, ' ', last_name) LIKE ? OR 
                      student_email LIKE ? OR 
                      personal_email LIKE ? OR 
                      program LIKE ?";
            $param = "%{$search_keyword}%";
            break;
    }
    
    $stmt = $conn->prepare($query);
    
    if ($search_type === 'name') {
        $stmt->bind_param("sss", $param, $param, $param);
    } elseif ($search_type === 'email') {
        $stmt->bind_param("ss", $param, $param);
    } elseif ($search_type === 'all') {
        $stmt->bind_param("sssssss", $param, $param, $param, $param, $param, $param, $param);
    } else {
        $stmt->bind_param("s", $param);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }
}

// Function to determine academic standing based on GPA
function getAcademicStanding($gpa) {
    if ($gpa >= 3.67) {
        return ['status' => 'Summa Cum Laude', 'class' => 'badge-success'];
    } elseif ($gpa >= 3.50) {
        return ['status' => 'Magna Cum Laude', 'class' => 'badge-success'];
    } elseif ($gpa >= 3.00) {
        return ['status' => 'Cum Laude', 'class' => 'badge-success'];
    } elseif ($gpa >= 2.00) {
        return ['status' => 'Good Standing', 'class' => 'badge-success'];
    } else {
        return ['status' => 'Academic Probation', 'class' => 'badge-danger'];
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
    <title>UCC Registry - Admin Dashboard</title>
    <link rel="stylesheet" href="modern-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <a href="admin_dashboard.php" class="logo">
            <img src="ucc_logo.png" alt="UCC Logo" style="height: 100px; margin-right: 10px;">
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
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="container">
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-title">Total Students</div>
                    <div class="stat-value"><?= $students_count ?></div>
                    <a href="#" class="stat-link" onclick="setActiveTab('students')">
                        View All
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Total Lecturers</div>
                    <div class="stat-value"><?= $lecturers_count ?></div>
                    <a href="#" class="stat-link" onclick="setActiveTab('lecturers')">
                        View All
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Total Courses</div>
                    <div class="stat-value"><?= $courses_count ?></div>
                    <a href="#" class="stat-link" onclick="setActiveTab('courses')">
                        View All
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Total Enrollments</div>
                    <div class="stat-value"><?= $enrollments_count ?></div>
                    <a href="#" class="stat-link" onclick="setActiveTab('enrollment')">
                        View All
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <!-- Tabs Navigation -->
            <div class="tabs">
                <div class="tab-header">
                    <button class="tab-btn active" onclick="setActiveTab('students')">
                        <i class="fas fa-user-graduate"></i>
                        Student Management
                    </button>
                    <button class="tab-btn" onclick="setActiveTab('lecturers')">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Lecturer Management
                    </button>
                    <button class="tab-btn" onclick="setActiveTab('courses')">
                        <i class="fas fa-book"></i>
                        Course Management
                    </button>
                    <button class="tab-btn" onclick="setActiveTab('enrollment')">
                        <i class="fas fa-clipboard-list"></i>
                        Enrollment Management
                    </button>
                </div>
                
                <div class="tab-content">
                    <!-- STUDENT MANAGEMENT TAB -->
                    <div id="students-tab" class="tab-section active">
                        <!-- Student Search Section -->
                        <div class="search-section">
                            <div class="flex justify-between items-center mb-3">
                                <h2><i class="fas fa-search"></i> Find Students</h2>
                                <div class="action-buttons">
                                    <a href="add_student.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Add New Student
                                    </a>
                                    <a href="update_gpa.php" class="btn btn-secondary">
                                        <i class="fas fa-calculator"></i>
                                        Update GPAs
                                    </a>
                                    <a href="grades_reference.php" class="btn btn-secondary">
                                        <i class="fas fa-graduation-cap"></i>
                                        Grading System
                                    </a>
                                </div>
                            </div>
                            
                            <form action="" method="GET" class="search-form">
                                <select name="type" class="search-input">
                                    <option value="all" <?= $search_type === 'all' ? 'selected' : '' ?>>All Fields</option>
                                    <option value="id" <?= $search_type === 'id' ? 'selected' : '' ?>>Student ID</option>
                                    <option value="name" <?= $search_type === 'name' ? 'selected' : '' ?>>Name</option>
                                    <option value="email" <?= $search_type === 'email' ? 'selected' : '' ?>>Email</option>
                                    <option value="program" <?= $search_type === 'program' ? 'selected' : '' ?>>Program</option>
                                </select>
                                <input type="text" name="keyword" class="search-input" placeholder="Search students..." value="<?= htmlspecialchars($search_keyword) ?>" required>
                                <button type="submit" name="search" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                    Search
                                </button>
                                <a href="admin_dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Clear
                                </a>
                            </form>
                        </div>
                        
                        <!-- Search Results Section -->
                        <?php if ($has_searched): ?>
                            <div class="data-table-container">
                                <div class="data-table-header">
                                    <h2>
                                        <i class="fas fa-list"></i>
                                        Search Results
                                        <?php if (!empty($search_results)): ?>
                                            <span class="badge badge-success"><?= count($search_results) ?> found</span>
                                        <?php endif; ?>
                                    </h2>
                                </div>
                                
                                <?php if (empty($search_results)): ?>
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-search"></i>
                                        </div>
                                        <p class="empty-state-text">No students found matching "<?= htmlspecialchars($search_keyword) ?>"</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Program</th>
                                                    <th>GPA</th>
                                                    <th>Standing</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($search_results as $student): 
                                                    $standing = getAcademicStanding($student['gpa']);
                                                ?>
                                                <tr>
                                                    <td><?= $student['student_id'] ?></td>
                                                    <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                                    <td><?= htmlspecialchars($student['student_email']) ?></td>
                                                    <td><?= htmlspecialchars($student['program']) ?></td>
                                                    <td><?= number_format($student['gpa'], 2) ?></td>
                                                    <td>
                                                        <span class="badge <?= $standing['class'] ?>">
                                                            <?= $standing['status'] ?>
                                                        </span>
                                                    </td>
                                                    <td class="table-actions">
                                                        <a href="edit_student.php?student_id=<?= $student['student_id'] ?>" class="btn-table-action btn-edit" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="view_student.php?student_id=<?= $student['student_id'] ?>" class="btn-table-action btn-view" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="delete_student.php?student_id=<?= $student['student_id'] ?>" onclick="return confirm('Are you sure you want to delete this student?')" class="btn-table-action btn-delete" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <!-- No search performed yet, show instructions -->
                            <div class="data-table-container">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <p class="empty-state-text">Search for students using the form above</p>
                                    <p>You can search by student ID, name, email, or program</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- LECTURER MANAGEMENT TAB -->
                    <div id="lecturers-tab" class="tab-section">
                        <div class="search-section">
                            <div class="flex justify-between items-center mb-3">
                                <h2><i class="fas fa-chalkboard-teacher"></i> Lecturers</h2>
                                <a href="add_lecturer.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Add New Lecturer
                                </a>
                            </div>
                        </div>
                        
                        <div class="data-table-container">
                            <div class="data-table-header">
                                <h2>Lecturer List</h2>
                            </div>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Department</th>
                                            <th>Position</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT lecturer_id, title, first_name, last_name, lecturer_email, department, position FROM lecturers ORDER BY last_name, first_name";
                                        $result = $conn->query($sql);

                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) { ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row["lecturer_id"]) ?></td>
                                                    <td><?= htmlspecialchars($row["title"]) ?></td>
                                                    <td><?= htmlspecialchars($row["first_name"] . " " . $row["last_name"]) ?></td>
                                                    <td><?= htmlspecialchars($row["lecturer_email"]) ?></td>
                                                    <td><?= htmlspecialchars($row["department"]) ?></td>
                                                    <td>
                                                        <span class="badge <?= $row["position"] === 'Staff Lecturer' ? 'badge-success' : 'badge-warning' ?>">
                                                            <?= !empty($row["position"]) ? htmlspecialchars($row["position"]) : "Adjunct Lecturer" ?>
                                                        </span>
                                                    </td>
                                                    <td class="table-actions">
                                                        <a href="edit_lecturer.php?lecturer_id=<?= $row['lecturer_id'] ?>" class="btn-table-action btn-edit" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="delete_lecturer.php?lecturer_id=<?= $row['lecturer_id'] ?>" onclick="return confirm('Are you sure you want to delete this lecturer?')" class="btn-table-action btn-delete" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php }
                                        } else {
                                            echo '<tr><td colspan="7" class="text-center">No lecturers found</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- COURSE MANAGEMENT TAB -->
                    <div id="courses-tab" class="tab-section">
                        <div class="search-section">
                            <div class="flex justify-between items-center mb-3">
                                <h2><i class="fas fa-book"></i> Courses</h2>
                                <a href="add_course.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Add New Course
                                </a>
                            </div>
                        </div>
                        
                        <div class="data-table-container">
                            <div class="data-table-header">
                                <h2>Course List</h2>
                            </div>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Title</th>
                                            <th>Credits</th>
                                            <th>Level</th>
                                            <th>Prerequisites</th>
                                            <th>Schedule</th>
                                            <th>Lecturer</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT c.*, s.semester, s.year, s.section, s.lecturers, s.day, s.time, s.location
                                        FROM courses c
                                        LEFT JOIN course_schedule s ON c.course_code = s.course_code
                                        ORDER BY c.course_code";

                                        $result = $conn->query($sql);
                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row["course_code"]) ?></td>
                                                    <td><?= htmlspecialchars($row["title"]) ?></td>
                                                    <td><?= htmlspecialchars($row["credits"]) ?></td>
                                                    <td>
                                                        <span class="badge <?= $row["degree_level"] === 'Graduate' ? 'badge-success' : 'badge-warning' ?>">
                                                            <?= htmlspecialchars($row["degree_level"]) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($row["prerequisites"] ?: 'None') ?></td>
                                                    <td>
                                                        <?php if (!empty($row["semester"]) && !empty($row["year"])): ?>
                                                            <?= htmlspecialchars($row["semester"] . ' ' . $row["year"]) ?><br>
                                                            <?= htmlspecialchars($row["day"] . ' - ' . $row["time"]) ?>
                                                        <?php else: ?>
                                                            Not scheduled
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($row["lecturers"] ?? 'Not assigned') ?></td>
                                                    <td class="table-actions">
                                                        <a href="edit_course.php?course_code=<?= urlencode($row['course_code']) ?>" class="btn-table-action btn-edit" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="delete_course.php?course_code=<?= urlencode($row['course_code']) ?>" onclick="return confirm('Are you sure you want to delete this course?')" class="btn-table-action btn-delete" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile;
                                        } else {
                                            echo '<tr><td colspan="8" class="text-center">No courses found</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ENROLLMENT MANAGEMENT TAB -->
                    <div id="enrollment-tab" class="tab-section">
                        <div class="search-section">
                            <div class="flex justify-between items-center mb-3">
                                <h2><i class="fas fa-clipboard-list"></i> Enrollments</h2>
                                <a href="enroll_student.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    New Enrollment
                                </a>
                            </div>
                        </div>
                        
                        <div class="data-table-container">
                            <div class="data-table-header">
                                <h2>Enrollment List</h2>
                            </div>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Course</th>
                                            <th>Credits</th>
                                            <th>Coursework</th>
                                            <th>Final Exam</th>
                                            <th>Total</th>
                                            <th>Grade</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        function getLetterGrade($grade) {
                                            if ($grade >= 90) return ['grade' => 'A', 'class' => 'badge-success'];
                                            elseif ($grade >= 80) return ['grade' => 'A-', 'class' => 'badge-success'];
                                            elseif ($grade >= 75) return ['grade' => 'B+', 'class' => 'badge-success'];
                                            elseif ($grade >= 65) return ['grade' => 'B', 'class' => 'badge-success'];
                                            elseif ($grade >= 60) return ['grade' => 'B-', 'class' => 'badge-warning'];
                                            elseif ($grade >= 55) return ['grade' => 'C+', 'class' => 'badge-warning'];
                                            elseif ($grade >= 50) return ['grade' => 'C', 'class' => 'badge-warning'];
                                            elseif ($grade >= 40) return ['grade' => 'D', 'class' => 'badge-warning'];
                                            else return ['grade' => 'F', 'class' => 'badge-danger'];
                                        }

                                        $sql = "SELECT 
                                                    e.enrollment_id, 
                                                    e.student_id, 
                                                    e.course_code,
                                                    s.first_name, 
                                                    s.last_name, 
                                                    c.title, 
                                                    c.credits,
                                                    e.coursework_grade, 
                                                    e.final_exam_grade
                                                FROM course_enrollment e
                                                JOIN students s ON e.student_id = s.student_id
                                                JOIN courses c ON e.course_code = c.course_code
                                                ORDER BY e.enrollment_id DESC
                                                LIMIT 15";

                                        $result = $conn->query($sql);
                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()):
                                                $coursework = $row["coursework_grade"];
                                                $final = $row["final_exam_grade"];
                                                $hasGrades = $coursework !== null && $final !== null;
                                                
                                                if ($hasGrades) {
                                                    $total = round(($coursework * 0.6) + ($final * 0.4), 2);
                                                    $letterGradeInfo = getLetterGrade($total);
                                                }
                                            ?>
                                                <tr>
                                                    <td>
                                                        <?= htmlspecialchars($row["first_name"] . " " . $row["last_name"]) ?>
                                                        <div class="text-secondary"><?= $row["student_id"] ?></div>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($row["title"]) ?>
                                                        <div class="text-secondary"><?= htmlspecialchars($row["course_code"]) ?></div>
                                                    </td>
                                                    <td><?= $row["credits"] ?></td>
                                                    <td><?= $hasGrades ? $coursework : 'N/A' ?></td>
                                                    <td><?= $hasGrades ? $final : 'N/A' ?></td>
                                                    <td><?= $hasGrades ? $total : 'N/A' ?></td>
                                                    <td>
                                                        <?php if ($hasGrades): ?>
                                                            <span class="badge <?= $letterGradeInfo['class'] ?>">
                                                                <?= $letterGradeInfo['grade'] ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge">Not graded</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="table-actions">
                                                        <a href="edit_enrollment.php?enrollment_id=<?= $row['enrollment_id'] ?>" class="btn-table-action btn-edit" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="delete_enrollment.php?enrollment_id=<?= $row['enrollment_id'] ?>" onclick="return confirm('Are you sure you want to delete this enrollment?')" class="btn-table-action btn-delete" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile;
                                        } else {
                                            echo '<tr><td colspan="8" class="text-center">No enrollments found</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if ($enrollments_count > 15): ?>
                                <div class="flex justify-between items-center p-4">
                                    <p><?= min(15, $enrollments_count) ?> of <?= $enrollments_count ?> enrollments</p>
                                    <a href="view_all_enrollments.php" class="btn btn-secondary btn-sm">
                                        View All Enrollments
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
                <a href="update_gpa.php" class="footer-link">Update GPA</a>
                <a href="login.php" class="footer-link">Change User</a>
            </div>
        </div>
    </footer>

    <script>
        // Tab functionality
        function setActiveTab(tabName) {
            // Hide all tab content
            const tabSections = document.querySelectorAll('.tab-section');
            tabSections.forEach(section => {
                section.classList.remove('active');
            });
            
            // Show the selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Update active tab button
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(button => {
                button.classList.remove('active');
            });
            
            // Find the button that was clicked and make it active
            const clickedButton = document.querySelector(`.tab-btn[onclick="setActiveTab('${tabName}')"]`);
            if (clickedButton) {
                clickedButton.classList.add('active');
            }
            
            // Save active tab to localStorage
            localStorage.setItem('activeTab', tabName);
        }
        
        // When the page loads, show the previously active tab or default to students
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = localStorage.getItem('activeTab') || 'students';
            setActiveTab(activeTab);
        });
    </script>
</body>
</html>