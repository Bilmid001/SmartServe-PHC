<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

checkAuth();
if (!hasPermission('admin')) {
    header("Location: ../../dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle user actions
if ($_POST['action'] ?? '' === 'create_user') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $role = $_POST['role'];
    $department = $_POST['department'];
    $full_name = $_POST['full_name'];
    
    $query = "INSERT INTO users (username, password, email, role, department, full_name) 
              VALUES (:username, :password, :email, :role, :department, :full_name)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':department', $department);
    $stmt->bindParam(':full_name', $full_name);
    
    if ($stmt->execute()) {
        $success = "User created successfully!";
    } else {
        $error = "Error creating user!";
    }
}

// Get all users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

?>
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PHCHMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     
     <style>
       

        /* Main Content Area */
        .main-content {
            margin-left: 120px !important;
            padding: 20px !important;
            min-height: 100vh !important;
            transition: all 0.3s ease !important;
        }
    </style>

        <div class="main-content">
            <div class="content-header">
                <h2>User Management</h2>
                <div class="breadcrumb">
                    <span>Admin</span> / <span>User Management</span>
                </div>
            </div>

            <!-- Create User Form -->
            <div class="dashboard-section">
                <h3>Create New User</h3>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="user-form">
                    <input type="hidden" name="action" value="create_user">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="admin">Administrator</option>
                                <option value="records">Records Officer</option>
                                <option value="doctor">Doctor</option>
                                <option value="pharmacy">Pharmacy</option>
                                <option value="lab">Laboratory</option>
                                <option value="eha">Environmental Health</option>
                                <option value="patient">Patient</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department">Department</label>
                            <input type="text" id="department" name="department">
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">Create User</button>
                </form>
            </div>

            <!-- Users List -->
            <div class="dashboard-section">
                <h3>System Users</h3>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['username']; ?></td>
                                <td><?php echo $user['full_name']; ?></td>
                                <td><span class="role-badge role-<?php echo $user['role']; ?>"><?php echo $user['role']; ?></span></td>
                                <td><?php echo $user['department']; ?></td>
                                <td><span class="status-badge status-<?php echo $user['status']; ?>"><?php echo $user['status']; ?></span></td>
                                <td>
                                    <button class="btn-sm btn-primary" onclick="editUser(<?php echo $user['id']; ?>)">Edit</button>
                                    <button class="btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function editUser(userId) {
        // Implement edit functionality
        alert('Edit user: ' + userId);
    }
    
    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            // Implement delete functionality
            alert('Delete user: ' + userId);
        }
    }
    </script>
</body>
</html>