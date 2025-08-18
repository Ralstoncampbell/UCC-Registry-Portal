<!--
Name of Enterprise App: ucc_registrar
Developers:Ralston Campbell
Version:1.0 
Version Date:30/3/2025
Purpose: A php function that deletes a selected student from database >
-->
<?php
include "db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Student</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php
if (isset($_GET["student_id"])) {
    $student_id = $_GET["student_id"];

    $sql1 = "DELETE FROM course_enrollment WHERE student_id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $student_id);
    $stmt1->execute();

    $sql2 = "DELETE FROM students WHERE student_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $student_id);

    if ($stmt2->execute()) {
        echo "<script>alert('✅ Student deleted successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('❌ Error: " . $conn->error . "'); window.history.back();</script>";
    }
}
?>

</body>
</html>
