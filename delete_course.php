<!--
Name of Enterprise App: UCC Registrar
Developers: Ralston Campbell
Version: 4.0 
Version Date: Dec/2/2025
Purpose: A php function that deletes a selected course from databaseo the database.
-->

<?php
include "db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Course</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php
if (isset($_GET["course_code"])) {
    $course_code = $_GET["course_code"];

    $sql1 = "DELETE FROM course_enrollment WHERE course_code = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("s", $course_code);
    $stmt1->execute();
    $stmt1->close();

    $sql2 = "DELETE FROM course_schedule WHERE course_code = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("s", $course_code);
    $stmt2->execute();
    $stmt2->close();

    $sql3 = "DELETE FROM courses WHERE course_code = ?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("s", $course_code);

    if ($stmt3->execute()) {
        echo "<script>alert('✅ Course deleted successfully!'); window.location.href='admin_dashboard.php#courses';</script>";
    } else {
        echo "<script>alert('❌ Error: " . $conn->error . "'); window.history.back();</script>";
    }

    $stmt3->close();
}
?>

</body>
</html>
