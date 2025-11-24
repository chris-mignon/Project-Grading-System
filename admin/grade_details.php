<?php
require_once "../includes/auth.php";
redirectIfNotAdmin();

require_once "../config/database.php";
$database = new Database();
$db = $database->getConnection();

$project_id = $_GET['project_id'] ?? null;

if (!$project_id) {
    header("Location: grades.php");
    exit();
}

// Fetch project details
$query = "SELECT p.*, c.course_code, c.course_name 
          FROM projects p 
          JOIN courses c ON p.course_id = c.id 
          WHERE p.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header("Location: grades.php");
    exit();
}

// Fetch detailed evaluations with criterion scores
$query = "SELECT 
            e.id as evaluation_id,
            u.full_name as lecturer_name,
            e.total_score,
            e.feedback,
            e.evaluated_at,
            rc.criterion_name,
            es.score as criterion_score,
            rc.max_score as criterion_max
          FROM evaluations e
          JOIN users u ON e.lecturer_id = u.id
          LEFT JOIN evaluation_scores es ON e.id = es.evaluation_id
          LEFT JOIN rubric_criteria rc ON es.criterion_id = rc.id
          WHERE e.project_id = ?
          ORDER BY u.full_name, rc.id";

$stmt = $db->prepare($query);
$stmt->execute([$project_id]);
$evaluations_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group evaluations by lecturer
$grouped_evaluations = [];
$criteria_list = [];

foreach ($evaluations_data as $eval) {
    $lecturer_name = $eval['lecturer_name'];
    
    if (!isset($grouped_evaluations[$lecturer_name])) {
        $grouped_evaluations[$lecturer_name] = [
            'total_score' => $eval['total_score'],
            'feedback' => $eval['feedback'],
            'evaluated_at' => $eval['evaluated_at'],
            'criteria_scores' => []
        ];
    }
    
    if ($eval['criterion_name']) {
        $grouped_evaluations[$lecturer_name]['criteria_scores'][$eval['criterion_name']] = [
            'score' => $eval['criterion_score'],
            'max' => $eval['criterion_max']
        ];
        
        // Build unique criteria list
        if (!in_array($eval['criterion_name'], $criteria_list)) {
            $criteria_list[] = $eval['criterion_name'];
        }
    }
}

// Calculate statistics
$total_evaluations = count($grouped_evaluations);
$average_score = 0;

if ($total_evaluations > 0) {
    $total_score = 0;
    foreach ($grouped_evaluations as $eval) {
        $total_score += $eval['total_score'];
    }
    $average_score = $total_score / $total_evaluations;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Details - Project Evaluation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="grades.php">Grades</a></li>
                        <li class="breadcrumb-item active">Project Details</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Project Evaluation Details</h2>
                    <a href="grades.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Grades
                    </a>
                </div>

                <!-- Project Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Project Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Project Name:</th>
                                        <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Student:</th>
                                        <td><?php echo htmlspecialchars($project['student_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Student ID:</th>
                                        <td><?php echo htmlspecialchars($project['student_id']); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Course:</th>
                                        <td><?php echo htmlspecialchars($project['course_code'] . ' - ' . $project['course_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Evaluations:</th>
                                        <td><span class="badge bg-info"><?php echo $total_evaluations; ?></span></td>
                                    </tr>
                                    <tr>
                                        <th>Average Score:</th>
                                        <td><span class="badge bg-success"><?php echo round($average_score, 2); ?></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <?php if ($project['description']): ?>
                            <div class="mt-3">
                                <strong>Description:</strong>
                                <p class="mt-1"><?php echo htmlspecialchars($project['description']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Detailed Evaluations -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Detailed Evaluations</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($grouped_evaluations) > 0): ?>
                            <?php foreach ($grouped_evaluations as $lecturer_name => $evaluation): ?>
                                <div class="evaluation-detail mb-4 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($lecturer_name); ?></h5>
                                        <div>
                                            <span class="badge bg-primary fs-6">Score: <?php echo $evaluation['total_score']; ?></span>
                                            <small class="text-muted ms-2">
                                                <?php echo date('M j, Y g:i A', strtotime($evaluation['evaluated_at'])); ?>
                                            </small>
                                        </div>
                                    </div>

                                    <?php if (count($evaluation['criteria_scores']) > 0): ?>
                                        <h6>Criteria Breakdown:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Criterion</th>
                                                        <th>Score</th>
                                                        <th>Max</th>
                                                        <th>Percentage</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($evaluation['criteria_scores'] as $criterion_name => $score_data): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($criterion_name); ?></td>
                                                            <td><?php echo $score_data['score']; ?></td>
                                                            <td><?php echo $score_data['max']; ?></td>
                                                            <td>
                                                                <?php 
                                                                $percentage = $score_data['max'] > 0 ? 
                                                                    ($score_data['score'] / $score_data['max']) * 100 : 0;
                                                                echo round($percentage, 1) . '%';
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($evaluation['feedback']): ?>
                                        <div class="mt-3">
                                            <h6>Feedback:</h6>
                                            <div class="border rounded p-3 bg-light">
                                                <?php echo nl2br(htmlspecialchars($evaluation['feedback'])); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-warning text-center">
                                <i class="bi bi-exclamation-triangle"></i>
                                No evaluations found for this project.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>