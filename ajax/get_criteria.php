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

if (!$project_id) {
    exit('Invalid project ID');
}

// Get course_id from project and project details
$query = "SELECT p.*, c.course_name, c.course_code FROM projects p 
          JOIN courses c ON p.course_id = c.id 
          WHERE p.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    exit('Project not found');
}

$course_id = $project['course_id'];

// Set project description for modal
echo '<script>';
echo '$("#modal-project-description").text("' . addslashes($project['description']) . '");';
echo '</script>';

// Get criteria for this course
$query = "SELECT * FROM rubric_criteria WHERE course_id = ? ORDER BY id";
$stmt = $db->prepare($query);
$stmt->execute([$course_id]);
$criteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($criteria) === 0) {
    echo '<div class="alert alert-warning">';
    echo '<i class="bi bi-exclamation-triangle"></i> ';
    echo 'No evaluation criteria found for this course. Please contact the administrator.';
    echo '</div>';
    exit();
}

echo '<div class="criteria-list">';
foreach ($criteria as $criterion) {
    echo '
    <div class="criteria-item mb-3 p-3 border rounded">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="mb-1 fw-bold">' . htmlspecialchars($criterion['criterion_name']) . '</h6>
                <small class="text-muted">Maximum score: ' . $criterion['max_score'] . '</small>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="number" 
                           class="form-control score-input text-center" 
                           id="score-' . $criterion['id'] . '" 
                           data-criterion-id="' . $criterion['id'] . '" 
                           min="0" 
                           max="' . $criterion['max_score'] . '" 
                           step="0.5"
                           placeholder="0"
                           value="0">
                    <span class="input-group-text">/ ' . $criterion['max_score'] . '</span>
                </div>
            </div>
        </div>
    </div>';
}
echo '</div>';
?>