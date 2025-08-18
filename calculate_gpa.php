<!--
Name of Enterprise App: ucc_registrar
Developers:Geordi Duncan
Version:2.0 
Version Date:30/3/2025
Purpose: A php function that calculates the grade for each course and uses the weight 60 40 to calculate overall grade.>
-->
<?php
include "db.php";

function getLetterGrade($grade) {
    if ($grade >= 80) return ['grade' => 'A-', 'points' => 3.67];
    elseif ($grade >= 75) return ['grade' => 'B+', 'points' => 3.50];
    elseif ($grade >= 65) return ['grade' => 'B', 'points' => 3.00];
    elseif ($grade >= 60) return ['grade' => 'B-', 'points' => 2.67];
    elseif ($grade >= 55) return ['grade' => 'C+', 'points' => 2.33];
    elseif ($grade >= 50) return ['grade' => 'C', 'points' => 2.00];
    elseif ($grade >= 40) return ['grade' => 'D', 'points' => 1.67];
    else return ['grade' => 'F', 'points' => 0.00];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Grades</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h2>Enrollment Grades</h2>

    <?php
    $sql = "SELECT s.student_name, e.course_code, e.coursework_grade, e.final_exam_grade
            FROM course_enrollment e
            JOIN students s ON e.student_id = s.student_id";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr>
                <th>Student Name</th>
                <th>Course Code</th>
                <th>Coursework</th>
                <th>Final Exam</th>
                <th>Total Grade</th>
                <th>Letter Grade</th>
                <th>Quality Points</th>
              </tr>";

        while ($row = $result->fetch_assoc()) {
            $cw = $row['coursework_grade'];
            $fe = $row['final_exam_grade'];

            $total = round(($cw * 0.6) + ($fe * 0.4), 2);
            $gradeData = getLetterGrade($total);

            echo "<tr>
                    <td>{$row['student_name']}</td>
                    <td>{$row['course_code']}</td>
                    <td>$cw</td>
                    <td>$fe</td>
                    <td>$total</td>
                    <td>{$gradeData['grade']}</td>
                    <td>{$gradeData['points']}</td>
                  </tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No enrollments found.</p>";
    }

    $conn->close();
    ?>
</div>

</body>
</html>
