<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

if (!isLoggedIn()) {
    http_response_code(403);
    exit('Unauthorized');
}

$database = new Database();
$db = $database->getConnection();

$course_id = $_POST['course_id'] ?? null;

$query = "SELECT p.*, c.course_name, c.course_code 
          FROM projects p 
          JOIN courses c ON p.course_id = c.id";
          
if ($course_id) {
    $query .= " WHERE p.course_id = ?";
}
$query .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
if ($course_id) {
    $stmt->execute([$course_id]);
} else {
    $stmt->execute();
}
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $projects]);
?>