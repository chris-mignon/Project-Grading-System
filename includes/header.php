<?php
require_once "auth.php";

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$isOneLevelDeep = preg_match('#/(admin|lecturer|ajax)/#', $scriptName) === 1;

$dashboardUrl = $isOneLevelDeep ? '../dashboard.php' : 'dashboard.php';
$adminPrefix = $isOneLevelDeep ? '../admin/' : 'admin/';
$lecturerPrefix = $isOneLevelDeep ? '../lecturer/' : 'lecturer/';
$logoutUrl = $isOneLevelDeep ? '../logout.php' : 'logout.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $dashboardUrl; ?>">
            <i class="bi bi-clipboard-check"></i> Project Evaluation System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                        <a class="nav-link" href="<?php echo $dashboardUrl; ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                </li>
                <?php if (isAdmin() || isLecturer()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Manage
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (isAdmin()): ?>
                                <li><a class="dropdown-item" href="<?php echo $adminPrefix; ?>users.php">Users</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?php echo $adminPrefix; ?>courses.php">Courses</a></li>
                            <li><a class="dropdown-item" href="<?php echo $adminPrefix; ?>projects.php">Projects</a></li>
                            <li><a class="dropdown-item" href="<?php echo $adminPrefix; ?>rubric.php">Rubric</a></li>
                            <?php if (isAdmin()): ?>
                                <li><a class="dropdown-item" href="<?php echo $adminPrefix; ?>grades.php">View Grades</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $lecturerPrefix; ?>evaluations.php">
                            <i class="bi bi-pencil-square"></i> Evaluate
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $lecturerPrefix; ?>results.php">
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
                        <li><a class="dropdown-item" href="<?php echo $logoutUrl; ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
