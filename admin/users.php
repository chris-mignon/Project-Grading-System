<?php
require_once "../includes/auth.php";
redirectIfNotAdmin();

require_once "../config/database.php";
$database = new Database();
$db = $database->getConnection();

// Handle form submission for adding new user
if ($_POST && isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $status = 'active';
    
    $query = "INSERT INTO users (username, password, role, full_name, email, status) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$username, $password, $role, $full_name, $email, $status]);
    
    header("Location: users.php?success=1");
    exit();
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $user_id = $_GET['toggle_status'];
    $query = "UPDATE users SET status = IF(status='active', 'inactive', 'active') WHERE id = ? AND id != ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id, $_SESSION['user_id']]);
    
    header("Location: users.php?success=2");
    exit();
}

// Handle delete user
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Prevent deleting current user
    if ($delete_id != $_SESSION['user_id']) {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$delete_id]);
    }
    
    header("Location: users.php?success=3");
    exit();
}

// Fetch all users
$query = "SELECT id, username, role, full_name, email, status, created_at 
          FROM users 
          ORDER BY role, full_name";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count users by role
$admin_count = 0;
$lecturer_count = 0;
$active_count = 0;

foreach ($users as $user) {
    if ($user['role'] === 'admin') $admin_count++;
    if ($user['role'] === 'lecturer') $lecturer_count++;
    if ($user['status'] === 'active') $active_count++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Project Evaluation System</title>
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
                    <h2>Manage Users</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-person-plus"></i> Add User
                    </button>
                </div>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-<?php echo $_GET['success'] == 3 ? 'warning' : 'success'; ?> alert-dismissible fade show" role="alert">
                        <?php 
                        if ($_GET['success'] == 1) echo 'User added successfully!';
                        elseif ($_GET['success'] == 2) echo 'User status updated!';
                        elseif ($_GET['success'] == 3) echo 'User deleted successfully!';
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- User Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Total Users</h5>
                                <h3><?php echo count($users); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Active Users</h5>
                                <h3><?php echo $active_count; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5>Admins</h5>
                                <h3><?php echo $admin_count; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>Lecturers</h5>
                                <h3><?php echo $lecturer_count; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Users</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($users) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($user['username']); ?>
                                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                        <span class="badge bg-primary">You</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'info'; ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <a href="users.php?toggle_status=<?php echo $user['id']; ?>" 
                                                           class="btn btn-sm btn-<?php echo $user['status'] === 'active' ? 'warning' : 'success'; ?>">
                                                            <i class="bi bi-<?php echo $user['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                                            <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                        </a>
                                                        <a href="users.php?delete_id=<?php echo $user['id']; ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Current User</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-people display-1 text-muted"></i>
                                <h4 class="mt-3">No Users Found</h4>
                                <p class="text-muted">Get started by adding your first user.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Administrator</option>
                                <option value="lecturer">Lecturer</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>