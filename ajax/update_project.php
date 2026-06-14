<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$db = (new Database())->getConnection();

$id = (int)($_POST['id'] ?? 0);
$project_name = $_POST['project_name'] ?? '';
$description = $_POST['description'] ?? '';
$student_name = $_POST['student_name'] ?? '';
$student_id = $_POST['student_id'] ?? '';
$course_id = $_POST['course_id'] ?? '';
$assigned_to_all = isset($_POST['assigned_to_all']) ? 1 : 0;
$lecturers = $_POST['lecturers'] ?? [];

if (!$id || !$project_name || !$student_name || !$student_id || !$course_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE projects SET project_name=?, description=?, student_name=?, student_id=?, course_id=?, assigned_to_all=? WHERE id=?");
    $stmt->execute([$project_name, $description, $student_name, $student_id, $course_id, $assigned_to_all, $id]);

    // reset assignments
    $db->prepare("DELETE FROM project_assignments WHERE project_id=?")->execute([$id]);

    if (!$assigned_to_all && !empty($lecturers)) {
        $ins = $db->prepare("INSERT INTO project_assignments (project_id, lecturer_id) VALUES (?, ?)");
        foreach ($lecturers as $lid) {
            $ins->execute([$id, $lid]);
        }
    }

    $db->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
