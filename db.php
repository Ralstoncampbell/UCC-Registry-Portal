<!--
Name of Enterprise App: UCC Registrar
Developers: Ralston Campbell
Version: 4.0 
Version Date: Dec/2/2025
Purpose: A php function that allows the application to connect to the UCC database.
-->

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host     = "localhost";
$user     = "root";
$password = "Campbell@22";
$database = "ucc_registry";
$port     = 3306; // Explicit port for MySQL Workbench

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
