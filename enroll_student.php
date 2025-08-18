<!--Name of Enterprise App: ucc_registrar
Developers: Geordi Duncan
Version:3.0 
Version Date:06/4/2025
Purpose: A php function that allows adminto register students for classes -->
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
    $course_offering = $_POST["course_offering"] ?? "";
    $coursework_grade = $_POST["coursework_grade"] ?? null;
    $final_exam_grade = $_POST["final_exam_grade"] ?? null;

    // Split the course offering value to get course_code, semester, year, and section
    if (!empty($course_offering)) {
        $offering_parts = explode("_", $course_offering);
        $course_code = $offering_parts[0];
        $semester = isset($offering_parts[1]) ? $offering_parts[1] : "";
        $year = isset($offering_parts[2]) ? $offering_parts[2] : "";
        $section = isset($offering_parts[3]) ? $offering_parts[3] : "";
    } else {
        $course_code = "";
        $semester = "";
        $year = "";
        $section = "";
    }

    // Convert empty strings to NULL for grades
    if ($coursework_grade === "") $coursework_grade = null;
    if ($final_exam_grade === "") $final_exam_grade = null;

    // Validate input data
    if (empty($student_id) || empty($course_code) || empty($semester) || empty($year) || empty($section)) {
        $messages[] = ["type" => "error", "text" => "Student ID and Course Offering are required!"];
    } else {
        // Verify the student exists
        $check_student = $conn->prepare("SELECT first_name, last_name FROM students WHERE student_id = ?");
        $check_student->bind_param("i", $student_id);
        $check_student->execute();
        $student_result = $check_student->get_result();
        
        if ($student_result->num_rows === 0) {
            $messages[] = ["type" => "error", "text" => "Student not found!"];
        } else {
            $student_row = $student_result->fetch_assoc();
            $student_name = $student_row['first_name'] . " " . $student_row['last_name'];
            
            // Verify the course exists with this schedule
            $check_course = $conn->prepare("SELECT cs.course_code 
                                          FROM course_schedule cs 
                                          JOIN courses c ON cs.course_code = c.course_code 
                                          WHERE cs.course_code = ? 
                                          AND cs.semester = ? 
                                          AND cs.year = ? 
                                          AND cs.section = ?");
            $check_course->bind_param("ssis", $course_code, $semester, $year, $section);
            $check_course->execute();
            $course_result = $check_course->get_result();
            
            if ($course_result->num_rows === 0) {
                $messages[] = ["type" => "error", "text" => "Course offering not found!"];
            } else {
                // Check if the student is already enrolled in this course section
                $check_enrollment = $conn->prepare("SELECT enrollment_id 
                                                 FROM course_enrollment 
                                                 WHERE student_id = ? 
                                                 AND course_code = ? 
                                                 AND semester = ? 
                                                 AND year = ? 
                                                 AND section = ?");
                $check_enrollment->bind_param("issis", $student_id, $course_code, $semester, $year, $section);
                $check_enrollment->execute();
                $enrollment_result = $check_enrollment->get_result();
                
                if ($enrollment_result->num_rows > 0) {
                    $messages[] = ["type" => "error", "text" => "Student is already enrolled in this course section!"];
                } else {
                    // If grades are provided, validate them
                    if ($coursework_grade !== null && $final_exam_grade !== null) {
                        // Validate grades are within range
                        if ($coursework_grade < 0 || $coursework_grade > 100 || $final_exam_grade < 0 || $final_exam_grade > 100) {
                            $messages[] = ["type" => "error", "text" => "Grades must be between 0 and 100!"];
                        } else {
                            // All validation passed, insert the enrollment with schedule information
                            $insert_sql = "INSERT INTO course_enrollment 
                                           (student_id, course_code, semester, year, section, coursework_grade, final_exam_grade) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?)";
                            $insert_stmt = $conn->prepare($insert_sql);
                            $insert_stmt->bind_param("ississd", $student_id, $course_code, $semester, $year, $section, $coursework_grade, $final_exam_grade);
                            
                            if ($insert_stmt->execute()) {
                                $messages[] = ["type" => "success", "text" => "Student successfully enrolled in the course!"];
                                
                                // Update the student's GPA after enrollment
                                updateStudentGPA($conn, $student_id);
                            } else {
                                $messages[] = ["type" => "error", "text" => "Error enrolling student: " . $conn->error];
                            }
                            $insert_stmt->close();
                        }
                    } else {
                        // Enrollment without grades but with schedule information
                        $insert_sql = "INSERT INTO course_enrollment 
                                       (student_id, course_code, semester, year, section) 
                                       VALUES (?, ?, ?, ?, ?)";
                        $insert_stmt = $conn->prepare($insert_sql);
                        $insert_stmt->bind_param("issis", $student_id, $course_code, $semester, $year, $section);
                        
                        if ($insert_stmt->execute()) {
                            $messages[] = ["type" => "success", "text" => "Student successfully enrolled in the course without grades!"];
                        } else {
                            $messages[] = ["type" => "error", "text" => "Error enrolling student: " . $conn->error];
                        }
                        $insert_stmt->close();
                    }
                }
                $check_enrollment->close();
            }
            $check_course->close();
        }
        $check_student->close();
    }
}

// Function to update a student's GPA
function updateStudentGPA($conn, $student_id) {
    // Get all course enrollments for this student
    $sql = "SELECT c.credits, e.coursework_grade, e.final_exam_grade
            FROM course_enrollment e
            JOIN courses c ON e.course_code = c.course_code
            WHERE e.student_id = ? AND e.coursework_grade IS NOT NULL AND e.final_exam_grade IS NOT NULL";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $totalCredits = 0;
    $totalPoints = 0;
    
    while ($row = $result->fetch_assoc()) {
        $credits = $row['credits'];
        $totalGrade = ($row['coursework_grade'] * 0.6) + ($row['final_exam_grade'] * 0.4);
        
        // Determine quality points based on grade
        $qualityPoints = 0;
        
        if ($totalGrade >= 90) $qualityPoints = 4.00;
        elseif ($totalGrade >= 80) $qualityPoints = 3.67;
        elseif ($totalGrade >= 75) $qualityPoints = 3.50;
        elseif ($totalGrade >= 65) $qualityPoints = 3.00;
        elseif ($totalGrade >= 60) $qualityPoints = 2.67;
        elseif ($totalGrade >= 55) $qualityPoints = 2.33;
        elseif ($totalGrade >= 50) $qualityPoints = 2.00;
        elseif ($totalGrade >= 40) $qualityPoints = 1.67;
        
        $totalCredits += $credits;
        $totalPoints += ($qualityPoints * $credits);
    }
    
    $gpa = ($totalCredits > 0) ? round($totalPoints / $totalCredits, 2) : 0;
    
    // Update the student's GPA
    $update_sql = "UPDATE students SET gpa = ? WHERE student_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("di", $gpa, $student_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    $stmt->close();
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
    <title>Enroll Student | UCC Registry</title>
    <link rel="stylesheet" href="modern-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional styles for the searchable dropdown */
        .search-dropdown {
            position: relative;
        }
        
        .search-results {
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            z-index: 10;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: none;
        }
        
        .search-option {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .search-option:hover {
            background-color: #f5f5f5;
        }
        
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
                        <i class="fas fa-user-plus"></i>
                        Enroll Student in Course
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
                    <h2>Enrollment Form</h2>
                </div>
                <div class="tab-content" style="padding: 1.5rem;">
                    <form method="post" class="mb-4">
                        <div class="grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                            <div>
                                <label for="student_search" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Select Student:</label>
                                <div class="search-dropdown">
                                    <input type="text" id="student_search" class="search-input" placeholder="Search students by ID or name..." style="width: 100%;">
                                    <input type="hidden" name="student_id" id="student_id" required>
                                    <div id="student_results" class="search-results"></div>
                                </div>
                            </div>
                            
                            <div>
                                <label for="course_offering" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Select Course Offering:</label>
                                <select name="course_offering" id="course_offering" class="search-input" required style="width: 100%;">
                                    <option value="">-- Select Course Offering --</option>
                                    <?php
                                    // Get course offerings with schedule details
                                    $offerings = $conn->query("SELECT c.course_code, c.title, c.credits, 
                                                           s.semester, s.year, s.section, s.day, s.time, s.location
                                                      FROM courses c 
                                                      JOIN course_schedule s ON c.course_code = s.course_code 
                                                      ORDER BY s.year DESC, 
                                                          CASE 
                                                              WHEN s.semester = 'Spring' THEN 1 
                                                              WHEN s.semester = 'Summer' THEN 2 
                                                              WHEN s.semester = 'Fall' THEN 3 
                                                          END DESC, 
                                                          c.course_code, 
                                                          s.section");
                                    
                                    while ($row = $offerings->fetch_assoc()) {
                                        // Create a unique identifier for this offering
                                        $offering_id = $row['course_code'] . '_' . $row['semester'] . '_' . $row['year'] . '_' . $row['section'];
                                        
                                        // Create a display name
                                        $display_name = $row['course_code'] . " - " . $row['title'] . 
                                                      " (" . $row['semester'] . " " . $row['year'] . 
                                                      ", Section " . $row['section'] . ", " . 
                                                      $row['day'] . " " . $row['time'] . ", " . 
                                                      $row['location'] . ")";
                                        
                                        echo "<option value='" . $offering_id . "'>" . $display_name . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="search-section" style="margin: 1.5rem 0; background-color: rgba(23, 162, 184, 0.1); border-left: 3px solid var(--primary);">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-info-circle text-primary"></i>
                                <p><strong>Note:</strong> Grades are optional during enrollment. You can add or update grades later.</p>
                            </div>
                        </div>
                        
                        <div class="grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                            <div>
                                <label for="coursework_grade" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Coursework Grade (0-100):</label>
                                <input type="number" step="0.01" min="0" max="100" name="coursework_grade" id="coursework_grade" placeholder="Optional" class="search-input" style="width: 100%;">
                            </div>
                            
                            <div>
                                <label for="final_exam_grade" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Final Exam Grade (0-100):</label>
                                <input type="number" step="0.01" min="0" max="100" name="final_exam_grade" id="final_exam_grade" placeholder="Optional" class="search-input" style="width: 100%;">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i>
                            Enroll Student
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="data-table-container" style="margin-top: 2rem;">
                <div class="data-table-header">
                    <h2>
                        <i class="fas fa-history"></i>
                        Recently Enrolled Students
                    </h2>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Section</th>
                                <th>Term</th>
                                <th>Coursework</th>
                                <th>Final Exam</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Get recent enrollments with schedule information
                            $recent_sql = "SELECT 
                                e.enrollment_id, 
                                e.student_id, 
                                CONCAT(s.first_name, ' ', s.last_name) AS student_name, 
                                e.course_code, 
                                c.title, 
                                e.section,
                                e.semester,
                                e.year,
                                e.coursework_grade, 
                                e.final_exam_grade 
                            FROM course_enrollment e
                            JOIN students s ON e.student_id = s.student_id
                            JOIN courses c ON e.course_code = c.course_code
                            ORDER BY e.enrollment_id DESC
                            LIMIT 5";
                            
                            $recent_result = $conn->query($recent_sql);
                            
                            if ($recent_result && $recent_result->num_rows > 0) {
                                while ($row = $recent_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$row['student_id']}</td>";
                                    echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                                    echo "<td><div>" . htmlspecialchars($row['title']) . "</div><div class='text-secondary'>" . htmlspecialchars($row['course_code']) . "</div></td>";
                                    echo "<td>" . htmlspecialchars($row['section']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['semester'] . ' ' . $row['year']) . "</td>";
                                    
                                    // Display grades if available
                                    if ($row['coursework_grade'] !== null && $row['final_exam_grade'] !== null) {
                                        echo "<td>{$row['coursework_grade']}</td>";
                                        echo "<td>{$row['final_exam_grade']}</td>";
                                        
                                        $total = ($row['coursework_grade'] * 0.6) + ($row['final_exam_grade'] * 0.4);
                                        echo "<td>" . number_format($total, 2) . "</td>";
                                        
                                        // Determine status based on grade
                                        if ($total >= 50) {
                                            echo "<td><span class='badge badge-success'>Pass</span></td>";
                                        } else {
                                            echo "<td><span class='badge badge-danger'>Fail</span></td>";
                                        }
                                    } else {
                                        echo "<td colspan='3' class='text-center'>Grades not yet assigned</td>";
                                        echo "<td><span class='badge'>Enrolled</span></td>";
                                    }
                                    
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>No recent enrollments</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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
                <a href="update_gpa.php" class="footer-link">Update GPA</a>
                <a href="admin_dashboard.php" class="footer-link">Back to Dashboard</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const studentSearch = document.getElementById('student_search');
            const studentResults = document.getElementById('student_results');
            const studentIdField = document.getElementById('student_id');
            
            // Student data
            const students = [
                <?php
                $students = $conn->query("SELECT student_id, first_name, last_name FROM students ORDER BY last_name, first_name");
                while ($row = $students->fetch_assoc()) {
                    echo "{ id: '" . $row['student_id'] . "', name: '" . 
                        addslashes($row['first_name'] . " " . $row['last_name']) . "' },";
                }
                ?>
            ];
            
            // Search functionality
            studentSearch.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                
                if (query.length < 2) {
                    studentResults.style.display = 'none';
                    return;
                }
                
                const filteredStudents = students.filter(student => 
                    student.name.toLowerCase().includes(query) || 
                    student.id.toString().includes(query)
                );
                
                // Display results
                studentResults.innerHTML = '';
                
                if (filteredStudents.length === 0) {
                    const noResults = document.createElement('div');
                    noResults.className = 'search-option';
                    noResults.textContent = 'No students found';
                    studentResults.appendChild(noResults);
                } else {
                    filteredStudents.forEach(student => {
                        const option = document.createElement('div');
                        option.className = 'search-option';
                        option.innerHTML = `<strong>${student.id}</strong> - ${student.name}`;
                        
                        option.addEventListener('click', function() {
                            studentIdField.value = student.id;
                            studentSearch.value = `${student.id} - ${student.name}`;
                            studentResults.style.display = 'none';
                        });
                        
                        studentResults.appendChild(option);
                    });
                }
                
                studentResults.style.display = 'block';
            });
            
            // Close results when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target !== studentSearch && e.target !== studentResults && !studentResults.contains(e.target)) {
                    studentResults.style.display = 'none';
                }
            });
            
            // Allow keyboard navigation of dropdown
            studentSearch.addEventListener('keydown', function(e) {
                if (studentResults.style.display === 'block') {
                    const options = studentResults.querySelectorAll('.search-option');
                    const activeOption = studentResults.querySelector('.search-option:hover');
                    let index = -1;
                    
                    if (activeOption) {
                        for (let i = 0; i < options.length; i++) {
                            if (options[i] === activeOption) {
                                index = i;
                                break;
                            }
                        }
                    }
                    
                    // Arrow down
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (index < options.length - 1) {
                            if (activeOption) activeOption.classList.remove('hover');
                            options[index + 1].classList.add('hover');
                            options[index + 1].scrollIntoView({ block: 'nearest' });
                        }
                    }
                    
                    // Arrow up
                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (index > 0) {
                            if (activeOption) activeOption.classList.remove('hover');
                            options[index - 1].classList.add('hover');
                            options[index - 1].scrollIntoView({ block: 'nearest' });
                        }
                    }
                    
                    // Enter key
                    if (e.key === 'Enter' && activeOption) {
                        e.preventDefault();
                        activeOption.click();
                    }
                    
                    // Escape key
                    if (e.key === 'Escape') {
                        studentResults.style.display = 'none';
                    }
                }
            });
        });
    </script>
</body>
</html>