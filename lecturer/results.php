<?php
require_once "../includes/auth.php";
redirectIfNotLecturer();

require_once "../config/database.php";
$database = new Database();
$db = $database->getConnection();

// Fetch evaluation results
$query = "SELECT p.*, c.course_name, c.course_code, 
                 e.total_score as my_score, e.feedback as my_feedback,
                 (SELECT AVG(total_score) FROM evaluations WHERE project_id = p.id) as avg_score,
                 (SELECT COUNT(*) FROM evaluations WHERE project_id = p.id) as evaluator_count
          FROM projects p 
          JOIN courses c ON p.course_id = c.id 
          LEFT JOIN evaluations e ON p.id = e.project_id AND e.lecturer_id = ?
          ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Results - Project Evaluation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Evaluation Results</h2>
                
                <!-- Summary Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Total Projects</h5>
                                <h3><?php echo count($projects); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Evaluated</h5>
                                <h3>
                                    <?php
                                    $evaluated_count = 0;
                                    foreach ($projects as $project) {
                                        if ($project['my_score'] !== null) {
                                            $evaluated_count++;
                                        }
                                    }
                                    echo $evaluated_count;
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>Pending</h5>
                                <h3><?php echo count($projects) - $evaluated_count; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5>Avg Evaluators</h5>
                                <h3>
                                    <?php
                                    $total_evaluators = 0;
                                    foreach ($projects as $project) {
                                        $total_evaluators += $project['evaluator_count'];
                                    }
                                    echo count($projects) > 0 ? round($total_evaluators / count($projects), 1) : 0;
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Projects Evaluation Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($projects) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover results-table">
                                    <thead>
                                        <tr>
                                            <th>Project Name</th>
                                            <th>Student</th>
                                            <th>Course</th>
                                            <th>Your Score</th>
                                            <th>Average Score</th>
                                            <th>Evaluators</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($projects as $project): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                                                <td><?php echo htmlspecialchars($project['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($project['course_code']); ?></td>
                                                <td>
                                                    <?php if ($project['my_score'] !== null): ?>
                                                        <span class="badge bg-primary"><?php echo $project['my_score']; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Not evaluated</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($project['avg_score'] !== null): ?>
                                                        <span class="badge bg-success"><?php echo round($project['avg_score'], 2); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">No evaluations</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $project['evaluator_count']; ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($project['my_score'] !== null): ?>
                                                        <span class="badge bg-success">Evaluated</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary view-details-btn"
                                                            data-project-id="<?php echo $project['id']; ?>"
                                                            data-project-name="<?php echo htmlspecialchars($project['project_name']); ?>">
                                                        <i class="bi bi-eye"></i> Details
                                                    </button>
                                                    <?php if ($project['my_score'] !== null): ?>
                                                        <button class="btn btn-sm btn-outline-warning update-eval-btn"
                                                                data-project-id="<?php echo $project['id']; ?>"
                                                                data-project-name="<?php echo htmlspecialchars($project['project_name']); ?>">
                                                            <i class="bi bi-pencil"></i> Update
                                                        </button>
                                                    <?php else: ?>
                                                        <a href="evaluations.php" class="btn btn-sm btn-outline-success">
                                                            <i class="bi bi-check-circle"></i> Evaluate
                                                        </a>
                                                    <?php endif; ?>
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
                                <p class="text-muted">There are no projects available for evaluation.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Evaluation Details: <span id="modal-project-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="details-content">
                        <!-- Details will be loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // View details button
        $('.view-details-btn').click(function() {
            const projectId = $(this).data('project-id');
            const projectName = $(this).data('project-name');
            
            $('#modal-project-name').text(projectName);
            
            // Load details via AJAX
            $.ajax({
                url: '../ajax/get_evaluation_details.php',
                method: 'POST',
                data: { project_id: projectId },
                success: function(response) {
                    $('#details-content').html(response);
                },
                error: function() {
                    $('#details-content').html('<div class="alert alert-danger">Error loading details</div>');
                }
            });
            
            $('#detailsModal').modal('show');
        });
        
        // Update evaluation button
        $('.update-eval-btn').click(function() {
            const projectId = $(this).data('project-id');
            // Redirect to evaluations page and trigger evaluation modal
            window.location.href = 'evaluations.php?evaluate=' + projectId;
        });
    });
    </script>
</body>
</html>