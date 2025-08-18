<!--
Name of Enterprise App: ucc_registrar
Developers: Ralston Campbell , Geordi Duncan
Version: 3.0 
Version Date: 6/4/2025
Purpose: A php function that adds lecturer to the database.
-->
<?php
session_start();
require 'db.php';

// Check if the user is logged in as admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Initialize messages array
$messages = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lecturer_id = intval($_POST["lecturer_id"]);
    $title = $_POST["title"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $lecturer_email = $_POST["lecturer_email"];
    $department = $_POST["department"];
    $position = $_POST["position"];

    // Check if lecturer ID already exists
    $check_sql = "SELECT lecturer_id FROM lecturers WHERE lecturer_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $lecturer_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $messages[] = ["type" => "error", "text" => "Lecturer ID already exists!"];
    } else {
        $sql = "INSERT INTO lecturers (lecturer_id, title, first_name, last_name, lecturer_email, department, position) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $lecturer_id, $title, $first_name, $last_name, $lecturer_email, $department, $position);

        if ($stmt->execute()) {
            $messages[] = ["type" => "success", "text" => "Lecturer added successfully!"];
            
            // Clear form data after successful submission
            $lecturer_id = $title = $first_name = $last_name = $lecturer_email = "";
            $department = $position = "";
        } else {
            $messages[] = ["type" => "error", "text" => "Error: " . $stmt->error];
        }

        $stmt->close();
    }
    $check_stmt->close();
}

// Function to get the first letter of the name for avatar
function getInitial($name) {
    return strtoupper(substr($name, 0, 1));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Lecturer | UCC Registry</title>
    <link rel="stylesheet" href="modern-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .logo img {
            height: 40px;
            margin-right: 10px;
        }
        
        .logo-text {
            font-size: 20px;
            font-weight: 600;
            color: #2563eb;
            display: flex;
            align-items: center;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <a href="admin_dashboard.php" class="logo">
                <img src="ucc_logo.png" alt="UCC Logo">
                <span class="logo-text">UCC Registry</span>
            </a>
            <div class="header-actions">
                <div class="user-menu">
                    <div class="user-avatar">
                        <?= getInitial($_SESSION['user']) ?>
                    </div>
                    <span><?= htmlspecialchars($_SESSION['user']) ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="dashboard">
        <div class="container">
            <div class="search-section mb-4">
                <div class="flex justify-between items-center">
                    <h2>
                        <i class="fas fa-chalkboard-teacher"></i>
                        Add New Lecturer
                    </h2>
                </div>
            </div>
            
            <!-- Messages -->
            <?php foreach ($messages as $message) : ?>
                <div class="search-section <?= $message['type'] === 'success' ? 'bg-success-light' : 'bg-danger-light' ?>" style="margin-bottom: 1rem; padding: 1rem;">
                    <div class="flex items-center gap-2">
                        <i class="fas <?= $message['type'] === 'success' ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger' ?>"></i>
                        <p class="<?= $message['type'] === 'success' ? 'text-success' : 'text-danger' ?>"><?= $message['text'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="data-table-container">
                <div class="data-table-header">
                    <h2>Lecturer Information</h2>
                </div>
                <div class="tab-content" style="padding: 1.5rem;">
                    <form method="post" class="mb-4">
                        <div class="form-grid">
                            <div>
                                <label for="lecturer_id" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Lecturer ID:</label>
                                <input type="number" name="lecturer_id" id="lecturer_id" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($lecturer_id ?? '') ?>">
                            </div>
                            
                            <div>
                                <label for="title" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Title:</label>
                                <select name="title" id="title" required class="search-input" style="width: 100%;">
                                    <option value="" disabled <?= empty($title ?? '') ? 'selected' : '' ?>>Select Title</option>
                                    <option value="Mr." <?= ($title ?? '') === 'Mr.' ? 'selected' : '' ?>>Mr.</option>
                                    <option value="Mrs." <?= ($title ?? '') === 'Mrs.' ? 'selected' : '' ?>>Mrs.</option>
                                    <option value="Ms." <?= ($title ?? '') === 'Ms.' ? 'selected' : '' ?>>Ms.</option>
                                    <option value="Dr." <?= ($title ?? '') === 'Dr.' ? 'selected' : '' ?>>Dr.</option>
                                    <option value="Prof." <?= ($title ?? '') === 'Prof.' ? 'selected' : '' ?>>Prof.</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="first_name" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">First Name:</label>
                                <input type="text" name="first_name" id="first_name" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($first_name ?? '') ?>">
                            </div>
                            
                            <div>
                                <label for="last_name" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Last Name:</label>
                                <input type="text" name="last_name" id="last_name" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($last_name ?? '') ?>">
                            </div>
                            
                            <div>
                                <label for="lecturer_email" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Email:</label>
                                <input type="email" name="lecturer_email" id="lecturer_email" required class="search-input" style="width: 100%;" value="<?= htmlspecialchars($lecturer_email ?? '') ?>">
                            </div>
                            
                            <div>
                                <label for="department" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Department:</label>
                                <select name="department" id="department" required class="search-input" style="width: 100%;">
                                    <option value="" disabled <?= empty($department ?? '') ? 'selected' : '' ?>>Select Department</option>
                                    <option value="Business Administration" <?= ($department ?? '') === 'Business Administration' ? 'selected' : '' ?>>Business Administration</option>
                                    <option value="Information Technology" <?= ($department ?? '') === 'Information Technology' ? 'selected' : '' ?>>Information Technology</option>
                                    <option value="General Studies and Behavioural" <?= ($department ?? '') === 'General Studies and Behavioural' ? 'selected' : '' ?>>General Studies and Behavioural</option>
                                    <option value="Mathematics" <?= ($department ?? '') === 'Mathematics' ? 'selected' : '' ?>>Mathematics</option>
                                    <option value="Law" <?= ($department ?? '') === 'Law' ? 'selected' : '' ?>>Law</option>
                                    <option value="Tourism and Hospitality" <?= ($department ?? '') === 'Tourism and Hospitality' ? 'selected' : '' ?>>Tourism and Hospitality</option>
                                    <option value="College of Graduate Studies and Research" <?= ($department ?? '') === 'College of Graduate Studies and Research' ? 'selected' : '' ?>>College of Graduate Studies and Research</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="position" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Position:</label>
                                <select name="position" id="position" required class="search-input" style="width: 100%;">
                                    <option value="" disabled <?= empty($position ?? '') ? 'selected' : '' ?>>Select Position</option>
                                    <option value="Adjunct Lecturer" <?= ($position ?? '') === 'Adjunct Lecturer' ? 'selected' : '' ?>>Adjunct Lecturer</option>
                                    <option value="Staff Lecturer" <?= ($position ?? '') === 'Staff Lecturer' ? 'selected' : '' ?>>Staff Lecturer</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i>
                                Add Lecturer
                            </button>
                            <button type="reset" class="btn btn-secondary" style="margin-left: 0.5rem;">
                                <i class="fas fa-undo"></i>
                                Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-content">
            <p>University of the Commonwealth Caribbean - Registry Department</p>
            <p>&copy; <?= date('Y') ?> UCC Registry System | Version 1.0</p>
            <div class="footer-links">
                <a href="admin_dashboard.php" class="footer-link">Back to Dashboard</a>
            </div>
        </div>
    </footer>
</body>
</html>