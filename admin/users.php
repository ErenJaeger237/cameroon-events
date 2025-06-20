<?php
require_once '../includes/db.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php', 'Please log in as admin to access this page.', 'error');
}

$message = '';
$message_type = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $user_id = $_POST['user_id'];
        $action = $_POST['action'];
        
        switch ($action) {
            case 'delete':
                // Check if user has bookings
                $bookings = getCount("SELECT COUNT(*) FROM bookings WHERE user_id = ?", [$user_id]);
                if ($bookings > 0) {
                    $message = 'Cannot delete user with existing bookings. Cancel bookings first.';
                    $message_type = 'error';
                } else {
                    $result = executeQuery("DELETE FROM users WHERE id = ? AND role != 'admin'", [$user_id]);
                    if ($result) {
                        $message = 'User deleted successfully.';
                        $message_type = 'success';
                    } else {
                        $message = 'Failed to delete user.';
                        $message_type = 'error';
                    }
                }
                break;
                
            case 'toggle_role':
                $current_role = fetchOne("SELECT role FROM users WHERE id = ?", [$user_id])['role'];
                $new_role = ($current_role === 'admin') ? 'user' : 'admin';
                
                $result = executeQuery("UPDATE users SET role = ? WHERE id = ?", [$new_role, $user_id]);
                if ($result) {
                    $message = "User role updated to $new_role.";
                    $message_type = 'success';
                } else {
                    $message = 'Failed to update user role.';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get search parameters
$search = sanitizeInput($_GET['search'] ?? '');
$role_filter = $_GET['role'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($role_filter)) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
$sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC";
$users = fetchAll($sql, $params);

// Get statistics
$total_users = getCount("SELECT COUNT(*) FROM users");
$admin_count = getCount("SELECT COUNT(*) FROM users WHERE role = 'admin'");
$user_count = getCount("SELECT COUNT(*) FROM users WHERE role = 'user'");
$recent_users = getCount("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - CameroonEvents Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/african-theme.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #fafafa;
        }
        
        .admin-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .admin-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 2rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .search-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="pink-pattern">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-drum me-2"></i>CameroonEvents Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <a href="../auth/logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <h1 class="admin-title">
                <i class="fas fa-users me-3" style="color: var(--cameroon-yellow);"></i>
                User Management
            </h1>
            <p class="mb-0">Manage user accounts and permissions</p>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-users fa-2x mb-3"></i>
                    <h3 class="mb-1"><?php echo $total_users; ?></h3>
                    <p class="mb-0">Total Users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card secondary">
                    <i class="fas fa-user-shield fa-2x mb-3"></i>
                    <h3 class="mb-1"><?php echo $admin_count; ?></h3>
                    <p class="mb-0">Administrators</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card accent">
                    <i class="fas fa-user fa-2x mb-3"></i>
                    <h3 class="mb-1"><?php echo $user_count; ?></h3>
                    <p class="mb-0">Regular Users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-user-plus fa-2x mb-3"></i>
                    <h3 class="mb-1"><?php echo $recent_users; ?></h3>
                    <p class="mb-0">New This Week</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="search-section">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search Users</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="role" class="form-label">Filter by Role</label>
                        <select class="form-select" id="role" name="role">
                            <option value="">All Roles</option>
                            <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Users List
                    <?php if ($search || $role_filter): ?>
                        <span class="badge bg-secondary"><?php echo count($users); ?> results</span>
                    <?php endif; ?>
                </h5>
                <?php if ($search || $role_filter): ?>
                    <a href="users.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i>Clear Filters
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($users)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Bookings</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $user_bookings = getCount("SELECT COUNT(*) FROM bookings WHERE user_id = ?", [$user['id']]);
                                    $initials = strtoupper(substr($user['name'], 0, 1) . substr(strstr($user['name'], ' '), 1, 1));
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <?php echo $initials; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                                                    <small class="text-muted">ID: <?php echo $user['id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge role-badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                                <i class="fas fa-<?php echo $user['role'] === 'admin' ? 'user-shield' : 'user'; ?> me-1"></i>
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo date('M j, Y', strtotime($user['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $user_bookings; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to toggle this user\'s role?')">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="action" value="toggle_role">
                                                    <button type="submit" class="btn btn-outline-warning" title="Toggle Role">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                </form>
                                                
                                                <?php if ($user['role'] !== 'admin' || $admin_count > 1): ?>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete User">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x mb-3" style="color: var(--african-terracotta);"></i>
                        <h4 style="color: var(--text-primary);">No Users Found</h4>
                        <p class="text-muted">
                            <?php if ($search || $role_filter): ?>
                                No users match your search criteria.
                            <?php else: ?>
                                No users have registered yet.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
