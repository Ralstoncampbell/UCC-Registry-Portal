<!--Name of Enterprise App: ucc_registrar
Developers: Geordi Duncan
Version:1.0 
Version Date:30/3/2025
Purpose: A php function that updates edits to a selected enrollment to the database -->
<?php
include "db.php";

$enrollment = null;
$student_id = '';
$course_code = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "load") {
    $student_id = $_POST["student_id"];
    $course_code = $_POST["course_code"];

    if (!empty($student_id) && !empty($course_code)) {
        $sql = "SELECT * FROM course_enrollment WHERE student_id = ? AND course_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $student_id, $course_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $enrollment = $result->fetch_assoc();
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "update") {
    $student_id = $_POST["student_id"];
    $course_code = $_POST["course_code"];
    $coursework_grade = $_POST["coursework_grade"];
    $final_exam_grade = $_POST["final_exam_grade"];

    $sql = "UPDATE course_enrollment SET coursework_grade = ?, final_exam_grade = ? WHERE student_id = ? AND course_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddis", $coursework_grade, $final_exam_grade, $student_id, $course_code);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Enrollment updated successfully!'); window.location.href='admin_dashboard.php#enrollment';</script>";
    } else {
        echo "<script>alert('❌ Error: " . $conn->error . "');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Enrollment</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h2>Edit Enrollment</h2>

    <form class="horizontal-form" method="post">
        <input type="hidden" name="action" value="load">
        <div class="form-group">
            <label>Select Student:</label>
            <select name="student_id" required>
                <option value="">-- Select Student --</option>
                <?php
                $students = $conn->query("SELECT student_id, first_name, last_name FROM students");
                while ($row = $students->fetch_assoc()) {
                    $selected = ($row['student_id'] == $student_id) ? "selected" : "";
                    echo "<option value='{$row['student_id']}' $selected>ID: {$row['student_id']} - {$row['first_name']} {$row['last_name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Select Course:</label>
            <select name="course_code" required>
                <option value="">-- Select Course --</option>
                <?php
                $courses = $conn->query("SELECT course_code, title FROM courses");
                while ($row = $courses->fetch_assoc()) {
                    $selected = ($row['course_code'] == $course_code) ? "selected" : "";
                    echo "<option value='{$row['course_code']}' $selected>Code: {$row['course_code']} - {$row['title']}</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit">Load Enrollment</button>
    </form>

    <?php if ($enrollment): ?>
        <hr>
        <form class="horizontal-form" method="post">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_id) ?>">
            <input type="hidden" name="course_code" value="<?= htmlspecialchars($course_code) ?>">

            <div class="form-group">
                <label>Coursework Grade (%):</label>
                <input type="number" step="0.01" min="0" max="100" name="coursework_grade"
                       value="<?= htmlspecialchars($enrollment['coursework_grade']) ?>" required>
            </div>

            <div class="form-group">
                <label>Final Exam Grade (%):</label>
                <input type="number" step="0.01" min="0" max="100" name="final_exam_grade"
                       value="<?= htmlspecialchars($enrollment['final_exam_grade']) ?>" required>
            </div>

            <button type="submit">Update Enrollment</button>
        </form>
    <?php elseif ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["action"] === "load"): ?>
        <p class="error">❌ No enrollment record found for that student and course.</p>
    <?php endif; ?>
</div>

</body>
</html>
