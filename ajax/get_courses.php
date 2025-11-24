<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    exit('Unauthorized');
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT c.*, u.full_name as created_by_name FROM courses c 
          LEFT JOIN users u ON c.created_by = u.id 
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($courses) > 0) {
    echo '
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Description</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';
    foreach ($courses as $course) {
        echo '
                <tr>
                    <td>' . htmlspecialchars($course['course_code']) . '</td>
                    <td>' . htmlspecialchars($course['course_name']) . '</td>
                    <td>' . htmlspecialchars($course['description']) . '</td>
                    <td>' . htmlspecialchars($course['created_by_name']) . '</td>
                    <td>
                        <a href="projects.php?course_id=' . $course['id'] . '" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-folder"></i> Projects
                        </a>
                        <a href="rubric.php?course_id=' . $course['id'] . '" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-clipboard-check"></i> Rubric
                        </a>
                    </td>
                </tr>';
    }
    echo '
            </tbody>
        </table>
    </div>';
} else {
    echo '
    <div class="text-center py-4">
        <i class="bi bi-book display-1 text-muted"></i>
        <h4 class="mt-3">No Courses Found</h4>
        <p class="text-muted">Get started by adding your first course.</p>
    </div>';
}
?>