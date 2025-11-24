<?php
require_once "../includes/auth.php";
redirectIfNotAdmin();

require_once "../config/database.php";
$database = new Database();
$db = $database->getConnection();

// Get course_id from URL if provided
$course_id = $_GET['course_id'] ?? null;

// Handle form submission
if ($_POST && isset($_POST['project_name'])) {
    $project_name = $_POST['project_name'];
    $description = $_POST['description'];
    $student_name = $_POST['student_name'];
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    
    $query = "INSERT INTO projects (project_name, description, student_name, student_id, course_id) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$project_name, $description, $student_name, $student_id, $course_id]);
    
    header("Location: projects.php?success=1&course_id=" . $course_id);
    exit();
}

// Fetch courses for dropdown
$courses_query = "SELECT * FROM courses ORDER BY course_name";
$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch projects
$projects_query = "SELECT p.*, c.course_name, c.course_code 
                  FROM projects p 
                  JOIN courses c ON p.course_id = c.id";
if ($course_id) {
    $projects_query .= " WHERE p.course_id = ?";
}
$projects_query .= " ORDER BY p.created_at DESC";

$projects_stmt = $db->prepare($projects_query);
if ($course_id) {
    $projects_stmt->execute([$course_id]);
} else {
    $projects_stmt->execute();
}
$projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects - Project Evaluation System</title>
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
                    <h2>Manage Projects</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                        <i class="bi bi-plus-circle"></i> Add Project
                    </button>
                </div>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Project added successfully!
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
                                    <a href="projects.php" class="btn btn-outline-secondary">Clear Filter</a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Projects</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($projects) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Project Name</th>
                                            <th>Student</th>
                                            <th>Student ID</th>
                                            <th>Course</th>
                                            <th>Description</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($projects as $project): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                                                <td><?php echo htmlspecialchars($project['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($project['student_id']); ?></td>
                                                <td><?php echo htmlspecialchars($project['course_code']); ?></td>
                                                <td><?php echo htmlspecialchars($project['description']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($project['created_at'])); ?></td>
                                                <td>
                                                    <a href="rubric.php?course_id=<?php echo $project['course_id']; ?>" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="bi bi-clipboard-check"></i> Rubric
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-folder display-1 text-muted"></i>
                                <h4 class="mt-3">No Projects Found</h4>
                                <p class="text-muted">
                                    <?php echo $course_id ? 'No projects found for this course.' : 'Get started by adding your first project.'; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Project Modal -->
    <div class="modal fade" id="addProjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Project</h5>
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
                            <label for="project_name" class="form-label">Project Name</label>
                            <input type="text" class="form-control" id="project_name" name="project_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="student_name" class="form-label">Student Name</label>
                            <input type="text" class="form-control" id="student_name" name="student_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="student_id" class="form-label">Student ID</label>
                            <input type="text" class="form-control" id="student_id" name="student_id" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>