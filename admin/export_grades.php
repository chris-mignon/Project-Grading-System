<?php
require_once "../includes/auth.php";
redirectIfNotAdmin();

require_once "../config/database.php";
$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$course_id = $_GET['course_id'] ?? null;
$project_id = $_GET['project_id'] ?? null;

// Fetch evaluation data (same query as grades.php)
$query = "SELECT 
            c.course_code,
            c.course_name,
            p.project_name,
            p.student_name,
            p.student_id,
            u.full_name as lecturer_name,
            e.total_score,
            e.feedback,
            e.evaluated_at,
            AVG(e.total_score) OVER(PARTITION BY p.id) as project_avg_score
          FROM projects p
          JOIN courses c ON p.course_id = c.id
          LEFT JOIN evaluations e ON p.id = e.project_id
          LEFT JOIN users u ON e.lecturer_id = u.id
          WHERE 1=1";

$params = [];
$types = "";

if ($course_id) {
    $query .= " AND c.id = ?";
    $params[] = $course_id;
    $types .= "i";
}

if ($project_id) {
    $query .= " AND p.id = ?";
    $params[] = $project_id;
    $types .= "i";
}

$query .= " ORDER BY c.course_name, p.project_name, e.evaluated_at DESC";

$stmt = $db->prepare($query);

if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}

$evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="project_grades_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Create Excel content
echo "<table border='1'>";
echo "<tr>";
echo "<th>Course Code</th>";
echo "<th>Course Name</th>";
echo "<th>Project Name</th>";
echo "<th>Student Name</th>";
echo "<th>Student ID</th>";
echo "<th>Lecturer</th>";
echo "<th>Score</th>";
echo "<th>Project Average</th>";
echo "<th>Feedback</th>";
echo "<th>Evaluation Date</th>";
echo "</tr>";

foreach ($evaluations as $eval) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($eval['course_code']) . "</td>";
    echo "<td>" . htmlspecialchars($eval['course_name']) . "</td>";
    echo "<td>" . htmlspecialchars($eval['project_name']) . "</td>";
    echo "<td>" . htmlspecialchars($eval['student_name']) . "</td>";
    echo "<td>" . htmlspecialchars($eval['student_id']) . "</td>";
    echo "<td>" . htmlspecialchars($eval['lecturer_name']) . "</td>";
    echo "<td>" . ($eval['total_score'] ? $eval['total_score'] : 'N/A') . "</td>";
    echo "<td>" . ($eval['project_avg_score'] ? round($eval['project_avg_score'], 2) : 'N/A') . "</td>";
    echo "<td>" . htmlspecialchars($eval['feedback']) . "</td>";
    echo "<td>" . ($eval['evaluated_at'] ? date('Y-m-d H:i', strtotime($eval['evaluated_at'])) : 'N/A') . "</td>";
    echo "</tr>";
}

echo "</table>";
exit;
?>