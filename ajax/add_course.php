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

$course_name = $_POST['course_name'] ?? '';
$course_code = $_POST['course_code'] ?? '';
$description = $_POST['description'] ?? '';

if (empty($course_name) || empty($course_code)) {
    echo json_encode(['success' => false, 'message' => 'Course name and code are required']);
    exit();
}

try {
    $query = "INSERT INTO courses (course_name, course_code, description, created_by) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$course_name, $course_code, $description, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Course added successfully']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'Course code already exists']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>