<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

if (!isLoggedIn() || !(isAdmin() || isLecturer())) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// CSRF check
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$project_name = $_POST['project_name'] ?? '';
$description = $_POST['description'] ?? '';
$student_name = $_POST['student_name'] ?? '';
$student_id = $_POST['student_id'] ?? '';
$course_id = $_POST['course_id'] ?? '';
$assigned_to_all = isset($_POST['assigned_to_all']) ? 1 : 0;
$lecturers = $_POST['lecturers'] ?? [];

if (empty($project_name) || empty($student_name) || empty($student_id) || empty($course_id)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

try {
    $db->beginTransaction();

    $query = "INSERT INTO projects (project_name, description, student_name, student_id, course_id, assigned_to_all) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$project_name, $description, $student_name, $student_id, $course_id, $assigned_to_all]);

    $project_id = $db->lastInsertId();

    // Assign specific lecturers if not assigned to all
    if (!$assigned_to_all && !empty($lecturers)) {
        $assignStmt = $db->prepare("INSERT INTO project_assignments (project_id, lecturer_id) VALUES (?, ?)");
        foreach ($lecturers as $lecturer_id) {
            $assignStmt->execute([$project_id, $lecturer_id]);
        }
    }

    $db->commit();
    
    echo json_encode(['success' => true, 'message' => 'Project added successfully']);
} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
