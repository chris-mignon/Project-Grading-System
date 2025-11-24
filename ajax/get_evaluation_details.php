<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

if (!isLoggedIn()) {
    http_response_code(403);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$database = new Database();
$db = $database->getConnection();

$project_id = $_POST['project_id'] ?? null;
$lecturer_id = $_SESSION['user_id'];

if (!$project_id) {
    exit('Invalid project ID');
}

// Get project details
$query = "SELECT p.*, c.course_name, c.course_code 
          FROM projects p 
          JOIN courses c ON p.course_id = c.id 
          WHERE p.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    exit('Project not found');
}

// Get current user's evaluation
$query = "SELECT e.* FROM evaluations e 
          WHERE e.project_id = ? AND e.lecturer_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$project_id, $lecturer_id]);
$my_evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

// Get all evaluations for this project
$query = "SELECT e.*, u.full_name as lecturer_name 
          FROM evaluations e 
          JOIN users u ON e.lecturer_id = u.id 
          WHERE e.project_id = ? 
          ORDER BY e.evaluated_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$project_id]);
$evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get criteria and scores
$query = "SELECT rc.*, es.score 
          FROM rubric_criteria rc 
          LEFT JOIN evaluation_scores es ON rc.id = es.criterion_id 
          LEFT JOIN evaluations e ON es.evaluation_id = e.id AND e.project_id = ? AND e.lecturer_id = ?
          WHERE rc.course_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$project_id, $lecturer_id, $project['course_id']]);
$criteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="row">
    <div class="col-md-6">
        <h6>Project Information</h6>
        <table class="table table-sm">
            <tr><th>Project:</th><td><?php echo htmlspecialchars($project['project_name']); ?></td></tr>
            <tr><th>Student:</th><td><?php echo htmlspecialchars($project['student_name']); ?></td></tr>
            <tr><th>Student ID:</th><td><?php echo htmlspecialchars($project['student_id']); ?></td></tr>
            <tr><th>Course:</th><td><?php echo htmlspecialchars($project['course_code'] . ' - ' . $project['course_name']); ?></td></tr>
            <tr><th>Description:</th><td><?php echo htmlspecialchars($project['description']); ?></td></tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6>Evaluation Summary</h6>
        <table class="table table-sm">
            <tr><th>Total Evaluators:</th><td><?php echo count($evaluations); ?></td></tr>
            <tr><th>Your Score:</th><td>
                <?php if ($my_evaluation): ?>
                    <span class="badge bg-primary"><?php echo $my_evaluation['total_score']; ?></span>
                <?php else: ?>
                    <span class="badge bg-secondary">Not evaluated</span>
                <?php endif; ?>
            </td></tr>
            <tr><th>Average Score:</th><td>
                <?php
                $total_score = 0;
                foreach ($evaluations as $eval) {
                    $total_score += $eval['total_score'];
                }
                $avg_score = count($evaluations) > 0 ? $total_score / count($evaluations) : 0;
                ?>
                <span class="badge bg-success"><?php echo round($avg_score, 2); ?></span>
            </td></tr>
        </table>
    </div>
</div>

<?php if ($my_evaluation): ?>
<div class="mt-4">
    <h6>Your Evaluation Details</h6>
    <div class="card">
        <div class="card-body">
            <h6>Criterion Scores:</h6>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Criterion</th>
                        <th>Score</th>
                        <th>Max</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($criteria as $criterion): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($criterion['criterion_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $criterion['score'] ? 'primary' : 'secondary'; ?>">
                                    <?php echo $criterion['score'] ? $criterion['score'] : '0'; ?>
                                </span>
                            </td>
                            <td><?php echo $criterion['max_score']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h6 class="mt-3">Your Feedback:</h6>
            <div class="border rounded p-3 bg-light">
                <?php echo nl2br(htmlspecialchars($my_evaluation['feedback'])); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (count($evaluations) > 0): ?>
<div class="mt-4">
    <h6>All Evaluations</h6>
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>Lecturer</th>
                    <th>Score</th>
                    <th>Evaluated At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evaluations as $eval): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($eval['lecturer_name']); ?></td>
                        <td><span class="badge bg-primary"><?php echo $eval['total_score']; ?></span></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($eval['evaluated_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>