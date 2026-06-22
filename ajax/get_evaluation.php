<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

if (!isLoggedIn() || !isLecturer()) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$project_id = (int)($_POST['project_id'] ?? 0);
if ($project_id <= 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid project ID']);
    exit();
}

$db = (new Database())->getConnection();

// Authorization: lecturer can only access assigned projects (or assigned to all)
$authQuery = "SELECT assigned_to_all FROM projects WHERE id = ?";
$authStmt = $db->prepare($authQuery);
$authStmt->execute([$project_id]);
$proj = $authStmt->fetch(PDO::FETCH_ASSOC);

if (!$proj) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Project not found']);
    exit();
}

if ((int)($proj['assigned_to_all'] ?? 0) === 0) {
    $authStmt = $db->prepare(
        "SELECT 1 FROM project_assignments WHERE project_id = ? AND lecturer_id = ? LIMIT 1"
    );
    $authStmt->execute([$project_id, $_SESSION['user_id']]);
    if ($authStmt->rowCount() === 0) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized access to this project']);
        exit();
    }
}

// Fetch evaluation (if exists)
$evalStmt = $db->prepare(
    "SELECT id, feedback FROM evaluations WHERE project_id = ? AND lecturer_id = ?"
);
$evalStmt->execute([$project_id, $_SESSION['user_id']]);
$eval = $evalStmt->fetch(PDO::FETCH_ASSOC);

if (!$eval) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => null]);
    exit();
}

$scoresStmt = $db->prepare(
    "SELECT criterion_id, score FROM evaluation_scores WHERE evaluation_id = ?"
);
$scoresStmt->execute([$eval['id']]);
$rows = $scoresStmt->fetchAll(PDO::FETCH_ASSOC);
$scores = [];
foreach ($rows as $r) {
    $scores[(string)$r['criterion_id']] = (float)$r['score'];
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => [
        'feedback' => $eval['feedback'] ?? '',
        'scores' => $scores
    ]
]);
