<?php
require_once "../includes/auth.php";
redirectIfNotAdmin();

require_once "../config/database.php";
$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$course_id = $_GET['course_id'] ?? null;
$project_id = $_GET['project_id'] ?? null;

// Fetch courses for filter dropdown
$courses_query = "SELECT * FROM courses ORDER BY course_name";
$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch projects for filter dropdown
$projects_query = "SELECT p.*, c.course_name, c.course_code 
                  FROM projects p 
                  JOIN courses c ON p.course_id = c.id";
if ($course_id) {
    $projects_query .= " WHERE p.course_id = ?";
}
$projects_query .= " ORDER BY p.project_name";

$projects_stmt = $db->prepare($projects_query);
if ($course_id) {
    $projects_stmt->execute([$course_id]);
} else {
    $projects_stmt->execute();
}
$projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch evaluation data with filters
$query = "SELECT 
            c.id as course_id,
            c.course_code,
            c.course_name,
            p.id as project_id,
            p.project_name,
            p.student_name,
            p.student_id,
            p.description as project_description,
            u.full_name as lecturer_name,
            e.total_score,
            e.feedback,
            e.evaluated_at,
            COUNT(e.id) OVER(PARTITION BY p.id) as evaluation_count,
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

// Group evaluations by course and project for display
$grouped_evaluations = [];
foreach ($evaluations as $eval) {
    $course_key = $eval['course_id'];
    $project_key = $eval['project_id'];
    
    if (!isset($grouped_evaluations[$course_key])) {
        $grouped_evaluations[$course_key] = [
            'course_code' => $eval['course_code'],
            'course_name' => $eval['course_name'],
            'projects' => []
        ];
    }
    
    if (!isset($grouped_evaluations[$course_key]['projects'][$project_key])) {
        $grouped_evaluations[$course_key]['projects'][$project_key] = [
            'project_name' => $eval['project_name'],
            'student_name' => $eval['student_name'],
            'student_id' => $eval['student_id'],
            'project_description' => $eval['project_description'],
            'evaluation_count' => $eval['evaluation_count'],
            'project_avg_score' => $eval['project_avg_score'],
            'evaluations' => []
        ];
    }
    
    if ($eval['lecturer_name']) {
        $grouped_evaluations[$course_key]['projects'][$project_key]['evaluations'][] = [
            'lecturer_name' => $eval['lecturer_name'],
            'total_score' => $eval['total_score'],
            'feedback' => $eval['feedback'],
            'evaluated_at' => $eval['evaluated_at']
        ];
    }
}

// Calculate overall statistics
$total_projects = 0;
$total_evaluations = 0;
$overall_avg_score = 0;
$courses_with_evaluations = 0;

foreach ($grouped_evaluations as $course) {
    foreach ($course['projects'] as $project) {
        $total_projects++;
        $total_evaluations += $project['evaluation_count'];
        if ($project['project_avg_score']) {
            $overall_avg_score += $project['project_avg_score'];
            $courses_with_evaluations++;
        }
    }
}

$overall_avg_score = $courses_with_evaluations > 0 ? $overall_avg_score / $courses_with_evaluations : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Grades - Project Evaluation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .evaluation-card {
            border-left: 4px solid #007bff;
        }
        .score-badge {
            font-size: 1.1em;
            padding: 0.5em 0.75em;
        }
        .course-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
        }
        .project-card {
            transition: transform 0.2s;
        }
        .project-card:hover {
            transform: translateY(-2px);
        }
        .feedback-text {
            max-height: 100px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Project Grades & Evaluations</h2>
                    <div>
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print Report
                        </button>
                        <a href="export_grades.php?<?php echo http_build_query($_GET); ?>" class="btn btn-success">
                            <i class="bi bi-download"></i> Export Excel
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Total Projects</h5>
                                <h3><?php echo $total_projects; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Total Evaluations</h5>
                                <h3><?php echo $total_evaluations; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5>Average Score</h5>
                                <h3><?php echo round($overall_avg_score, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>Courses</h5>
                                <h3><?php echo count($grouped_evaluations); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Filter Results</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label for="course_id" class="form-label">Course</label>
                                <select class="form-select" id="course_id" name="course_id" onchange="this.form.submit()">
                                    <option value="">All Courses</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['id']; ?>" 
                                                <?php echo ($course_id == $course['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="project_id" class="form-label">Project</label>
                                <select class="form-select" id="project_id" name="project_id" onchange="this.form.submit()">
                                    <option value="">All Projects</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?php echo $project['id']; ?>" 
                                                <?php echo ($project_id == $project['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($project['project_name'] . ' - ' . $project['student_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($course_id || $project_id): ?>
                                <div class="col-12">
                                    <a href="grades.php" class="btn btn-outline-secondary">Clear Filters</a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Grades Display -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Evaluation Results</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($grouped_evaluations) > 0): ?>
                            <?php foreach ($grouped_evaluations as $course_id => $course): ?>
                                <div class="course-section mb-5">
                                    <div class="course-header p-3 mb-3">
                                        <h4 class="mb-1">
                                            <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                        </h4>
                                        <small><?php echo count($course['projects']); ?> projects</small>
                                    </div>
                                    
                                    <?php foreach ($course['projects'] as $project_id => $project): ?>
                                        <div class="project-card card mb-4">
                                            <div class="card-header bg-light">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h5 class="mb-1"><?php echo htmlspecialchars($project['project_name']); ?></h5>
                                                        <p class="mb-0 text-muted">
                                                            Student: <strong><?php echo htmlspecialchars($project['student_name']); ?></strong> 
                                                            (ID: <?php echo htmlspecialchars($project['student_id']); ?>)
                                                        </p>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-primary score-badge">
                                                            Avg: <?php echo $project['project_avg_score'] ? round($project['project_avg_score'], 2) : 'N/A'; ?>
                                                        </span>
                                                        <span class="badge bg-secondary">
                                                            <?php echo $project['evaluation_count']; ?> evaluations
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text"><?php echo htmlspecialchars($project['project_description']); ?></p>
                                                
                                                <?php if (count($project['evaluations']) > 0): ?>
                                                    <h6 class="mt-4 mb-3">Individual Evaluations:</h6>
                                                    <div class="row">
                                                        <?php foreach ($project['evaluations'] as $evaluation): ?>
                                                            <div class="col-md-6 mb-3">
                                                                <div class="evaluation-card card h-100">
                                                                    <div class="card-body">
                                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                                            <h6 class="card-title mb-0">
                                                                                <?php echo htmlspecialchars($evaluation['lecturer_name']); ?>
                                                                            </h6>
                                                                            <span class="badge bg-success score-badge">
                                                                                <?php echo $evaluation['total_score']; ?>
                                                                            </span>
                                                                        </div>
                                                                        <p class="text-muted small mb-2">
                                                                            Evaluated: <?php echo date('M j, Y g:i A', strtotime($evaluation['evaluated_at'])); ?>
                                                                        </p>
                                                                        <?php if ($evaluation['feedback']): ?>
                                                                            <div class="feedback-text border rounded p-2 bg-light">
                                                                                <small><?php echo nl2br(htmlspecialchars($evaluation['feedback'])); ?></small>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <p class="text-muted small"><em>No feedback provided</em></p>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-warning text-center">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                        No evaluations submitted for this project yet.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-clipboard-check display-1 text-muted"></i>
                                <h4 class="mt-3">No Evaluation Data Found</h4>
                                <p class="text-muted">
                                    <?php echo ($course_id || $project_id) ? 
                                        'No evaluations match your filter criteria.' : 
                                        'No evaluations have been submitted yet.'; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update project dropdown when course changes
        document.getElementById('course_id').addEventListener('change', function() {
            const courseId = this.value;
            const projectSelect = document.getElementById('project_id');
            
            if (courseId) {
                // Enable project selection and potentially filter projects
                projectSelect.disabled = false;
            } else {
                // Reset project selection
                projectSelect.selectedIndex = 0;
            }
        });

        // Auto-submit form when filters change (for better UX)
        document.getElementById('course_id').addEventListener('change', function() {
            this.form.submit();
        });

        document.getElementById('project_id').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>