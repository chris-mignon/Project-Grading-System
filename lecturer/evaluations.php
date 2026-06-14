<?php
require_once "../includes/auth.php";
redirectIfNotLecturer();

require_once "../config/database.php";
$database = new Database();
$db = $database->getConnection();

// Fetch courses for dropdown
$coursesStmt = $db->prepare("SELECT id, course_name FROM courses ORDER BY course_name ASC");
$coursesStmt->execute();
$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

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

// Main query with pagination
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

// Count total for pagination
$countQuery = "SELECT COUNT(*) as total FROM projects p $where_sql";
$countStmt = $db->prepare($countQuery);
$countParams = array_slice($params, 2);
$countStmt->execute($countParams);
$total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluate Projects - Project Evaluation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Evaluate Projects</h2>
                
                <!-- Filters -->
                <form id="filter-form" class="row mb-4">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="evaluated" <?php if ($status=="evaluated") echo "selected"; ?>>Evaluated</option>
                            <option value="pending" <?php if ($status=="pending") echo "selected"; ?>>Pending</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="course_id" class="form-select">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>" <?php if ($course_id == $course['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>

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
                                        <li class="list-group-item">
                                            <strong>Course:</strong> <?php echo htmlspecialchars($project['course_name']); ?>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>Student:</strong> <?php echo htmlspecialchars($project['student_name']); ?>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>Student ID:</strong> <?php echo htmlspecialchars($project['student_id']); ?>
                                        </li>
                                        <?php if ($project['evaluated']): ?>
                                            <li class="list-group-item">
                                                <strong>Your Score:</strong> <?php echo $project['my_score']; ?>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary w-100 evaluate-btn" 
                                            data-project-id="<?php echo $project['id']; ?>"
                                            data-project-name="<?php echo htmlspecialchars($project['project_name']); ?>">
                                        <?php if ($project['evaluated']): ?>
                                            <i class="bi bi-pencil-square"></i> Update Evaluation
                                        <?php else: ?>
                                            <i class="bi bi-check-circle"></i> Evaluate Project
                                        <?php endif; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($projects) === 0): ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-folder display-1 text-muted"></i>
                            <h4 class="mt-3">No Projects Available</h4>
                            <p class="text-muted">There are no projects to evaluate at this time.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <div id="pagination-container">
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="#" data-page="<?php echo $page-1; ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                    <a class="page-link" href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="#" data-page="<?php echo $page+1; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Evaluation Modal -->
    <div class="modal fade" id="evaluationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Evaluate Project: <span id="modal-project-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="evaluation-form">
                        <input type="hidden" id="project-id" name="project_id">
                        <div id="criteria-container">
                            <!-- Criteria will be loaded via AJAX -->
                        </div>
                        <div class="mb-3">
                            <label for="feedback" class="form-label">Overall Feedback</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="4"></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Total Score: <span id="total-score">0</span></strong>
                            </div>
                            <button type="submit" class="btn btn-success">Submit Evaluation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
    // AJAX filtering + pagination
    function loadProjects(page = 1) {
        const formData = $('#filter-form').serialize() + '&page=' + page;

        $.get('../ajax/get_projects_paginated.php', formData, function(response) {
            const html = $('<div>').html(response);
            $('#projects-container').replaceWith(html.find('#projects-container'));
            $('#pagination-container').replaceWith(html.find('#pagination-container'));
        });
    }

    // Submit filter form
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        loadProjects(1);
    });

    // Handle pagination clicks
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) loadProjects(page);
    });
    </script>
</body>
</html>
