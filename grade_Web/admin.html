<?php
session_start();
include 'db.php';

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Handle project addition and deletion
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action == 'add') {
        $title = $_POST['title'];
        $description = $_POST['description'];

        $stmt = $pdo->prepare('INSERT INTO projects (title, description) VALUES (:title, :description)');
        $stmt->execute(['title' => $title, 'description' => $description]);
        echo json_encode(['status' => 'success', 'message' => 'Project added successfully!']);
        exit();
    } elseif ($action == 'edit_project') {
        $project_id = $_POST['project_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];

        $stmt = $pdo->prepare('UPDATE projects SET title = :title, description = :description WHERE id = :id');
        $stmt->execute(['title' => $title, 'description' => $description, 'id' => $project_id]);
        echo json_encode(['status' => 'success', 'message' => 'Project updated successfully!']);
        exit();
    } elseif ($action == 'delete_project') {
        $project_id = $_POST['project_id'];
        
        // Delete project and check for errors
        $stmt = $pdo->prepare('DELETE FROM projects WHERE id = :id');
        $stmt->execute(['id' => $project_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Project deleted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete the project!']);
        }
        exit();
    } elseif ($action == 'edit_user') {
        $user_id = $_POST['user_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

        $query = 'UPDATE users SET name = :name, email = :email, role = :role' . ($password ? ', password = :password' : '') . ' WHERE id = :id';
        $stmt = $pdo->prepare($query);
        $params = ['name' => $name, 'email' => $email, 'role' => $role, 'id' => $user_id];
        if ($password) $params['password'] = $password;
        $stmt->execute($params);
        echo json_encode(['status' => 'success', 'message' => 'User updated successfully!']);
        exit();
    } elseif ($action == 'delete_user') {
        $user_id = $_POST['user_id'];

        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $user_id]);
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully!']);
        exit();
    }
}

// Retrieve all projects
$stmt = $pdo->prepare('SELECT * FROM projects');
$stmt->execute();
$projects = $stmt->fetchAll();

// Retrieve all users
$stmt_users = $pdo->prepare('SELECT * FROM users');
$stmt_users->execute();
$users = $stmt_users->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Admin Dashboard</h1>

        <!-- Manage Projects Section -->
        <section class="mt-5">
            <h2>Manage Projects</h2>
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProjectModal">Add New Project</button>
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Project ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="project-list">
                    <?php foreach ($projects as $project): ?>
                        <tr id="project-<?php echo $project['id']; ?>">
                            <td><?php echo $project['id']; ?></td>
                            <td><?php echo $project['title']; ?></td>
                            <td><?php echo $project['description']; ?></td>
                            <td>
                                <button class="btn btn-warning btn-edit" data-id="<?php echo $project['id']; ?>">Edit</button>
                                <button class="btn btn-danger btn-delete" data-id="<?php echo $project['id']; ?>">Delete</button>
                                <a href="show_grades.php?project_id=<?php echo $project['id']; ?>" class="btn btn-info" target="_blank">Show Grades</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Manage Users Section -->
        <section class="mt-5">
            <h2>Manage Users</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="user-list">
                    <?php foreach ($users as $user): ?>
                        <tr id="user-<?php echo $user['id']; ?>">
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['role']; ?></td>
                            <td>
                                <button class="btn btn-warning btn-edit-user" data-id="<?php echo $user['id']; ?>" data-bs-toggle="modal" data-bs-target="#editUserModal">Edit</button>
                                <button class="btn btn-danger btn-delete-user" data-id="<?php echo $user['id']; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

    <!-- Add Project Modal -->
    <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProjectModalLabel">Add New Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addProjectForm">
                        <div class="mb-3">
                            <label for="projectTitle" class="form-label">Project Title</label>
                            <input type="text" class="form-control" id="projectTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="projectDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="projectDescription" name="description" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Project</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Project Modal -->
    <div class="modal fade" id="updateProjectModal" tabindex="-1" aria-labelledby="updateProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateProjectModalLabel">Edit Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateProjectForm">
                        <input type="hidden" id="editProjectId" name="project_id">
                        <div class="mb-3">
                            <label for="editProjectTitle" class="form-label">Project Title</label>
                            <input type="text" class="form-control" id="editProjectTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editProjectDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editProjectDescription" name="description" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning">Update Project</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="editUserId" name="user_id">
                        <div class="mb-3">
                            <label for="editUserName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editUserName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editUserEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editUserEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editUserRole" class="form-label">Role</label>
                            <select name="role" class="form-select" id="editUserRole" required>
                                <option value="admin">Admin</option>
                                <option value="lecturer">Lecturer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editUserPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="editUserPassword" name="password" placeholder="Leave blank to keep current password">
                        </div>
                        <button type="submit" class="btn btn-warning">Update User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Add project via AJAX
            $('#addProjectForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize() + '&action=add';
                $.ajax({
                    type: 'POST',
                    url: 'admin.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            location.reload(); // Reload to update the project list
                        }
                    }
                });
            });

            // Load project data into edit modal
            $('.btn-edit').click(function() {
                var projectId = $(this).data('id');
                var projectTitle = $('#project-' + projectId).find('td:eq(1)').text();
                var projectDescription = $('#project-' + projectId).find('td:eq(2)').text();
                
                $('#editProjectId').val(projectId);
                $('#editProjectTitle').val(projectTitle);
                $('#editProjectDescription').val(projectDescription);

                $('#updateProjectModal').modal('show');
            });

            // Update project via AJAX
            $('#updateProjectForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize() + '&action=edit_project';
                $.ajax({
                    type: 'POST',
                    url: 'admin.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            location.reload(); // Reload to update the project list
                        }
                    }
                });
            });

            // Delete project via AJAX
            $('.btn-delete').click(function() {
                if (confirm('Are you sure you want to delete this project?')) {
                    var projectId = $(this).data('id');
                    $.ajax({
                        type: 'POST',
                        url: 'admin.php',
                        data: {project_id: projectId, action: 'delete_project'},
                        dataType: 'json',
                        success: function(response) {
                            if (response.status == 'success') {
                                location.reload(); // Reload to update the project list
                            }
                        }
                    });
                }
            });

            // Load user data into edit user modal
            $('.btn-edit-user').click(function() {
                var userId = $(this).data('id');
                var userRow = $('#user-' + userId);
                var userName = userRow.find('td:eq(1)').text();
                var userEmail = userRow.find('td:eq(2)').text();
                var userRole = userRow.find('td:eq(3)').text();

                $('#editUserId').val(userId);
                $('#editUserName').val(userName);
                $('#editUserEmail').val(userEmail);
                $('#editUserRole').val(userRole);
                
                $('#editUserModal').modal('show');
            });

            // Update user via AJAX
            $('#editUserForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize() + '&action=edit_user';
                $.ajax({
                    type: 'POST',
                    url: 'admin.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            location.reload(); // Reload to update the user list
                        }
                    }
                });
            });

            // Delete user via AJAX
            $('.btn-delete-user').click(function() {
                if (confirm('Are you sure you want to delete this user?')) {
                    var userId = $(this).data('id');
                    $.ajax({
                        type: 'POST',
                        url: 'admin.php',
                        data: {user_id: userId, action: 'delete_user'},
                        dataType: 'json',
                        success: function(response) {
                            if (response.status == 'success') {
                                location.reload(); // Reload to update the user list
                            }
                        }
                    });
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>