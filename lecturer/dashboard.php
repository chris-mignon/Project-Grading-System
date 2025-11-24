<?php
require_once "../includes/auth.php";
redirectIfNotLecturer();

require_once "../config/database.php";
$database = new Database();
$db = $database->getConnection();

// Fetch projects with evaluation status for current lecturer
$lecturer_id = $_SESSION['user_id'];
$query = "SELECT p.*, c.course_name, c.course_code, 
                 e.id as evaluation_id, e.total_score as my_score, e.feedback as my_feedback,
                 (SELECT COUNT(*) FROM evaluations WHERE project_id = p.id) as total_evaluations,
                 (SELECT AVG(total_score) FROM evaluations WHERE project_id = p.id) as avg_score
          FROM projects p 
          JOIN courses c ON p.course_id = c.id 
          LEFT JOIN evaluations e ON p.id = e.project_id AND e.lecturer_id = ?
          ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute([$lecturer_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count projects by evaluation status
$evaluated_count = 0;
$pending_count = 0;

foreach ($projects as $project) {
    if ($project['evaluation_id']) {
        $evaluated_count++;
    } else {
        $pending_count++;
    }
}
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
                
                <!-- Evaluation Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Total Projects</h5>
                                <h3><?php echo count($projects); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Evaluated</h5>
                                <h3><?php echo $evaluated_count; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>Pending</h5>
                                <h3><?php echo $pending_count; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Projects List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Available Projects for Evaluation</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($projects) > 0): ?>
                            <div class="row">
                                <?php foreach ($projects as $project): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100 <?php echo $project['evaluation_id'] ? 'border-success' : 'border-warning'; ?>">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($project['project_name']); ?></h6>
                                                <?php if ($project['evaluation_id']): ?>
                                                    <span class="badge bg-success">Evaluated</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text small"><?php echo htmlspecialchars($project['description']); ?></p>
                                                <div class="project-info">
                                                    <table class="table table-sm table-borderless mb-0">
                                                        <tr>
                                                            <td><strong>Course:</strong></td>
                                                            <td><?php echo htmlspecialchars($project['course_code']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Student:</strong></td>
                                                            <td><?php echo htmlspecialchars($project['student_name']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Student ID:</strong></td>
                                                            <td><?php echo htmlspecialchars($project['student_id']); ?></td>
                                                        </tr>
                                                        <?php if ($project['evaluation_id']): ?>
                                                            <tr>
                                                                <td><strong>Your Score:</strong></td>
                                                                <td><span class="badge bg-primary"><?php echo $project['my_score']; ?></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Avg Score:</strong></td>
                                                                <td>
                                                                    <?php if ($project['avg_score']): ?>
                                                                        <span class="badge bg-success"><?php echo round($project['avg_score'], 2); ?></span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-secondary">N/A</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Evaluators:</strong></td>
                                                                <td><span class="badge bg-info"><?php echo $project['total_evaluations']; ?></span></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-<?php echo $project['evaluation_id'] ? 'warning' : 'primary'; ?> w-100 evaluate-btn" 
                                                        data-project-id="<?php echo $project['id']; ?>"
                                                        data-project-name="<?php echo htmlspecialchars($project['project_name']); ?>"
                                                        data-student-name="<?php echo htmlspecialchars($project['student_name']); ?>"
                                                        data-course-name="<?php echo htmlspecialchars($project['course_code'] . ' - ' . $project['course_name']); ?>">
                                                    <?php if ($project['evaluation_id']): ?>
                                                        <i class="bi bi-pencil-square"></i> Update Evaluation
                                                    <?php else: ?>
                                                        <i class="bi bi-check-circle"></i> Evaluate Project
                                                    <?php endif; ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-folder display-1 text-muted"></i>
                                <h4 class="mt-3">No Projects Available</h4>
                                <p class="text-muted">There are no projects to evaluate at this time.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Evaluation Modal -->
    <div class="modal fade" id="evaluationModal" tabindex="-1" aria-labelledby="evaluationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="evaluationModalLabel">
                        Evaluate Project: <span id="modal-project-name"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Project Information -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6>Project Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Student:</strong></td>
                                    <td id="modal-student-name"></td>
                                </tr>
                                <tr>
                                    <td><strong>Course:</strong></td>
                                    <td id="modal-course-name"></td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td id="modal-project-description"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Evaluation Form -->
                    <form id="evaluation-form">
                        <input type="hidden" id="project-id" name="project_id">
                        
                        <!-- Criteria Section -->
                        <div class="mb-4">
                            <h6>Evaluation Criteria</h6>
                            <div id="criteria-container">
                                <!-- Criteria will be loaded via AJAX -->
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading criteria...</span>
                                    </div>
                                    <p class="mt-2">Loading evaluation criteria...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Feedback Section -->
                        <div class="mb-4">
                            <label for="feedback" class="form-label">
                                <strong>Overall Feedback</strong>
                            </label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="4" 
                                      placeholder="Provide constructive feedback for the student..."></textarea>
                            <div class="form-text">
                                This feedback will be visible to the student and other evaluators.
                            </div>
                        </div>
                        
                        <!-- Total Score Display -->
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Total Score:</strong>
                                    <span id="total-score" class="fw-bold fs-4 ms-2">0</span>
                                    <span id="max-total-score" class="text-muted"></span>
                                </div>
                                <div>
                                    <span id="score-percentage" class="badge bg-primary fs-6">0%</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="submit-evaluation">
                        <i class="bi bi-check-circle"></i> Submit Evaluation
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Success!</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-check-circle-fill text-success display-4"></i>
                    <p class="mt-3">Evaluation submitted successfully!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success w-100" data-bs-dismiss="modal">
                        Continue Evaluating
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        let currentProjectId = null;
        let maxTotalScore = 0;
        
        // Evaluation modal handling
        $('.evaluate-btn').click(function() {
            const projectId = $(this).data('project-id');
            const projectName = $(this).data('project-name');
            const studentName = $(this).data('student-name');
            const courseName = $(this).data('course-name');
            
            currentProjectId = projectId;
            
            // Set modal content
            $('#modal-project-name').text(projectName);
            $('#modal-student-name').text(studentName);
            $('#modal-course-name').text(courseName);
            $('#project-id').val(projectId);
            
            // Reset form
            $('#feedback').val('');
            $('#total-score').text('0');
            $('#score-percentage').text('0%');
            
            // Show loading state for criteria
            $('#criteria-container').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading criteria...</span>
                    </div>
                    <p class="mt-2">Loading evaluation criteria...</p>
                </div>
            `);
            
            // Load criteria via AJAX
            loadCriteria(projectId);
            
            // Load existing evaluation if any
            loadExistingEvaluation(projectId);
            
            $('#evaluationModal').modal('show');
        });
        
        function loadCriteria(projectId) {
            $.ajax({
                url: '../ajax/get_criteria.php',
                method: 'POST',
                data: { project_id: projectId },
                success: function(response) {
                    $('#criteria-container').html(response);
                    calculateTotalScore();
                    
                    // Calculate max total score
                    maxTotalScore = 0;
                    $('.score-input').each(function() {
                        maxTotalScore += parseFloat($(this).attr('max')) || 0;
                    });
                    
                    if (maxTotalScore > 0) {
                        $('#max-total-score').text('/ ' + maxTotalScore);
                    }
                },
                error: function(xhr, status, error) {
                    $('#criteria-container').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            Error loading criteria: ${error}
                        </div>
                    `);
                }
            });
        }
        
        function loadExistingEvaluation(projectId) {
            $.ajax({
                url: '../ajax/get_evaluation.php',
                method: 'POST',
                data: { project_id: projectId },
                success: function(response) {
                    if (response.success && response.data) {
                        $('#feedback').val(response.data.feedback);
                        if (response.data.scores) {
                            $.each(response.data.scores, function(criterionId, score) {
                                $(`#score-${criterionId}`).val(score);
                            });
                            calculateTotalScore();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading existing evaluation:', error);
                }
            });
        }
        
        // Calculate total score when scores change
        $(document).on('input', '.score-input', function() {
            const maxScore = parseFloat($(this).attr('max')) || 0;
            let currentScore = parseFloat($(this).val()) || 0;
            
            // Validate score doesn't exceed max
            if (currentScore > maxScore) {
                currentScore = maxScore;
                $(this).val(maxScore);
            }
            
            // Validate score is not negative
            if (currentScore < 0) {
                currentScore = 0;
                $(this).val(0);
            }
            
            calculateTotalScore();
        });
        
        function calculateTotalScore() {
            let total = 0;
            $('.score-input').each(function() {
                const score = parseFloat($(this).val()) || 0;
                total += score;
            });
            
            $('#total-score').text(total.toFixed(2));
            
            // Calculate percentage
            const percentage = maxTotalScore > 0 ? (total / maxTotalScore) * 100 : 0;
            $('#score-percentage').text(percentage.toFixed(1) + '%');
            
            // Update color based on score percentage
            const percentageElement = $('#score-percentage');
            percentageElement.removeClass('bg-success bg-warning bg-danger');
            
            if (percentage >= 80) {
                percentageElement.addClass('bg-success');
            } else if (percentage >= 60) {
                percentageElement.addClass('bg-warning');
            } else {
                percentageElement.addClass('bg-danger');
            }
        }
        
        // Submit evaluation
        $('#submit-evaluation').click(function() {
            const projectId = $('#project-id').val();
            const feedback = $('#feedback').val();
            const scores = {};
            
            // Validate at least one score is provided
            let hasScores = false;
            $('.score-input').each(function() {
                const criterionId = $(this).data('criterion-id');
                const score = $(this).val() || 0;
                scores[criterionId] = score;
                
                if (parseFloat(score) > 0) {
                    hasScores = true;
                }
            });
            
            if (!hasScores) {
                alert('Please provide scores for at least one criterion.');
                return;
            }
            
            // Validate feedback is provided
            if (!feedback.trim()) {
                if (!confirm('You haven\'t provided any feedback. Continue without feedback?')) {
                    return;
                }
            }
            
            // Show loading state
            const submitBtn = $(this);
            const originalText = submitBtn.html();
            submitBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');
            submitBtn.prop('disabled', true);
            
            $.ajax({
                url: '../ajax/save_evaluation.php',
                method: 'POST',
                data: {
                    project_id: projectId,
                    feedback: feedback,
                    scores: scores
                },
                success: function(response) {
                    if (response.success) {
                        $('#evaluationModal').modal('hide');
                        $('#successModal').modal('show');
                        
                        // Reload page after a delay to show updated status
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        alert('Error: ' + response.message);
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error saving evaluation: ' + error);
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                }
            });
        });
        
        // Auto-check for URL parameters to open evaluation modal
        const urlParams = new URLSearchParams(window.location.search);
        const evaluateProjectId = urlParams.get('evaluate');
        if (evaluateProjectId) {
            // Find and trigger the evaluate button for this project
            const evaluateBtn = $(`.evaluate-btn[data-project-id="${evaluateProjectId}"]`);
            if (evaluateBtn.length) {
                evaluateBtn.click();
            }
        }
        
        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // Ctrl+Enter to submit evaluation when modal is open
            if (e.ctrlKey && e.keyCode === 13 && $('#evaluationModal').is(':visible')) {
                $('#submit-evaluation').click();
            }
            
            // Escape to close modal
            if (e.keyCode === 27 && $('#evaluationModal').is(':visible')) {
                $('#evaluationModal').modal('hide');
            }
        });
        
        // Auto-save functionality (optional)
        let autoSaveTimer;
        $(document).on('input', '#evaluation-form input, #evaluation-form textarea', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function() {
                // Optional: Implement auto-save here
                console.log('Auto-save triggered');
            }, 5000); // Auto-save after 5 seconds of inactivity
        });
    });
    </script>
</body>
</html>