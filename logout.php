<!--Name of Enterprise App: ucc_registrar
Developers: Ralston Campbell
Version:1.0 
Version Date:30/3/2025
Purpose: A php function that logs user out of application. -->

<?php
session_start();
session_destroy();
header('location:login.php');
exit();
?>
