<?php
require_once "includes/auth.php";
redirectIfNotLoggedIn();

// Redirect lecturers to lecturer dashboard
if (isLecturer()) {
    header("Location: lecturer/dashboard.php");
    exit();
}

// Rest of the admin dashboard code remains the same...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Project Evaluation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Admin Dashboard</h2>
                    <div class="text-muted">
                        Welcome, <?php echo $_SESSION['full_name']; ?>!
                    </div>
                </div>
                
                <!-- Admin Dashboard Cards -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-primary h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Users</h5>
                                        <p class="card-text">Manage user accounts</p>
                                    </div>
                                    <div class="display-4">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="admin/users.php" class="text-white">Manage Users <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-success h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Courses</h5>
                                        <p class="card-text">Manage courses</p>
                                    </div>
                                    <div class="display-4">
                                        <i class="bi bi-book"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="admin/courses.php" class="text-white">Manage Courses <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-info h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Projects</h5>
                                        <p class="card-text">Manage student projects</p>
                                    </div>
                                    <div class="display-4">
                                        <i class="bi bi-folder"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="admin/projects.php" class="text-white">Manage Projects <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-warning h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Rubric</h5>
                                        <p class="card-text">Set evaluation criteria</p>
                                    </div>
                                    <div class="display-4">
                                        <i class="bi bi-clipboard-check"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="admin/rubric.php" class="text-white">Manage Rubric <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <!-- Add this card to the admin dashboard row -->
<div class="col-md-3 mb-4">
    <div class="card text-white bg-info h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="card-title">Grades</h5>
                    <p class="card-text">View project evaluations</p>
                </div>
                <div class="display-4">
                    <i class="bi bi-graph-up"></i>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="admin/grades.php" class="text-white">View Grades <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "includes/footer.php"; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>