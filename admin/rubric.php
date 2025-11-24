<?php
require_once "../includes/auth.php";
redirectIfNotAdmin();

require_once "../config/database.php";
$database = new Database();
$db = $database->getConnection();

// Get course_id from URL
$course_id = $_GET['course_id'] ?? null;

// Handle form submission
if ($_POST && isset($_POST['criterion_name'])) {
    $criterion_name = $_POST['criterion_name'];
    $max_score = $_POST['max_score'];
    $course_id = $_POST['course_id'];
    
    $query = "INSERT INTO rubric_criteria (criterion_name, max_score, course_id) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$criterion_name, $max_score, $course_id]);
    
    header("Location: rubric.php?success=1&course_id=" . $course_id);
    exit();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $query = "DELETE FROM rubric_criteria WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$delete_id]);
    
    header("Location: rubric.php?success=2&course_id=" . $course_id);
    exit();
}

// Fetch courses for dropdown
$courses_query = "SELECT * FROM courses ORDER BY course_name";
$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch rubric criteria
$criteria_query = "SELECT rc.*, c.course_name, c.course_code 
                  FROM rubric_criteria rc 
                  JOIN courses c ON rc.course_id = c.id";
if ($course_id) {
    $criteria_query .= " WHERE rc.course_id = ?";
}
$criteria_query .= " ORDER BY rc.id";

$criteria_stmt = $db->prepare($criteria_query);
if ($course_id) {
    $criteria_stmt->execute([$course_id]);
} else {
    $criteria_stmt->execute();
}
$criteria = $criteria_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected course name
$selected_course_name = "All Courses";
if ($course_id) {
    foreach ($courses as $course) {
        if ($course['id'] == $course_id) {
            $selected_course_name = $course['course_code'] . ' - ' . $course['course_name'];
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rubric - Project Evaluation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Rubric Criteria</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCriterionModal">
                        <i class="bi bi-plus-circle"></i> Add Criterion
                    </button>
                </div>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-<?php echo $_GET['success'] == 1 ? 'success' : 'warning'; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_GET['success'] == 1 ? 'Criterion added successfully!' : 'Criterion deleted successfully!'; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Course Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label for="course_filter" class="form-label">Filter by Course</label>
                                <select class="form-select" id="course_filter" name="course_id" onchange="this.form.submit()">
                                    <option value="">All Courses</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['id']; ?>" 
                                                <?php echo ($course_id == $course['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($course_id): ?>
                                <div class="col-md-6 d-flex align-items-end">
                                    <a href="rubric.php" class="btn btn-outline-secondary">Clear Filter</a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Rubric Criteria for: <?php echo htmlspecialchars($selected_course_name); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($criteria) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Criterion Name</th>
                                            <th>Max Score</th>
                                            <th>Course</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($criteria as $criterion): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($criterion['criterion_name']); ?></td>
                                                <td><?php echo $criterion['max_score']; ?></td>
                                                <td><?php echo htmlspecialchars($criterion['course_code']); ?></td>
                                                <td>
                                                    <a href="rubric.php?delete_id=<?php echo $criterion['id']; ?>&course_id=<?php echo $course_id; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Are you sure you want to delete this criterion?')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Summary Statistics -->
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h5>Total Criteria</h5>
                                            <h3><?php echo count($criteria); ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h5>Max Total Score</h5>
                                            <h3>
                                                <?php 
                                                $total_max = 0;
                                                foreach ($criteria as $criterion) {
                                                    $total_max += $criterion['max_score'];
                                                }
                                                echo $total_max;
                                                ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h5>Average Max Score</h5>
                                            <h3>
                                                <?php 
                                                echo count($criteria) > 0 ? round($total_max / count($criteria), 2) : 0;
                                                ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-clipboard-check display-1 text-muted"></i>
                                <h4 class="mt-3">No Rubric Criteria Found</h4>
                                <p class="text-muted">
                                    <?php echo $course_id ? 'No criteria found for this course.' : 'Get started by adding your first criterion.'; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Criterion Modal -->
    <div class="modal fade" id="addCriterionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Criterion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="course_id" class="form-label">Course</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>" 
                                            <?php echo ($course_id == $course['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="criterion_name" class="form-label">Criterion Name</label>
                            <input type="text" class="form-control" id="criterion_name" name="criterion_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="max_score" class="form-label">Maximum Score</label>
                            <input type="number" class="form-control" id="max_score" name="max_score" min="1" max="100" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Criterion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>