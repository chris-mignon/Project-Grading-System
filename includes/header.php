<?php
require_once "auth.php";
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="http://projectevaluationsystem-env.eba-8seiyy78.us-east-2.elasticbeanstalk.com/dashboard.php">
            <i class="bi bi-clipboard-check"></i> Project Evaluation System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="http://projectevaluationsystem-env.eba-8seiyy78.us-east-2.elasticbeanstalk.com/dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <?php if (isAdmin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="http://projectevaluationsystem-env.eba-8seiyy78.us-east-2.elasticbeanstalk.com/admin/users.php">Users</a></li>
                            <li><a class="dropdown-item" href="http://projectevaluationsystem-env.eba-8seiyy78.us-east-2.elasticbeanstalk.com/admin/courses.php">Courses</a></li>
                            <li><a class="dropdown-item" href="http://projectevaluationsystem-env.eba-8seiyy78.us-east-2.elasticbeanstalk.com/admin/projects.php">Projects</a></li>
                            <li><a class="dropdown-item" href="http://projectevaluationsystem-env.eba-8seiyy78.us-east-2.elasticbeanstalk.com/admin/rubric.php">Rubric</a></li>
                            <li><a class="dropdown-item" href="http://projectevaluationsystem-env.eba-8seiyy78.us-east-2.elasticbeanstalk.com/admin/grades.php">View Grades</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../lecturer/evaluations.php">
                            <i class="bi bi-pencil-square"></i> Evaluate
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../lecturer/results.php">
                            <i class="bi bi-bar-chart"></i> Results
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo $_SESSION['full_name']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">Role: <?php echo ucfirst($_SESSION['role']); ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>