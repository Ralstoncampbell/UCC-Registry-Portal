<!--Name of Enterprise App: ucc_registrar
Developers: Geordi Duncan
Version:1.0 
Version Date:30/3/2025
Purpose: A php function that shows history of classes taken and average grades for a selected user.-->
<?php
session_start();
include "db.php";

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Get student information
$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// Calculate letter grade and points
function getLetterGrade($grade) {
    if ($grade >= 90) return ['grade' => 'A', 'points' => 4.00, 'standing' => 'Summa Cum Laude'];
    elseif ($grade >= 80) return ['grade' => 'A-', 'points' => 3.67, 'standing' => 'Summa Cum Laude'];
    elseif ($grade >= 75) return ['grade' => 'B+', 'points' => 3.50, 'standing' => 'Magna Cum Laude'];
    elseif ($grade >= 65) return ['grade' => 'B', 'points' => 3.00, 'standing' => 'Cum Laude'];
    elseif ($grade >= 60) return ['grade' => 'B-', 'points' => 2.67, 'standing' => 'Pass'];
    elseif ($grade >= 55) return ['grade' => 'C+', 'points' => 2.33, 'standing' => 'Credit'];
    elseif ($grade >= 50) return ['grade' => 'C', 'points' => 2.00, 'standing' => 'Pass'];
    elseif ($grade >= 40) return ['grade' => 'D', 'points' => 1.67, 'standing' => 'Pass'];
    else return ['grade' => 'F', 'points' => 0.00, 'standing' => 'Fail'];
}

// Get course enrollment data
$sql = "SELECT 
            e.course_code, 
            c.title, 
            c.credits, 
            e.coursework_grade, 
            e.final_exam_grade,
            cs.semester,
            cs.year
        FROM 
            course_enrollment e
        JOIN 
            courses c ON e.course_code = c.course_code
        LEFT JOIN 
            course_schedule cs ON e.course_code = cs.course_code
        WHERE 
            e.student_id = ?
        ORDER BY 
            cs.year, 
            CASE 
                WHEN cs.semester = 'Spring' THEN 1 
                WHEN cs.semester = 'Summer' THEN 2 
                WHEN cs.semester = 'Fall' THEN 3 
                ELSE 4 
            END";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Group courses by semester
$semesters = [];
$totalCredits = 0;
$totalPoints = 0;

while ($row = $result->fetch_assoc()) {
    $semesterKey = isset($row['semester']) && isset($row['year']) ? $row['semester'] . ' ' . $row['year'] : 'Unscheduled';
    
    if (!isset($semesters[$semesterKey])) {
        $semesters[$semesterKey] = [
            'courses' => [],
            'semesterCredits' => 0,
            'semesterPoints' => 0
        ];
    }
    
    $totalScore = round(($row['coursework_grade'] * 0.6) + ($row['final_exam_grade'] * 0.4), 2);
    $gradeData = getLetterGrade($totalScore);
    $qualityPoints = $row['credits'] * $gradeData['points'];
    
    $semesters[$semesterKey]['courses'][] = [
        'code' => $row['course_code'],
        'title' => $row['title'],
        'credits' => $row['credits'],
        'coursework' => $row['coursework_grade'],
        'final' => $row['final_exam_grade'],
        'total' => $totalScore,
        'letter' => $gradeData['grade'],
        'points' => $gradeData['points'],
        'qualityPoints' => $qualityPoints
    ];
    
    $semesters[$semesterKey]['semesterCredits'] += $row['credits'];
    $semesters[$semesterKey]['semesterPoints'] += $qualityPoints;
    
    $totalCredits += $row['credits'];
    $totalPoints += $qualityPoints;
}

$cumulativeGPA = ($totalCredits > 0) ? round($totalPoints / $totalCredits, 2) : 0;
$academicStanding = "";

if ($cumulativeGPA >= 3.67) {
    $academicStanding = "Summa Cum Laude";
} elseif ($cumulativeGPA >= 3.50) {
    $academicStanding = "Magna Cum Laude";
} elseif ($cumulativeGPA >= 3.00) {
    $academicStanding = "Cum Laude";
} elseif ($cumulativeGPA >= 2.00) {
    $academicStanding = "Good Standing";
} else {
    $academicStanding = "Academic Probation";
}

// Get current date for transcript
$currentDate = date("F j, Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Transcript - <?= $student['first_name'] . ' ' . $student['last_name'] ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background: #fff;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        .transcript {
            max-width: 1000px;
            margin: 20px auto;
            padding: 40px;
            border: 1px solid #000;
            background: #fff;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .university-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 15px 0;
        }
        
        .student-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .student-info div {
            margin-bottom: 10px;
        }
        
        .semester-header {
            background: #f0f0f0;
            padding: 10px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            margin-top: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table, th, td {
            border: 1px solid #000;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f0f0f0;
        }
        
        .semester-summary {
            text-align: right;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .gpa-summary {
            margin-top: 30px;
            border-top: 2px solid #000;
            padding-top: 20px;
        }
        
        .academic-standing {
            margin-top: 10px;
            font-weight: bold;
            font-size: 18px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 14px;
        }
        
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            width: 250px;
            display: inline-block;
            text-align: center;
            padding-top: 5px;
        }
        
        .print-button {
            text-align: center;
            margin: 20px;
        }
        
        .print-button button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            opacity: 0.1;
            color: #007bff;
            pointer-events: none;
            z-index: 1000;
        }
        
        .confidential {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 40px;
        }

        @media print {
            .print-button {
                display: none;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .transcript {
                border: none;
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()">Print Transcript</button>
        <button onclick="window.location.href='student_dashboard.php'">Back to Dashboard</button>
    </div>
    
    <div class="watermark">OFFICIAL COPY</div>
    
    <div class="transcript">
        <div class="header">
            <div class="university-name">UNIVERSITY OF THE COMMONWEALTH CARIBBEAN</div>
            <div>17 Worthington Ave, Kingston, Jamaica</div>
            <div>Tel: 876-123-4567 | Email: registrar@ucc.edu.jm</div>
            <div class="document-title">Official Academic Transcript</div>
        </div>
        
        <div class="student-info">
            <div>
                <strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?><br>
                <strong>Name:</strong> <?= htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']) ?><br>
                <strong>Program:</strong> <?= htmlspecialchars($student['program']) ?>
            </div>
            <div>
                <strong>Issue Date:</strong> <?= $currentDate ?><br>
                <strong>Cumulative GPA:</strong> <?= number_format($cumulativeGPA, 2) ?><br>
                <strong>Academic Standing:</strong> <?= $academicStanding ?>
            </div>
        </div>
        
        <?php foreach ($semesters as $semester => $data) : ?>
            <div class="semester-header"><?= htmlspecialchars($semester) ?></div>
            <table>
                <tr>
                    <th>Course Code</th>
                    <th>Course Title</th>
                    <th>Credits</th>
                    <th>Coursework</th>
                    <th>Final Exam</th>
                    <th>Total</th>
                    <th>Grade</th>
                    <th>Points</th>
                </tr>
                
                <?php foreach ($data['courses'] as $course) : ?>
                    <tr>
                        <td><?= htmlspecialchars($course['code']) ?></td>
                        <td><?= htmlspecialchars($course['title']) ?></td>
                        <td><?= $course['credits'] ?></td>
                        <td><?= $course['coursework'] ?></td>
                        <td><?= $course['final'] ?></td>
                        <td><?= $course['total'] ?></td>
                        <td><?= $course['letter'] ?></td>
                        <td><?= number_format($course['qualityPoints'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            
            <div class="semester-summary">
                Semester Credits: <?= $data['semesterCredits'] ?> | 
                Quality Points: <?= number_format($data['semesterPoints'], 2) ?> | 
                Semester GPA: <?= number_format($data['semesterPoints'] / $data['semesterCredits'], 2) ?>
            </div>
        <?php endforeach; ?>
        
        <div class="gpa-summary">
            <strong>Total Credits Attempted:</strong> <?= $totalCredits ?><br>
            <strong>Total Quality Points:</strong> <?= number_format($totalPoints, 2) ?><br>
            <strong>Cumulative GPA:</strong> <?= number_format($cumulativeGPA, 2) ?>
            
            <div class="academic-standing">
                Academic Standing: <?= $academicStanding ?>
            </div>
        </div>
        
        <div class="footer">
            <div class="signature-line">
                Registrar's Signature
            </div>
            
            <div class="confidential">
                This transcript is official only if it bears the seal of the University of the Commonwealth Caribbean and the signature of the Registrar.
                This document contains confidential information protected under the Family Educational Rights and Privacy Act (FERPA).
            </div>
        </div>
    </div>
</body>
</html>