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
$course_id = (is_string($course_id) && ctype_digit($course_id)) ? (int)$course_id : '';

$status = $_GET['status'] ?? '';
$status = in_array($status, ['evaluated', 'pending'], true) ? $status : '';

$search = trim((string)($_GET['search'] ?? ''));
if (mb_strlen($search) > 80) {
    $search = mb_substr($search, 0, 80);
}

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

$where_sql = $where ? " AND " . implode(" AND ", $where) : "";

// Main query
$query = "SELECT p.id, p.project_name, p.description, p.student_name, p.student_id, p.created_at,
                 c.course_name,
                 e.id AS evaluation_id,
                 e.total_score AS my_score
          FROM projects p
          JOIN courses c ON p.course_id = c.id
          LEFT JOIN project_assignments pa ON pa.project_id = p.id
          LEFT JOIN evaluations e ON e.project_id = p.id AND e.lecturer_id = ?
          WHERE (p.assigned_to_all = 1 OR pa.lecturer_id = ?)
          $where_sql
          ORDER BY p.created_at DESC
          LIMIT $limit OFFSET $offset";

$lecturer_id = $_SESSION['user_id'];
$stmt = $db->prepare($query);
// first param for JOIN lecturer_id, then rest filters (skip the duplicated scope params)
$filterParams = array_slice($params, 2);
$execParams = [$lecturer_id, $lecturer_id];
$execParams = array_merge($execParams, $filterParams);
$stmt->execute($execParams);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count
$countQuery = "SELECT COUNT(DISTINCT p.id) as total
               FROM projects p
               LEFT JOIN project_assignments pa ON pa.project_id = p.id
               WHERE (p.assigned_to_all = 1 OR pa.lecturer_id = ?)" . $where_sql;
$countStmt = $db->prepare($countQuery);
$countStmt->execute(array_merge([$lecturer_id], $filterParams));
$total = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
$total_pages = ceil($total / $limit);
$from = $total === 0 ? 0 : $offset + 1;
$to = min($total, $offset + $limit);

// Render partial HTML
?>

<div id="projects-container" class="row">
<?php foreach ($projects as $project): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 <?php echo $project['evaluation_id'] ? 'border-success' : ''; ?>">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo htmlspecialchars($project['project_name']); ?></h5>
                <?php if ($project['evaluation_id']): ?>
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
                    <?php if ($project['evaluation_id']): ?>
                        <li class="list-group-item"><strong>Your Score:</strong> <?php echo $project['my_score']; ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary w-100 evaluate-btn" data-project-id="<?php echo $project['id']; ?>">
                    <?php echo $project['evaluation_id'] ? 'Update Evaluation' : 'Evaluate Project'; ?>
                </button>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<div id="pagination-container">
    <div class="text-center text-muted mb-2">
        <?php if ($total > 0): ?>
            Showing <?php echo $from; ?>–<?php echo $to; ?> of <?php echo $total; ?>
        <?php else: ?>
            No projects found
        <?php endif; ?>
    </div>
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
