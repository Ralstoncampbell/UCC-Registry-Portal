<!--
Name of Enterprise App: UCC Registrar
Developers: Ralston Campbell
Version: 4.0 
Version Date: Dec/2/2025
Purpose: A php function that user to select role admin or student and access the application.
-->

<?php 
session_start();
include "db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>UCC Registry - Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .form-group {
            position: relative;
            margin-bottom: 15px;
        }
        .toggle-password {
            position: absolute;
            top: 38px;
            right: 15px;
            cursor: pointer;
            font-size: 18px;
            user-select: none;
        }
        .login-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .login-tab {
            padding: 10px 20px;
            cursor: pointer;
            background: #f4f4f4;
            border: 1px solid #ddd;
            border-radius: 5px 5px 0 0;
            margin: 0 5px;
        }
        .login-tab.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        .login-form {
            display: none;
        }
        .login-form.active {
            display: block;
        }
    </style>
</head>
<body class="login-page" style="background: url('background-image.jpeg') no-repeat center center fixed; background-size: cover;">

<header>University of the Commonwealth Caribbean</header>

<div class="container">
    <div class="login-tabs">
        <div class="login-tab active" onclick="showLoginForm('admin')">Admin Login</div>
        <div class="login-tab" onclick="showLoginForm('student')">Student Login</div>
    </div>

    <!-- Admin Login Form -->
    <div id="admin-login" class="login-form active">
        <h2>Admin Login</h2>
        <form method="post" class="horizontal-form">
            <input type="hidden" name="role" value="admin">

            <div class="form-group">
                <label for="admin-username">Username</label>
                <input class="form-control" name="username" id="admin-username" placeholder="Admin Username" required>
            </div>

            <div class="form-group">
                <label for="admin-password">Password</label>
                <input class="form-control" type="password" name="password" id="admin-password" placeholder="Password" required>
                <span class="toggle-password" onclick="togglePassword('admin-password')">👁️</span>
            </div>

            <button class="btn btn-primary" type="submit">Login</button>
        </form>
    </div>

    <!-- Student Login Form -->
    <div id="student-login" class="login-form">
        <h2>Student Login</h2>
        <form method="post" class="horizontal-form">
            <input type="hidden" name="role" value="student">

            <div class="form-group">
                <label for="student-id">Student ID</label>
                <input class="form-control" name="student_id" id="student-id" placeholder="Enter your Student ID" required>
            </div>

            <div class="form-group">
                <label for="student-password">Password</label>
                <input class="form-control" type="password" name="password" id="student-password" placeholder="Password" required>
                <span class="toggle-password" onclick="togglePassword('student-password')">👁️</span>
            </div>

            <button class="btn btn-primary" type="submit">Login</button>
        </form>
    </div>
</div>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['role']) && $_POST['role'] === 'admin') {
        $user = $_POST['username'];
        $pass = $_POST['password'];
        
 // Default admin credentials
        $validUsers = [
            'Ralston' => '12345678',
            'Admin' => 'Admin123',
        ];

        if (isset($validUsers[$user]) && $validUsers[$user] === $pass) {
            $_SESSION['user'] = $user;
            $_SESSION['role'] = 'admin';

            header('Location: admin_dashboard.php');
            exit;
        } else {
            echo "<p class='error'>Invalid admin credentials</p>";
        }
    } else if (isset($_POST['role']) && $_POST['role'] === 'student') {
        $student_id = $_POST['student_id'];
        $password = $_POST['password'];
        
        // For simplicity, we're using student_id as both username and password
        // In a real application, you would have a proper password hashing system
        $sql = "SELECT * FROM students WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $student = $result->fetch_assoc();
            
            // Simple password verification - in production use proper password hashing
            // Here we're just checking if password matches student ID for demo purposes
            if ($password === $student_id) {
                $_SESSION['user'] = $student['first_name'] . ' ' . $student['last_name'];
                $_SESSION['role'] = 'student';
                $_SESSION['student_id'] = $student_id;
                
                header('Location: student_dashboard.php');
                exit;
            }
        }
        echo "<p class='error'>Invalid student credentials. For this demo, use your student ID as the password.</p>";
    }
}
?>

<script>
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const icon = passwordField.nextElementSibling;
    
    if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.textContent = "🙈";
    } else {
        passwordField.type = "password";
        icon.textContent = "👁️";
    }
}

function showLoginForm(formType) {
    // Hide all forms
    document.querySelectorAll('.login-form').forEach(form => {
        form.classList.remove('active');
    });
    
    // Deactivate all tabs
    document.querySelectorAll('.login-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected form and activate tab
    document.getElementById(formType + '-login').classList.add('active');
    document.querySelector(`.login-tab[onclick="showLoginForm('${formType}')"]`).classList.add('active');
}
</script>

</body>
</html>