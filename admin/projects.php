<?php
require_once "../includes/auth.php";
redirectIfNotAdminOrLecturer();

require_once "../config/database.php";
$database = new Database();
$db = $database->getConnection();

// Get course_id from URL if provided
$course_id = $_GET['course_id'] ?? null;

// Handle form submission
if ($_POST && isset($_POST['project_name'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        header("Location: projects.php?success=0");
        exit();
    }

    $project_name = trim($_POST['project_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $student_name = trim($_POST['student_name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $course_id = (int)($_POST['course_id'] ?? 0);
    $assigned_to_all = isset($_POST['assigned_to_all']) ? 1 : 0;
    $lecturers = $_POST['lecturers'] ?? [];

    if ($project_name === '' || $student_name === '' || $student_id === '' || !$course_id) {
        header("Location: projects.php?success=0&course_id=" . $course_id);
        exit();
    }

    try {
        $db->beginTransaction();
        $query = "INSERT INTO projects (project_name, description, student_name, student_id, course_id, assigned_to_all) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$project_name, $description, $student_name, $student_id, $course_id, $assigned_to_all]);
        $project_id = $db->lastInsertId();

        // Assign lecturers if not assigned to all
        if ((int)$assigned_to_all === 0 && !empty($lecturers)) {
            $assignStmt = $db->prepare("INSERT INTO project_assignments (project_id, lecturer_id) VALUES (?, ?)");
            foreach ($lecturers as $lecturer_id) {
                $lecturer_id = (int)$lecturer_id;
                if ($lecturer_id > 0) {
                    $assignStmt->execute([$project_id, $lecturer_id]);
                }
            }
        }

        $db->commit();
        header("Location: projects.php?success=1&course_id=" . $course_id);
        exit();
    } catch (PDOException $e) {
        if ($db->inTransaction()) $db->rollBack();
        header("Location: projects.php?success=0&course_id=" . $course_id);
        exit();
    }
}

// Fetch courses for dropdown
$courses_query = "SELECT * FROM courses ORDER BY course_name";
$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch lecturers for assignment
$lecturers_stmt = $db->prepare("SELECT id, username AS name FROM users WHERE role = 'lecturer' ORDER BY username ASC");
$lecturers_stmt->execute();
$lecturers = $lecturers_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch projects
$projects_query = "SELECT p.*, c.course_name, c.course_code,
                  CASE WHEN p.assigned_to_all = 1 THEN 'All Lecturers'
                       ELSE GROUP_CONCAT(u.username SEPARATOR ', ')
                  END AS assigned_lecturers,
                  COUNT(DISTINCT pa.lecturer_id) AS total_assigned,
                  COUNT(DISTINCT e.lecturer_id) AS completed
                  FROM projects p 
                  JOIN courses c ON p.course_id = c.id
                  LEFT JOIN project_assignments pa ON pa.project_id = p.id
                  LEFT JOIN users u ON u.id = pa.lecturer_id
                  LEFT JOIN evaluations e ON e.project_id = p.id";
if ($course_id) {
    $projects_query .= " WHERE p.course_id = ?";
}
$projects_query .= " GROUP BY p.id ORDER BY p.created_at DESC";

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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
                                            <th>Assigned To</th>
                                            <th>Progress</th>
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
                                                <td>
                                                    <?php
                                                        if ($project['assigned_to_all']) {
                                                            echo '<span class="badge bg-primary">All</span>';
                                                        } elseif (!empty($project['assigned_lecturers'])) {
                                                            echo htmlspecialchars($project['assigned_lecturers']);
                                                        } else {
                                                            echo '<span class="text-muted">None</span>';
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                        // total lecturers if assigned to all
                                                        if ($project['assigned_to_all']) {
                                                            $totalStmt = $db->query("SELECT COUNT(*) as cnt FROM users WHERE role='lecturer'");
                                                            $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
                                                        } else {
                                                            $total = $project['total_assigned'];
                                                        }

                                                        $done = $project['completed'] ?: 0;
                                                        $percent = $total ? ($done / $total) * 100 : 0;
                                                    ?>
                                                    <div><?php echo "$done / $total"; ?></div>
                                                    <div class="progress" style="height:6px;">
                                                        <div class="progress-bar bg-success" style="width: <?php echo $percent; ?>%"></div>
                                                    </div>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($project['created_at'])); ?></td>
                                                <td>
                                                    <a href="rubric.php?course_id=<?php echo $project['course_id']; ?>" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="bi bi-clipboard-check"></i> Rubric
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-primary edit-btn"
                                                        data-id="<?php echo $project['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($project['project_name']); ?>"
                                                        data-student="<?php echo htmlspecialchars($project['student_name']); ?>"
                                                        data-student-id="<?php echo htmlspecialchars($project['student_id']); ?>"
                                                        data-description="<?php echo htmlspecialchars($project['description']); ?>"
                                                        data-course="<?php echo $project['course_id']; ?>"
                                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                                        Edit
                                                    </button>
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

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit Project</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="edit-form">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">

                        <input class="form-control mb-2" name="project_name" id="edit-name" required>
                        <input class="form-control mb-2" name="student_name" id="edit-student" required>
                        <input class="form-control mb-2" name="student_id" id="edit-student-id" required>
                        <textarea class="form-control mb-2" name="description" id="edit-description"></textarea>

                        <select class="form-select mb-2" name="course_id" id="edit-course">
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="assigned_to_all" id="edit-assign-all" class="form-check-input">
                            <label class="form-check-label">Assign to all lecturers</label>
                        </div>

                        <select class="form-select" name="lecturers[]" id="edit-lecturers" multiple>
                            <?php foreach ($lecturers as $lec): ?>
                                <option value="<?php echo $lec['id']; ?>"><?php echo htmlspecialchars($lec['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </form>
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
                        <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
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

                        <!-- Assignment -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="assign_all" name="assigned_to_all">
                            <label class="form-check-label" for="assign_all">Assign to all lecturers</label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assign specific lecturers</label>
                            <select class="form-select" name="lecturers[]" id="lecturers_select" multiple>
                                <?php foreach ($lecturers as $lec): ?>
                                    <option value="<?php echo $lec['id']; ?>">
                                        <?php echo htmlspecialchars($lec['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple</small>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    // init select2
    $(document).ready(function() {
        $('#lecturers_select, #edit-lecturers').select2({
            placeholder: 'Select lecturers',
            width: '100%'
        });
    });
    // populate edit modal
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit-id').value = btn.dataset.id;
            document.getElementById('edit-name').value = btn.dataset.name;
            document.getElementById('edit-student').value = btn.dataset.student;
            document.getElementById('edit-student-id').value = btn.dataset.studentId;
            document.getElementById('edit-description').value = btn.dataset.description;
            document.getElementById('edit-course').value = btn.dataset.course;

            // reset selections
            const assignAll = document.getElementById('edit-assign-all');
            const lecturersSelect = document.getElementById('edit-lecturers');
            assignAll.checked = false;
            [...lecturersSelect.options].forEach(o => o.selected = false);

            // fetch current assignments
            fetch('../ajax/get_project_assignments.php?id=' + btn.dataset.id)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;

                    assignAll.checked = data.assigned_to_all == 1;
                    lecturersSelect.disabled = assignAll.checked;

                    if (!assignAll.checked) {
                        data.lecturers.forEach(id => {
                            const opt = lecturersSelect.querySelector('option[value="' + id + '"]');
                            if (opt) opt.selected = true;
                        });
                    }
                });
        });
    });

    // submit edit
    document.getElementById('edit-form').addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../ajax/update_project.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Error');
        });
    });

    // toggle disable in edit modal
    document.getElementById('edit-assign-all').addEventListener('change', function() {
        document.getElementById('edit-lecturers').disabled = this.checked;
    });
    </script>
    <script>
    // Disable lecturer select if assign all is checked
    document.getElementById('assign_all').addEventListener('change', function() {
        document.getElementById('lecturers_select').disabled = this.checked;
    });
    </script>
</body>
</html>
