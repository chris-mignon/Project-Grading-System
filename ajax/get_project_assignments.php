<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

if (!isLoggedIn() || !(isAdmin() || isLecturer())) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit();
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false]);
    exit();
}

$db = (new Database())->getConnection();

// Authorization: lecturer can only view assignments for projects in courses they created
if (!isAdmin()) {
    $authStmt = $db->prepare(
        "SELECT p.assigned_to_all, c.created_by
         FROM projects p
         JOIN courses c ON c.id = p.course_id
         WHERE p.id = ?"
    );
    $authStmt->execute([$id]);
    $proj = $authStmt->fetch(PDO::FETCH_ASSOC);
    if (!$proj) {
        http_response_code(404);
        echo json_encode(['success' => false]);
        exit();
    }
    if ((int)$proj['created_by'] !== (int)$_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false]);
        exit();
    }
} else {
    $project = $db->prepare("SELECT assigned_to_all FROM projects WHERE id=?");
    $project->execute([$id]);
    $proj = $project->fetch(PDO::FETCH_ASSOC);
}

$assigned = [];
if (!$proj['assigned_to_all']) {
    $stmt = $db->prepare("SELECT lecturer_id FROM project_assignments WHERE project_id=?");
    $stmt->execute([$id]);
    $assigned = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'lecturer_id');
}

echo json_encode([
    'success' => true,
    'assigned_to_all' => (int)$proj['assigned_to_all'],
    'lecturers' => $assigned
]);
