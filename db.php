<!--Name of Enterprise App: ucc_registrar
Developers: Ralston Campbell
Version:1.0 
Version Date:28/3/2025
Purpose: A php function that allows the application to connect to the UCC database  -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$password = "J@m@ic@1";
$database = "ucc_registry";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
