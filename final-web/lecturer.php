<?php
session_start();
include 'db.php';

if ($_SESSION['user_role'] !== 'lecturer') {
    header('Location: dashboard.php');
    exit();
}

// Retrieve projects and their scores
$stmt = $pdo->prepare('
    SELECT p.*, g.score AS existing_score
    FROM projects p
    LEFT JOIN grades g ON p.id = g.project_id AND g.user_id = :user_id
');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$projects = $stmt->fetchAll();

// Handle grading submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'grade') {
    $project_id = $_POST['project_id'];
    $file_structure_syntax = $_POST['file_structure_syntax'];
    $proper_file_names = $_POST['proper_file_names'];
    $navigation_content = $_POST['navigation_content'];
    $links = $_POST['links'];
    $functionality = $_POST['functionality'];
    $ux_ui_design = $_POST['ux_ui_design'];
    $responsive = $_POST['responsive'];
    $hosting = $_POST['hosting'];

    // Calculate overall score
    $overall_score = $file_structure_syntax + $proper_file_names + $navigation_content + $links + $functionality + $ux_ui_design + $responsive + $hosting;
    $user_id = $_SESSION['user_id'];

    // Check if the lecturer has graded this project before
    $stmt_check = $pdo->prepare('SELECT * FROM grades WHERE user_id = :user_id AND project_id = :project_id');
    $stmt_check->execute(['user_id' => $user_id, 'project_id' => $project_id]);
    $exists = $stmt_check->fetch();

    if ($exists) {
        // Update the existing grade
        $stmt = $pdo->prepare('UPDATE grades SET score = :score WHERE user_id = :user_id AND project_id = :project_id');
        $stmt->execute(['score' => $overall_score, 'user_id' => $user_id, 'project_id' => $project_id]);
    } else {
        // Insert new grade
        $stmt = $pdo->prepare('INSERT INTO grades (user_id, project_id, score) VALUES (:user_id, :project_id, :score)');
        $stmt->execute(['user_id' => $user_id, 'project_id' => $project_id, 'score' => $overall_score]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Grade submitted successfully!']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Web Design</h1>

        <!-- Grade Projects Section -->
        <section class="mt-5">
            <h2>Grade Projects</h2>
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Project ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Graded</th>
                        <th>Score</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="project-list">
                    <?php foreach ($projects as $project): ?>
                        <tr id="project-<?php echo $project['id']; ?>">
                            <td><?php echo $project['id']; ?></td>
                            <td><?php echo $project['title']; ?></td>
                            <td><?php echo $project['description']; ?></td>
                            <td><?php echo ($project['existing_score'] !== null) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo ($project['existing_score'] !== null) ? number_format($project['existing_score'], 2) : 'Not Graded'; ?></td>
                            <td>
                                <button class="btn btn-success btn-grade" data-id="<?php echo $project['id']; ?>" data-bs-toggle="modal" data-bs-target="#gradeProjectModal">Grade</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

    <!-- Grade Project Modal -->
    <div class="modal fade" id="gradeProjectModal" tabindex="-1" aria-labelledby="gradeProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gradeProjectModalLabel">Grade Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="gradeProjectForm">
                        <input type="hidden" id="gradeProjectId" name="project_id">
                        
                        <h5>Rubric Scoring (Score out of 10 each)</h5>
                        <div class="mb-3">
                            <label class="form-label">File Structure & Syntax</label>
                            <input type="number" class="form-control" id="fileStructure" name="file_structure_syntax" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Proper File Names and Directory Structure</label>
                            <input type="number" class="form-control" id="properFileNames" name="proper_file_names" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Navigation & Content</label>
                            <input type="number" class="form-control" id="navigationContent" name="navigation_content" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Links</label>
                            <input type="number" class="form-control" id="links" name="links" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Functionality</label>
                            <input type="number" class="form-control" id="functionality" name="functionality" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">UX/UI Design</label>
                            <input type="number" class="form-control" id="uxUiDesign" name="ux_ui_design" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Responsive</label>
                            <input type="number" class="form-control" id="responsive" name="responsive" min="0" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hosting</label>
                            <input type="number" class="form-control" id="hosting" name="hosting" min="0" max="10" required>
                        </div>
                        <button type="submit" class="btn btn-success">Submit Grade</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Load project data into grade modal
            $('.btn-grade').click(function() {
                var projectId = $(this).data('id');
                $('#gradeProjectId').val(projectId);
                
                // Reset scores
                $('#fileStructure').val('');
                $('#properFileNames').val('');
                $('#navigationContent').val('');
                $('#links').val('');
                $('#functionality').val('');
                $('#uxUiDesign').val('');
                $('#responsive').val('');
                $('#hosting').val('');
            });

            // Grade project via AJAX
            $('#gradeProjectForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize() + '&action=grade';
                $.ajax({
                    type: 'POST',
                    url: 'lecturer.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            $('#gradeProjectModal').modal('hide');
                            alert(response.message);
                            location.reload(); // Optionally reload to update the project list
                        }
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
