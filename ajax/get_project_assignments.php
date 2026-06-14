<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

if (!isLoggedIn() || !isAdmin()) {
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

$project = $db->prepare("SELECT assigned_to_all FROM projects WHERE id=?");
$project->execute([$id]);
$proj = $project->fetch(PDO::FETCH_ASSOC);

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
