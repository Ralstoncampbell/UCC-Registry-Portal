<!--
Name of Enterprise App: ucc_registrar
Developers:Ralston Campbell, Geordi Duncan
Version:2.0 
Version Date:30/3/2025
Purpose: A php function that reads students completed grades and calculates the current gpa and updates it.>
-->
<?php
session_start();
include "db.php";

// Check if the user is logged in as admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Function to calculate letter grade and points
function getLetterGrade($grade) {
    if ($grade >= 90) return ['grade' => 'A', 'points' => 4.00];
    elseif ($grade >= 80) return ['grade' => 'A-', 'points' => 3.67];
    elseif ($grade >= 75) return ['grade' => 'B+', 'points' => 3.50];
    elseif ($grade >= 65) return ['grade' => 'B', 'points' => 3.00];
    elseif ($grade >= 60) return ['grade' => 'B-', 'points' => 2.67];
    elseif ($grade >= 55) return ['grade' => 'C+', 'points' => 2.33];
    elseif ($grade >= 50) return ['grade' => 'C', 'points' => 2.00];
    elseif ($grade >= 40) return ['grade' => 'D', 'points' => 1.67];
    else return ['grade' => 'F', 'points' => 0.00];
}

// Function to calculate and update GPA for a student
function updateGPA($conn, $student_id) {
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
        $total = round(($row['coursework_grade'] * 0.6) + ($row['final_exam_grade'] * 0.4), 2);
        $gradeData = getLetterGrade($total);
        
        $totalCredits += $credits;
        $totalPoints += ($gradeData['points'] * $credits);
    }
    
    $gpa = ($totalCredits > 0) ? round($totalPoints / $totalCredits, 2) : 0;
    
    // Update the student's GPA
    $update_sql = "UPDATE students SET gpa = ? WHERE student_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("di", $gpa, $student_id);
    $update_stmt->execute();
    
    return [
        'gpa' => $gpa,
        'totalCredits' => $totalCredits,
        'totalPoints' => $totalPoints
    ];
}

// Process specific student if ID is provided, otherwise update all students
$updated = [];
$errors = [];

if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);
    
    // Check if student exists
    $check_sql = "SELECT student_id, first_name, last_name FROM students WHERE student_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $student_id);
    $check_stmt->execute();
    $student_result = $check_stmt->get_result();
    
    if ($student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        $result = updateGPA($conn, $student_id);
        $updated[] = [
            'id' => $student_id,
            'name' => $student['first_name'] . ' ' . $student['last_name'],
            'gpa' => $result['gpa']
        ];
    } else {
        $errors[] = "Student with ID $student_id not found.";
    }
} else {
    // Update all students
    $all_students = $conn->query("SELECT student_id, first_name, last_name FROM students");
    
    while ($student = $all_students->fetch_assoc()) {
        $student_id = $student['student_id'];
        $result = updateGPA($conn, $student_id);
        $updated[] = [
            'id' => $student_id,
            'name' => $student['first_name'] . ' ' . $student['last_name'],
            'gpa' => $result['gpa']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student GPA</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .results-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .error-list {
            background: #f8d7da;
            border-left: 5px solid #dc3545;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="top-right-buttons">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="container">
        <h2>Update Student GPA</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <h3>Errors</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="results-container">
            <h3>GPA Update Results</h3>
            
            <?php if (empty($updated)): ?>
                <p>No student records were updated.</p>
            <?php else: ?>
                <p><?= count($updated) ?> student GPAs have been updated.</p>
                
                <table border="1">
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Updated GPA</th>
                        <th>Standing</th>
                    </tr>
                    <?php foreach ($updated as $student): ?>
                        <tr>
                            <td><?= $student['id'] ?></td>
                            <td><?= htmlspecialchars($student['name']) ?></td>
                            <td><?= number_format($student['gpa'], 2) ?></td>
                            <td>
                                <?php 
                                $gpa = $student['gpa'];
                                if ($gpa >= 3.67): ?>
                                    <span class="status-badge status-success">Summa Cum Laude</span>
                                <?php elseif ($gpa >= 3.50): ?>
                                    <span class="status-badge status-success">Magna Cum Laude</span>
                                <?php elseif ($gpa >= 3.00): ?>
                                    <span class="status-badge status-success">Cum Laude</span>
                                <?php elseif ($gpa >= 2.00): ?>
                                    <span class="status-badge status-success">Good Standing</span>
                                <?php else: ?>
                                    <span class="status-badge status-warning">Academic Probation</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
            
            <div style="margin-top: 20px;">
                <a href="update_gpa.php" class="btn btn-primary">Update All Student GPAs</a>
                <a href="admin_dashboard.php" class="btn btn-secondary">Return to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>