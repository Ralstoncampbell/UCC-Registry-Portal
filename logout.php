<!--
Name of Enterprise App: UCC Registrar
Developers: Ralston Campbell
Version: 4.0 
Version Date: Dec/2/2025
Purpose: A php function that logs user out of application.
-->

<?php
session_start();
session_destroy();
header('location:login.php');
exit();
?>
