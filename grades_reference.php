<!--
Name of Enterprise App: UCC Registrar
Developers: Ralston Campbell
Version: 4.0 
Version Date: Dec/2/2025
Purpose: A php function that defines gpa score to a greek tier.
-->

<?php
session_start();
include "db.php";

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
    <title>UCC Registry - Grading System</title>
    <link rel="stylesheet" href="modern-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .grade-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .grade-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .grade-card-header h2 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--gray-800);
        }
        
        .grade-card-body {
            padding: 1.5rem;
        }
        
        .grade-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: 600;
            font-size: 0.75rem;
        }
        
        .info-box {
            background-color: var(--gray-100);
            border-left: 4px solid var(--primary);
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-top: 1.5rem;
        }
        
        .info-box h3 {
            color: var(--primary);
            margin-top: 0;
            margin-bottom: 1rem;
            font-size: 1.125rem;
        }
        
        .info-box ul {
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .info-box li {
            margin-bottom: 0.5rem;
        }
        
        .nav-buttons {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
                <a href="student_dashboard.php" class="logo">
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin_dashboard.php" class="logo">
            <?php else: ?>
                <a href="login.php" class="logo">
            <?php endif; ?>
                <img src="ucc_logo.png" alt="UCC Logo" style="height: 40px; margin-right: 10px;">
                <span style="font-size: 20px; font-weight: 600; color: #2563eb; display: flex; align-items: center;">UCC Registry</span>
            </a>
            <div class="header-actions">
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="user-menu">
                        <div class="user-avatar">
                            <?= getInitial($_SESSION['user']) ?>
                        </div>
                        <span><?= htmlspecialchars($_SESSION['user']) ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
                    <a href="student_dashboard.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="dashboard">
        <div class="container">
            <div class="search-section mb-4">
                <div class="flex justify-between items-center">
                    <h2>
                        <i class="fas fa-graduation-cap"></i>
                        Grading System Reference
                    </h2>
                </div>
            </div>
            
            <div class="grade-card">
                <div class="grade-card-header">
                    <h2>UCC Grading Scale</h2>
                </div>
                <div class="grade-card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Grade</th>
                                    <th>Percentage Range</th>
                                    <th>Quality Points</th>
                                    <th>Academic Award</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-success">A</span></td>
                                    <td>90-100</td>
                                    <td>4.00</td>
                                    <td>Summa Cum Laude</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success">A-</span></td>
                                    <td>80-89</td>
                                    <td>3.67</td>
                                    <td>Summa Cum Laude</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success">B+</span></td>
                                    <td>75-79</td>
                                    <td>3.50</td>
                                    <td>Magna Cum Laude</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success">B</span></td>
                                    <td>65-74</td>
                                    <td>3.00</td>
                                    <td>Cum Laude</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-warning">B-</span></td>
                                    <td>60-64</td>
                                    <td>2.67</td>
                                    <td>Pass</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-warning">C+</span></td>
                                    <td>55-59</td>
                                    <td>2.33</td>
                                    <td>Credit</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-warning">C</span></td>
                                    <td>50-54</td>
                                    <td>2.00</td>
                                    <td>Pass</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-warning">D</span></td>
                                    <td>40-49</td>
                                    <td>1.67</td>
                                    <td>Pass</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-danger">F</span></td>
                                    <td>0-39</td>
                                    <td>0.00</td>
                                    <td>Fail</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="info-box">
                        <h3>Academic Standing</h3>
                        <ul>
                            <li><strong>Summa Cum Laude</strong> - GPA of 3.67 or higher</li>
                            <li><strong>Magna Cum Laude</strong> - GPA between 3.50 and 3.66</li>
                            <li><strong>Cum Laude</strong> - GPA between 3.00 and 3.49</li>
                            <li><strong>Good Standing</strong> - GPA between 2.00 and 2.99</li>
                            <li><strong>Academic Probation</strong> - GPA below 2.00</li>
                        </ul>
                        
                        <div class="search-section" style="margin-top: 1.5rem; background-color: rgba(23, 162, 184, 0.1); border-left: 3px solid var(--primary); padding: 1rem;">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-info-circle text-primary"></i>
                                <p><strong>Grading Policy:</strong> Coursework is worth 60% of the final grade, and the final exam or project is worth 40%.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-box" style="margin-top: 1.5rem;">
                        <h3>Quality Points Calculation</h3>
                        <p>Quality points are calculated by multiplying the grade point value by the number of credit hours for a course.</p>
                        <p><strong>Example:</strong> A 3-credit course with a grade of B+ (3.50 points) would earn 10.50 quality points (3 credits × 3.50 points).</p>
                        <p>The Grade Point Average (GPA) is calculated by dividing the total quality points by the total number of credit hours attempted.</p>
                    </div>
                </div>
            </div>
            
            <div class="nav-buttons">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
                    <a href="student_dashboard.php" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i>
                        Return to Dashboard
                    </a>
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i>
                        Return to Admin Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                <?php endif; ?>
            </div>
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
        </div>
    </footer>
</body>
</html>