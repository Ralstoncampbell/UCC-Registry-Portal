<!--
Name of Enterprise App: ucc_registrar
Developers:Ralston Campbell
Version:1.0 
Version Date:30/3/2025
Purpose: A php function that deletes a selected enrollment from databaseo the database.>
-->
<?php
include "db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Enrollment</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php
if (isset($_GET["enrollment_id"])) {
    $enrollment_id = $_GET["enrollment_id"];

    $check_sql = "SELECT * FROM course_enrollment WHERE enrollment_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $enrollment_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        echo "<script>alert('❌ Error: Enrollment not found.'); window.location.href='admin_dashboard.php#enrollment';</script>";
        $check_stmt->close();
        $conn->close();
        exit();
    }
    $check_stmt->close();

    $sql = "DELETE FROM course_enrollment WHERE enrollment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $enrollment_id);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Enrollment Deleted Successfully!'); window.location.href='admin_dashboard.php#enrollment';</script>";
    } else {
        echo "<script>alert('❌ Error: " . $conn->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<div class='container'><p class='error'>❌ No enrollment ID provided.</p></div>";
}
?>

</body>
</html>
