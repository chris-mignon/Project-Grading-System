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

$database = new Database();
$db = $database->getConnection();

$project_name = $_POST['project_name'] ?? '';
$description = $_POST['description'] ?? '';
$student_name = $_POST['student_name'] ?? '';
$student_id = $_POST['student_id'] ?? '';
$course_id = $_POST['course_id'] ?? '';

if (empty($project_name) || empty($student_name) || empty($student_id) || empty($course_id)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

try {
    $query = "INSERT INTO projects (project_name, description, student_name, student_id, course_id) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$project_name, $description, $student_name, $student_id, $course_id]);
    
    echo json_encode(['success' => true, 'message' => 'Project added successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>