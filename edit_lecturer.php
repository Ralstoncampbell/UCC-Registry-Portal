<!--
Name of Enterprise App: UCC Registrar
Developers: Ralston Campbell
Version: 4.0 
Version Date: Dec/2/2025
Purpose: A php function that updates edits to a selected Lecturer in the database.
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

$title = $first_name = $last_name = $lecturer_email = $department = $position = "";
$lecturer_id = isset($_GET["lecturer_id"]) ? intval($_GET["lecturer_id"]) : 0;
$lecturer_exists = false;

if ($lecturer_id) {
    $sql = "SELECT * FROM lecturers WHERE lecturer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lecturer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $lecturer_exists = true;
        $title = htmlspecialchars($row["title"]);
        $first_name = htmlspecialchars($row["first_name"]);
        $last_name = htmlspecialchars($row["last_name"]);
        $lecturer_email = htmlspecialchars($row["lecturer_email"]);
        $department = htmlspecialchars($row["department"]);
        $position = htmlspecialchars($row["position"]);
    } else {
        $messages[] = ["type" => "error", "text" => "Lecturer not found."];
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $lecturer_email = $_POST["lecturer_email"];
    $department = $_POST["department"];
    $position = $_POST["position"];
    $lecturer_id = $_POST["lecturer_id"];

    if (empty($title) || empty($first_name) || empty($last_name) || empty($lecturer_email) || empty($department) || empty($position)) {
        $messages[] = ["type" => "error", "text" => "All fields are required!"];
    } else {
        $sql = "UPDATE lecturers SET title=?, first_name=?, last_name=?, lecturer_email=?, department=?, position=? WHERE lecturer_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $title, $first_name, $last_name, $lecturer_email, $department, $position, $lecturer_id);

        if ($stmt->execute()) {
            $messages[] = ["type" => "success", "text" => "Lecturer updated successfully!"];
            // Refresh data
            $sql = "SELECT * FROM lecturers WHERE lecturer_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $lecturer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $title = htmlspecialchars($row["title"]);
                $first_name = htmlspecialchars($row["first_name"]);
                $last_name = htmlspecialchars($row["last_name"]);
                $lecturer_email = htmlspecialchars($row["lecturer_email"]);
                $department = htmlspecialchars($row["department"]);
                $position = htmlspecialchars($row["position"]);
            }
        } else {
            $messages[] = ["type" => "error", "text" => "Error: " . $stmt->error];
        }
        $stmt->close();
    }
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
    <title>Edit Lecturer | UCC Registry</title>
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
        
        .lecturer-header {
            display: flex;
            align-items: center;
        }
        
        .lecturer-badge {
            background-color: #e2e8f0;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 1rem;
            font-weight: 600;
            margin-left: 1rem;
        }
        
        .no-lecturer {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .no-lecturer i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
        
        .no-lecturer p {
            font-size: 1.125rem;
            color: #64748b;
            margin-bottom: 1.5rem;
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
                    <div class="lecturer-header">
                        <h2>
                            <i class="fas fa-edit"></i>
                            Edit Lecturer
                        </h2>
                        <?php if ($lecturer_exists): ?>
                            <span class="lecturer-badge">ID: <?= $lecturer_id ?></span>
                            <span class="lecturer-badge"><?= htmlspecialchars($title . ' ' . $first_name . ' ' . $last_name) ?></span>
                        <?php endif; ?>
                    </div>
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
            
            <?php if ($lecturer_exists): ?>
                <div class="data-table-container">
                    <div class="data-table-header">
                        <h2>Lecturer Information</h2>
                    </div>
                    <div class="tab-content" style="padding: 1.5rem;">
                        <form method="post" class="mb-4">
                            <input type="hidden" name="lecturer_id" value="<?= $lecturer_id ?>">
                            
                            <div class="form-grid">
                                <div>
                                    <label for="title" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Title:</label>
                                    <select name="title" id="title" required class="search-input" style="width: 100%;">
                                        <option value="Mr." <?= ($title == "Mr.") ? "selected" : "" ?>>Mr.</option>
                                        <option value="Mrs." <?= ($title == "Mrs.") ? "selected" : "" ?>>Mrs.</option>
                                        <option value="Ms." <?= ($title == "Ms.") ? "selected" : "" ?>>Ms.</option>
                                        <option value="Dr." <?= ($title == "Dr.") ? "selected" : "" ?>>Dr.</option>
                                        <option value="Prof." <?= ($title == "Prof.") ? "selected" : "" ?>>Prof.</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="first_name" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">First Name:</label>
                                    <input type="text" name="first_name" id="first_name" required class="search-input" style="width: 100%;" value="<?= $first_name ?>">
                                </div>
                                
                                <div>
                                    <label for="last_name" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Last Name:</label>
                                    <input type="text" name="last_name" id="last_name" required class="search-input" style="width: 100%;" value="<?= $last_name ?>">
                                </div>
                                
                                <div>
                                    <label for="lecturer_email" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Email:</label>
                                    <input type="email" name="lecturer_email" id="lecturer_email" required class="search-input" style="width: 100%;" value="<?= $lecturer_email ?>">
                                </div>
                                
                                <div>
                                    <label for="department" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Department:</label>
                                    <select name="department" id="department" required class="search-input" style="width: 100%;">
                                        <option value="Business Administration" <?= ($department == "Business Administration" || $department == "Business Administratortion") ? "selected" : "" ?>>Business Administration</option>
                                        <option value="Information Technology" <?= ($department == "Information Technology") ? "selected" : "" ?>>Information Technology</option>
                                        <option value="General Studies and Behavioural" <?= ($department == "General Studies and Behavioural") ? "selected" : "" ?>>General Studies and Behavioural</option>
                                        <option value="Mathematics" <?= ($department == "Mathematics") ? "selected" : "" ?>>Mathematics</option>
                                        <option value="Law" <?= ($department == "Law") ? "selected" : "" ?>>Law</option>
                                        <option value="Tourism and Hospitality" <?= ($department == "Tourism and Hospitality") ? "selected" : "" ?>>Tourism and Hospitality</option>
                                        <option value="College of Graduate Studies and Research" <?= ($department == "College of Graduate Studies and Research") ? "selected" : "" ?>>College of Graduate Studies and Research</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="position" class="mb-1" style="display: block; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem;">Position:</label>
                                    <select name="position" id="position" required class="search-input" style="width: 100%;">
                                        <option value="Adjunct Lecturer" <?= ($position == "Adjunct Lecturer") ? "selected" : "" ?>>Adjunct Lecturer</option>
                                        <option value="Staff Lecturer" <?= ($position == "Staff Lecturer") ? "selected" : "" ?>>Staff Lecturer</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div style="margin-top: 1.5rem;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Update Lecturer
                                </button>
                                <a href="admin_dashboard.php#lecturers" class="btn btn-secondary" style="margin-left: 0.5rem;">
                                    <i class="fas fa-times"></i>
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="data-table-container">
                    <div class="no-lecturer">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>No lecturer found with the provided ID.</p>
                        <a href="admin_dashboard.php#lecturers" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Lecturers
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
       <div class="container footer-content">
            <p>University of the Commonwealth Caribbean - Registry Department</p>
            <p>&copy; <?= date('Y') ?> UCC Registry System | Version 4.0</p>
            <p>Website designed by Ralston Campbell
            <img src="Ralston_logo_icon.jpg" 
                 alt="logo" 
                 style="width:25px; height:25px; margin-left:6px; vertical-align:middle; border-radius:50%;"></p>
            <div class="footer-links">
                <a href="admin_dashboard.php" class="footer-link">Back to Dashboard</a>
            </div>
        </div>
    </footer>
</body>
</html>