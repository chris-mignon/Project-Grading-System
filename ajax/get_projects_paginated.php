<?php
require_once "../includes/auth.php";
redirectIfNotLecturer();

require_once "../config/database.php";
$database = new Database();
$db = $database->getConnection();

// Pagination
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Filters
$course_id = $_GET['course_id'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where = [];
$params = [$_SESSION['user_id'], $_SESSION['user_id']];

if ($course_id !== '') {
    $where[] = "p.course_id = ?";
    $params[] = $course_id;
}

if ($search !== '') {
    $where[] = "(p.project_name LIKE ? OR p.student_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status === 'evaluated') {
    $where[] = "EXISTS (SELECT 1 FROM evaluations e WHERE e.project_id = p.id AND e.lecturer_id = ?)";
    $params[] = $_SESSION['user_id'];
} elseif ($status === 'pending') {
    $where[] = "NOT EXISTS (SELECT 1 FROM evaluations e WHERE e.project_id = p.id AND e.lecturer_id = ?)";
    $params[] = $_SESSION['user_id'];
}

$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Main query
$query = "SELECT p.*, c.course_name, 
          (SELECT COUNT(*) FROM evaluations e WHERE e.project_id = p.id AND e.lecturer_id = ?) as evaluated,
          (SELECT total_score FROM evaluations e WHERE e.project_id = p.id AND e.lecturer_id = ?) as my_score
          FROM projects p 
          JOIN courses c ON p.course_id = c.id 
          $where_sql
          ORDER BY p.created_at DESC
          LIMIT $limit OFFSET $offset";

$stmt = $db->prepare($query);
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count
$countQuery = "SELECT COUNT(*) as total FROM projects p $where_sql";
$countStmt = $db->prepare($countQuery);
$countParams = array_slice($params, 2);
$countStmt->execute($countParams);
$total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);

// Render partial HTML
?>

<div id="projects-container" class="row">
<?php foreach ($projects as $project): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 <?php echo $project['evaluated'] ? 'border-success' : ''; ?>">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo htmlspecialchars($project['project_name']); ?></h5>
                <?php if ($project['evaluated']): ?>
                    <span class="badge bg-success">Evaluated</span>
                <?php else: ?>
                    <span class="badge bg-warning">Pending</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="card-text"><?php echo htmlspecialchars($project['description']); ?></p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Course:</strong> <?php echo htmlspecialchars($project['course_name']); ?></li>
                    <li class="list-group-item"><strong>Student:</strong> <?php echo htmlspecialchars($project['student_name']); ?></li>
                    <li class="list-group-item"><strong>Student ID:</strong> <?php echo htmlspecialchars($project['student_id']); ?></li>
                    <?php if ($project['evaluated']): ?>
                        <li class="list-group-item"><strong>Your Score:</strong> <?php echo $project['my_score']; ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary w-100 evaluate-btn" data-project-id="<?php echo $project['id']; ?>">
                    <?php echo $project['evaluated'] ? 'Update Evaluation' : 'Evaluate Project'; ?>
                </button>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<div id="pagination-container">
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="#" data-page="<?php echo $page-1; ?>">Previous</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <li class="page-item"><a class="page-link" href="#" data-page="<?php echo $page+1; ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
