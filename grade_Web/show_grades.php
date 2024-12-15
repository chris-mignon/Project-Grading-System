<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$project_id = $_GET['project_id'] ?? null;
if ($project_id === null) {
    die('Project ID is required.');
}

// Fetch project details
$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id');
$stmt->execute(['id' => $project_id]);
$project = $stmt->fetch();

// Fetch grades for the project
$stmt = $pdo->prepare('
    SELECT u.name AS lecturer_name, g.score
    FROM grades g
    JOIN users u ON g.user_id = u.id
    WHERE g.project_id = :project_id
');
$stmt->execute(['project_id' => $project_id]);
$grades = $stmt->fetchAll();

// Calculate the average grade
$average_score = 0;
if (count($grades) > 0) {
    foreach ($grades as $grade) {
        $average_score += $grade['score'];
    }
    $average_score /= count($grades);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades for <?php echo htmlspecialchars($project['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Grades for <?php echo htmlspecialchars($project['title']); ?></h1>
        <h5 class="text-center">Average Grade: <?php echo number_format($average_score, 2); ?></h5>

        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Lecturer Name</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($grades) > 0): ?>
                    <?php foreach ($grades as $grade): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($grade['lecturer_name']); ?></td>
                            <td><?php echo number_format($grade['score'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="text-center">No grades available for this project.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="admin.php" class="btn btn-secondary">Back to Admin Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>