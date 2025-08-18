<?php
session_start();
include "db.php";

// Check if user is logged in as admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Check if student ID is provided
if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$student_id = $_GET['student_id'];

// Fetch student data
$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Student not found
    header("Location: admin_dashboard.php");
    exit;
}

$student = $result->fetch_assoc();
$stmt->close();

// Fetch student's enrolled courses using enrollment's semester and year
$courses_sql = "SELECT 
                e.enrollment_id, 
                e.student_id, 
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

// Function to calculate letter grade
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

$standing = getAcademicStanding($student['gpa']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student | UCC Registry</title>
    <link rel="stylesheet" href="modern-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .student-profile {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 2rem;
        }
        
        @media (max-width: 768px) {
            .student-profile {
                grid-template-columns: 1fr;
            }
        }
        
        .profile-sidebar {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
        }
        
        .student-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 600;
            margin: 0 auto 1.5rem;
        }
        
        .profile-name {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .profile-id {
            text-align: center;
            color: var(--gray-500);
            margin-bottom: 1.5rem;
        }
        
        .profile-badge {
            display: block;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .profile-details {
            margin-top: 1.5rem;
        }
        
        .detail-item {
            display: flex;
            margin-bottom: 1rem;
        }
        
        .detail-icon {
            width: 2rem;
            color: var(--primary);
        }
        
        .detail-content {
            flex: 1;
        }
        
        .detail-label {
            font-size: 0.875rem;
            color: var(--gray-500);
        }
        
        .detail-value {
            font-weight: 500;
        }
        
        .profile-content {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .tab-pill-container {
            display: flex;
            background-color: var(--gray-100);
            border-radius: 9999px;
            padding: 0.5rem;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .tab-pill {
            flex: 1;
            text-align: center;
            padding: 0.75rem 1rem;
            border-radius: 9999px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .tab-pill.active {
            background-color: white;
            box-shadow: var(--box-shadow);
            color: var(--primary);
        }
        
        .pill-content {
            display: none;
        }
        
        .pill-content.active {
            display: block;
        }
        
        .no-courses {
            text-align: center;
            padding: 3rem;
            color: var(--gray-500);
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
        
        .section-badge {
            background-color: var(--primary);
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
    <div class="container header-content">
            <a href="admin_dashboard.php" class="logo">
            <img src="ucc_logo.png" alt="UCC Logo" style="height: 100px; margin-right: 10px;">
            <span style="font-size: 20px; font-weight: 600; color: #2563eb; display: flex; align-items: center;">UCC Registry</span>
            </a>
            <div class="header-actions">
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
                        <i class="fas fa-user-graduate"></i>
                        Student Profile
                    </h2>
                    <div class="action-buttons">
                        <a href="edit_student.php?student_id=<?= $student['student_id'] ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i>
                            Edit Student
                        </a>
                        <a href="generate_transcript.php?student_id=<?= $student['student_id'] ?>" class="btn btn-secondary">
                            <i class="fas fa-file-alt"></i>
                            Generate Transcript
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="student-profile">
                <div class="profile-sidebar">
                    <div class="student-avatar">
                        <?= strtoupper(substr($student['first_name'], 0, 1)) ?>
                    </div>
                    <h3 class="profile-name"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h3>
                    <div class="profile-id">ID: <?= $student['student_id'] ?></div>
                    
                    <span class="badge <?= $standing['class'] ?> profile-badge">
                        <?= $standing['status'] ?>
                    </span>
                    
                    <div class="profile-details">
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Student Email</div>
                                <div class="detail-value"><?= htmlspecialchars($student['student_email']) ?></div>
                            </div>
                        </div>
                        
                        <?php if (!empty($student['personal_email'])): ?>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-at"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Personal Email</div>
                                <div class="detail-value"><?= htmlspecialchars($student['personal_email']) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($student['mobile'])): ?>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Mobile</div>
                                <div class="detail-value"><?= htmlspecialchars($student['mobile']) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Program</div>
                                <div class="detail-value"><?= htmlspecialchars($student['program']) ?></div>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">GPA</div>
                                <div class="detail-value"><?= number_format($student['gpa'], 2) ?></div>
                            </div>
                        </div>
                        
                        <?php if (!empty($student['next_of_kin'])): ?>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-user-friends"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Next of Kin</div>
                                <div class="detail-value"><?= htmlspecialchars($student['next_of_kin']) ?></div>
                                <?php if (!empty($student['next_of_kin_contact'])): ?>
                                <div class="detail-value"><?= htmlspecialchars($student['next_of_kin_contact']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="profile-content">
                    <div class="data-table-container">
                        <div class="data-table-header">
                            <h2>Academic Information</h2>
                        </div>
                        
                        <div class="tab-pill-container">
                            <div class="tab-pill active" onclick="showTab('courses')">Enrolled Courses</div>
                            <div class="tab-pill" onclick="showTab('grades')">Grades & Performance</div>
                            <div class="tab-pill" onclick="showTab('details')">Additional Details</div>
                        </div>
                        
                        <div id="courses-tab" class="pill-content active">
                            <?php if (empty($courses)): ?>
                                <div class="no-courses">
                                    <i class="fas fa-book-open" style="font-size: 3rem; color: var(--gray-300); margin-bottom: 1rem;"></i>
                                    <p>This student is not enrolled in any courses yet.</p>
                                </div>
                            <?php else: 
                                // Group courses by semester and year from enrollment data
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
                                                <th>Status</th>
                                                <th>Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($semester_courses as $course): 
                                                $has_grades = $course['coursework_grade'] !== null && $course['final_exam_grade'] !== null;
                                                
                                                if ($has_grades) {
                                                    $total = round(($course['coursework_grade'] * 0.6) + ($course['final_exam_grade'] * 0.4), 2);
                                                    $letter_grade = getLetterGrade($total);
                                                }
                                            ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                                                    <td><?= htmlspecialchars($course['title']) ?></td>
                                                    <td><?= htmlspecialchars($course['enrollment_section'] ?? 'N/A') ?></td>
                                                    <td><?= $course['credits'] ?></td>
                                                    <td>
                                                        <?php if ($has_grades): ?>
                                                            <span class="badge <?= $total >= 50 ? 'badge-success' : 'badge-danger' ?>">
                                                                <?= $total >= 50 ? 'Completed' : 'Failed' ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge">In Progress</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($has_grades): ?>
                                                            <span class="badge <?= $letter_grade['class'] ?>">
                                                                <?= $letter_grade['grade'] ?> (<?= $total ?>%)
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge">Not Graded</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div id="grades-tab" class="pill-content">
                            <?php if (empty($courses)): ?>
                                <div class="no-courses">
                                    <i class="fas fa-chart-bar" style="font-size: 3rem; color: var(--gray-300); margin-bottom: 1rem;"></i>
                                    <p>No grades available yet.</p>
                                </div>
                            <?php else: 
                                // Get graded courses
                                $graded_courses = array_filter($courses, function($course) {
                                    return $course['coursework_grade'] !== null && $course['final_exam_grade'] !== null;
                                });
                                
                                if (empty($graded_courses)): 
                            ?>
                                <div class="no-courses">
                                    <i class="fas fa-chart-bar" style="font-size: 3rem; color: var(--gray-300); margin-bottom: 1rem;"></i>
                                    <p>No grades available yet.</p>
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
                                            
                                            foreach ($graded_courses as $course): 
                                                $total = round(($course['coursework_grade'] * 0.6) + ($course['final_exam_grade'] * 0.4), 2);
                                                $letter_grade = getLetterGrade($total);
                                                
                                                // Calculate quality points based on letter grade
                                                $quality_points = 0;
                                                if ($total >= 90) $quality_points = 4.00;
                                                elseif ($total >= 80) $quality_points = 3.67;
                                                elseif ($total >= 75) $quality_points = 3.50;
                                                elseif ($total >= 65) $quality_points = 3.00;
                                                elseif ($total >= 60) $quality_points = 2.67;
                                                elseif ($total >= 55) $quality_points = 2.33;
                                                elseif ($total >= 50) $quality_points = 2.00;
                                                elseif ($total >= 40) $quality_points = 1.67;
                                                
                                                $course_quality_points = $quality_points * $course['credits'];
                                                
                                                // Add to totals
                                                $total_credits += $course['credits'];
                                                $total_quality_points += $course_quality_points;
                                            ?>
                                                <tr>
                                                    <td>
                                                        <?= htmlspecialchars($course['title']) ?>
                                                        <div class="text-secondary"><?= htmlspecialchars($course['course_code']) ?></div>
                                                    </td>
                                                    <td><?= htmlspecialchars($course['enrollment_semester'] . ' ' . $course['enrollment_year']) ?></td>
                                                    <td><?= $course['credits'] ?></td>
                                                    <td><?= $course['coursework_grade'] ?></td>
                                                    <td><?= $course['final_exam_grade'] ?></td>
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
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div id="details-tab" class="pill-content">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <tbody>
                                        <?php if (!empty($student['home_address'])): ?>
                                        <tr>
                                            <th width="30%">Home Address</th>
                                            <td><?= htmlspecialchars($student['home_address']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($student['home_number'])): ?>
                                        <tr>
                                            <th>Home Phone</th>
                                            <td><?= htmlspecialchars($student['home_number']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($student['work_number'])): ?>
                                        <tr>
                                            <th>Work Phone</th>
                                            <td><?= htmlspecialchars($student['work_number']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($student['middle_name'])): ?>
                                        <tr>
                                            <th>Middle Name</th>
                                            <td><?= htmlspecialchars($student['middle_name']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        
                                        <tr>
                                            <th>Full Name</th>
                                            <td>
                                                <?= htmlspecialchars($student['first_name'] . ' ' . 
                                                   (!empty($student['middle_name']) ? $student['middle_name'] . ' ' : '') . 
                                                   $student['last_name']) ?>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <th>Enrollment Date</th>
                                            <td>
                                                <?php
                                                // Try to determine enrollment date from first course
                                                if (!empty($courses)) {
                                                    $enrollment_years = array_map(function($course) {
                                                        return $course['enrollment_year'] ?? null;
                                                    }, $courses);
                                                    $enrollment_years = array_filter($enrollment_years);
                                                    
                                                    if (!empty($enrollment_years)) {
                                                        echo min($enrollment_years);
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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
            <p>&copy; <?= date('Y') ?> UCC Registry System | Version 1.0</p>
        </div>
    </footer>
    
    <script>
        function showTab(tabName) {
            // Hide all pill content
            const pillContents = document.querySelectorAll('.pill-content');
            pillContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Show the selected content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Update pill buttons
            const pills = document.querySelectorAll('.tab-pill');
            pills.forEach(pill => {
                pill.classList.remove('active');
            });
            
            // Find the button that was clicked and make it active
            const clickedPill = document.querySelector(`.tab-pill[onclick="showTab('${tabName}')"]`);
            if (clickedPill) {
                clickedPill.classList.add('active');
            }
        }
    </script>
</body>
</html>