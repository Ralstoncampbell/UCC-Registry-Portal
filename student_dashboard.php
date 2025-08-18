<!--Name of Enterprise App: ucc_registrar
Developers: Geordi Duncan
Version:5.0 
Version Date:06/4/2025
Purpose: A php page that allows student to look at their course grades with gpa, scores etc. -->
<?php
session_start();
include "db.php";

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

// Get student information
$student_id = $_SESSION['student_id'];
$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// Calculate GPA function
function getLetterGrade($grade) {
    if ($grade >= 90) return ['grade' => 'A', 'points' => 4.00, 'class' => 'badge-success'];
    elseif ($grade >= 80) return ['grade' => 'A-', 'points' => 3.67, 'class' => 'badge-success'];
    elseif ($grade >= 75) return ['grade' => 'B+', 'points' => 3.50, 'class' => 'badge-success'];
    elseif ($grade >= 65) return ['grade' => 'B', 'points' => 3.00, 'class' => 'badge-success'];
    elseif ($grade >= 60) return ['grade' => 'B-', 'points' => 2.67, 'class' => 'badge-warning'];
    elseif ($grade >= 55) return ['grade' => 'C+', 'points' => 2.33, 'class' => 'badge-warning'];
    elseif ($grade >= 50) return ['grade' => 'C', 'points' => 2.00, 'class' => 'badge-warning'];
    elseif ($grade >= 40) return ['grade' => 'D', 'points' => 1.67, 'class' => 'badge-warning'];
    else return ['grade' => 'F', 'points' => 0.00, 'class' => 'badge-danger'];
}

// Calculate current GPA
function calculateGPA($conn, $student_id) {
    $sql = "SELECT c.credits, e.coursework_grade, e.final_exam_grade
            FROM course_enrollment e
            JOIN courses c ON e.course_code = c.course_code
            WHERE e.student_id = ? 
            AND e.coursework_grade IS NOT NULL 
            AND e.final_exam_grade IS NOT NULL";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $totalCredits = 0;
    $totalPoints = 0;
    
    while ($row = $result->fetch_assoc()) {
        $credits = $row['credits'];
        $totalScore = ($row['coursework_grade'] * 0.6) + ($row['final_exam_grade'] * 0.4);
        $gradeData = getLetterGrade($totalScore);
        
        $totalCredits += $credits;
        $totalPoints += ($gradeData['points'] * $credits);
    }
    
    $stmt->close();
    
    return ($totalCredits > 0) ? round($totalPoints / $totalCredits, 2) : 0;
}

// Get student's current GPA
$currentGPA = calculateGPA($conn, $student_id);

// Update GPA in database
$updateGPA = "UPDATE students SET gpa = ? WHERE student_id = ?";
$stmtUpdate = $conn->prepare($updateGPA);
$stmtUpdate->bind_param("di", $currentGPA, $student_id);
$stmtUpdate->execute();
$stmtUpdate->close();

// Determine academic standing
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

$academicStanding = getAcademicStanding($currentGPA);

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
    <title>Student Dashboard | UCC Registry</title>
    <link rel="stylesheet" href="modern-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gpa-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            text-align: center;
        }
        
        .gpa-value {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .academic-standing {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        
        .student-info-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
        }
        
        .student-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 600;
            margin-right: 1rem;
        }
        
        .student-details {
            flex: 1;
        }
        
        .student-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .student-id {
            color: var(--gray-500);
            font-size: 0.875rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .info-item {
            margin-bottom: 1rem;
        }
        
        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-500);
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-weight: 500;
        }
        
        .semester-header {
            background-color: var(--gray-100);
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .semester-header i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <a href="student_dashboard.php" class="logo">
                <img src="ucc_logo.png" alt="UCC Logo" style="height: 40px; margin-right: 10px;">
                <span style="font-size: 20px; font-weight: 600; color: #2563eb; display: flex; align-items: center;">UCC Registry</span>
            </a>
            <div class="header-actions">
                <div class="user-menu">
                    <div class="user-avatar">
                        <?= getInitial($student['first_name']) ?>
                    </div>
                    <span><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
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
                        <i class="fas fa-tachometer-alt"></i>
                        Student Dashboard
                    </h2>
                    <div class="action-buttons">
                        <a href="generate_transcript.php" class="btn btn-primary">
                            <i class="fas fa-file-alt"></i>
                            Generate Transcript
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="student-info-card">
                    <div class="student-header">
                        <div class="student-avatar">
                            <?= getInitial($student['first_name']) ?>
                        </div>
                        <div class="student-details">
                            <div class="student-name"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></div>
                            <div class="student-id">Student ID: <?= $student['student_id'] ?></div>
                        </div>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Program</div>
                            <div class="info-value"><?= htmlspecialchars($student['program']) ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?= htmlspecialchars($student['student_email']) ?></div>
                        </div>
                        
                        <?php if (!empty($student['mobile'])): ?>
                        <div class="info-item">
                            <div class="info-label">Mobile</div>
                            <div class="info-value"><?= htmlspecialchars($student['mobile']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($student['personal_email'])): ?>
                        <div class="info-item">
                            <div class="info-label">Personal Email</div>
                            <div class="info-value"><?= htmlspecialchars($student['personal_email']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="gpa-card">
                    <h3 style="margin-bottom: 1rem; color: var(--gray-700);">Academic Standing</h3>
                    <div class="gpa-value"><?= number_format($currentGPA, 2) ?></div>
                    <span class="badge <?= $academicStanding['class'] ?> academic-standing">
                        <?= $academicStanding['status'] ?>
                    </span>
                    <p style="color: var(--gray-600); margin-top: 0.5rem;">Grade Point Average (GPA)</p>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="tabs" style="margin-top: 2rem;">
                <div class="tab-header">
                    <button class="tab-btn active" onclick="setActiveTab('courses')">
                        <i class="fas fa-book"></i>
                        My Courses
                    </button>
                    <button class="tab-btn" onclick="setActiveTab('grades')">
                        <i class="fas fa-chart-bar"></i>
                        Grades & Performance
                    </button>
                </div>
                
                <div class="tab-content">
                    <!-- COURSES TAB -->
                    <div id="courses-tab" class="tab-section active">
                        <?php
                        // Get student's enrolled courses with semester information
                        $courses_sql = "SELECT 
                                    e.course_code, 
                                    e.semester AS enrollment_semester, 
                                    e.year AS enrollment_year, 
                                    e.section AS enrollment_section,
                                    c.title, 
                                    c.credits,
                                    s.day, 
                                    s.time, 
                                    s.lecturers, 
                                    s.location
                                FROM course_enrollment e
                                JOIN courses c ON e.course_code = c.course_code
                                LEFT JOIN course_schedule s ON e.course_code = s.course_code 
                                    AND e.semester = s.semester 
                                    AND e.year = s.year 
                                    AND e.section = s.section
                                WHERE e.student_id = ?
                                ORDER BY e.year DESC, 
                                        CASE 
                                            WHEN e.semester = 'Spring' THEN 1 
                                            WHEN e.semester = 'Summer' THEN 2 
                                            WHEN e.semester = 'Fall' THEN 3 
                                            ELSE 4 
                                        END DESC";
                        
                        $courses_stmt = $conn->prepare($courses_sql);
                        $courses_stmt->bind_param("i", $student_id);
                        $courses_stmt->execute();
                        $courses_result = $courses_stmt->get_result();
                        $courses = [];
                        
                        while ($course = $courses_result->fetch_assoc()) {
                            $courses[] = $course;
                        }
                        $courses_stmt->close();
                        
                        if (empty($courses)): 
                        ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <p class="empty-state-text">You are not enrolled in any courses yet.</p>
                            </div>
                        <?php 
                        else: 
                            // Group courses by semester and year
                            $grouped_courses = [];
                            foreach ($courses as $course) {
                                $semester_key = !empty($course['enrollment_semester']) && !empty($course['enrollment_year']) 
                                    ? $course['enrollment_semester'] . ' ' . $course['enrollment_year'] 
                                    : 'Unscheduled';
                                
                                if (!isset($grouped_courses[$semester_key])) {
                                    $grouped_courses[$semester_key] = [];
                                }
                                
                                $grouped_courses[$semester_key][] = $course;
                            }
                            
                            // Display courses grouped by semester
                            foreach ($grouped_courses as $semester => $semester_courses): 
                        ?>
                            <div class="semester-header">
                                <i class="fas fa-calendar-alt"></i>
                                <?= htmlspecialchars($semester) ?>
                            </div>
                            
                            <div class="table-responsive mb-4">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Title</th>
                                            <th>Section</th>
                                            <th>Credits</th>
                                            <th>Schedule</th>
                                            <th>Lecturer(s)</th>
                                            <th>Location</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($semester_courses as $course): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($course['course_code']) ?></td>
                                                <td><?= htmlspecialchars($course['title']) ?></td>
                                                <td><?= htmlspecialchars($course['enrollment_section']) ?></td>
                                                <td><?= $course['credits'] ?></td>
                                                <td>
                                                    <?php if (!empty($course['day']) && !empty($course['time'])): ?>
                                                        <?= htmlspecialchars($course['day'] . ' at ' . $course['time']) ?>
                                                    <?php else: ?>
                                                        <span class="text-secondary">Not scheduled</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($course['lecturers'])): ?>
                                                        <?= htmlspecialchars($course['lecturers']) ?>
                                                    <?php else: ?>
                                                        <span class="text-secondary">Not assigned</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($course['location'])): ?>
                                                        <?= htmlspecialchars($course['location']) ?>
                                                    <?php else: ?>
                                                        <span class="text-secondary">TBA</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php 
                            endforeach;
                        endif; 
                        ?>
                    </div>
                    
                    <!-- GRADES TAB -->
                    <div id="grades-tab" class="tab-section">
                        <?php
                        // Get student's graded courses
                        $grades_sql = "SELECT 
                                    e.course_code, 
                                    e.semester AS enrollment_semester, 
                                    e.year AS enrollment_year, 
                                    e.section AS enrollment_section,
                                    e.coursework_grade, 
                                    e.final_exam_grade,
                                    c.title, 
                                    c.credits
                                FROM course_enrollment e
                                JOIN courses c ON e.course_code = c.course_code
                                WHERE e.student_id = ? 
                                AND e.coursework_grade IS NOT NULL 
                                AND e.final_exam_grade IS NOT NULL
                                ORDER BY e.year DESC, 
                                        CASE 
                                            WHEN e.semester = 'Spring' THEN 1 
                                            WHEN e.semester = 'Summer' THEN 2 
                                            WHEN e.semester = 'Fall' THEN 3 
                                            ELSE 4 
                                        END DESC";
                        
                        $grades_stmt = $conn->prepare($grades_sql);
                        $grades_stmt->bind_param("i", $student_id);
                        $grades_stmt->execute();
                        $grades_result = $grades_stmt->get_result();
                        $grades = [];
                        
                        while ($grade = $grades_result->fetch_assoc()) {
                            $grades[] = $grade;
                        }
                        $grades_stmt->close();
                        
                        if (empty($grades)): 
                        ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <p class="empty-state-text">No grades available yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive mb-4">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Term</th>
                                            <th>Credits</th>
                                            <th>Coursework (60%)</th>
                                            <th>Final Exam (40%)</th>
                                            <th>Total</th>
                                            <th>Letter Grade</th>
                                            <th>Quality Points</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total_credits = 0;
                                        $total_quality_points = 0;
                                        
                                        foreach ($grades as $grade):
                                            $total = round(($grade['coursework_grade'] * 0.6) + ($grade['final_exam_grade'] * 0.4), 2);
                                            $letter_grade = getLetterGrade($total);
                                            
                                            // Calculate quality points
                                            $quality_points = $letter_grade['points'];
                                            $course_quality_points = $quality_points * $grade['credits'];
                                            
                                            // Add to totals
                                            $total_credits += $grade['credits'];
                                            $total_quality_points += $course_quality_points;
                                        ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($grade['title']) ?>
                                                    <div class="text-secondary"><?= htmlspecialchars($grade['course_code']) ?></div>
                                                </td>
                                                <td><?= htmlspecialchars($grade['enrollment_semester'] . ' ' . $grade['enrollment_year']) ?></td>
                                                <td><?= $grade['credits'] ?></td>
                                                <td><?= $grade['coursework_grade'] ?></td>
                                                <td><?= $grade['final_exam_grade'] ?></td>
                                                <td><?= $total ?></td>
                                                <td>
                                                    <span class="badge <?= $letter_grade['class'] ?>">
                                                        <?= $letter_grade['grade'] ?>
                                                    </span>
                                                </td>
                                                <td><?= number_format($course_quality_points, 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="7" style="text-align: right;">Total Credits:</th>
                                            <th><?= $total_credits ?></th>
                                        </tr>
                                        <tr>
                                            <th colspan="7" style="text-align: right;">Total Quality Points:</th>
                                            <th><?= number_format($total_quality_points, 2) ?></th>
                                        </tr>
                                        <tr>
                                            <th colspan="7" style="text-align: right;">Calculated GPA:</th>
                                            <th><?= number_format($total_quality_points / $total_credits, 2) ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="search-section" style="margin: 1.5rem 0; background-color: rgba(23, 162, 184, 0.1); border-left: 3px solid var(--primary);">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-info-circle text-primary"></i>
                                    <p><strong>Grading Policy:</strong> Coursework is worth 60% of the final grade, and the final exam is worth 40%.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-content">
            <p>University of the Commonwealth Caribbean - Registry Department</p>
            <p>&copy; <?= date('Y') ?> UCC Registry System | Version 5.0</p>
            <div class="footer-links">
                <a href="grades_reference.php" class="footer-link">Grading System</a>
                <a href="student_dashboard.php" class="footer-link">Refresh Dashboard</a>
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
        }
    </script>
</body>
</html>