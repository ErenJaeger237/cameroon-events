<?php
require_once '../includes/db.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php', 'Admin access required.', 'error');
}

// Get dashboard statistics
$total_events = getCount("SELECT COUNT(*) FROM events");
$active_events = getCount("SELECT COUNT(*) FROM events WHERE status = 'active' AND date >= CURDATE()");
$total_bookings = getCount("SELECT COUNT(*) FROM bookings");
$total_users = getCount("SELECT COUNT(*) FROM users WHERE role = 'user'");
$total_revenue = fetchOne("SELECT SUM(total_amount) as revenue FROM bookings WHERE status = 'confirmed'")['revenue'] ?? 0;

// Get recent bookings
$recent_bookings = fetchAll("
    SELECT b.*, e.name as event_name, u.name as user_name, u.email as user_email
    FROM bookings b 
    JOIN events e ON b.event_id = e.id 
    JOIN users u ON b.user_id = u.id 
    ORDER BY b.created_at DESC 
    LIMIT 5
");

// Get upcoming events
$upcoming_events = fetchAll("
    SELECT *, (max_capacity - current_bookings) as available_tickets
    FROM events 
    WHERE status = 'active' AND date >= CURDATE() 
    ORDER BY date ASC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CameroonEvents</title>
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
            font-size: 2.2rem;
        }
        .stat-card.revenue {
            background: var(--accent-gradient);
            color: var(--text-primary);
        }
        .stat-card.events {
            background: var(--secondary-gradient);
        }
        .stat-card.users {
            background: var(--primary-gradient);
        }
        .admin-sidebar {
            background: #f8f9fa;
            min-height: calc(100vh - 76px);
            border-radius: 10px;
        }
        .sidebar-link {
            color: #495057;
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: all 0.3s ease;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: var(--primary-gradient);
            color: white;
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
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-1"></i><?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../index.php">
                                <i class="fas fa-home me-2"></i>View Site
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2">
                <div class="admin-sidebar p-3">
                    <h6 class="text-muted mb-3">ADMIN PANEL</h6>
                    <nav>
                        <a href="dashboard.php" class="sidebar-link active">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="events.php" class="sidebar-link">
                            <i class="fas fa-calendar me-2"></i>Manage Events
                        </a>
                        <a href="upload_images.php" class="sidebar-link">
                            <i class="fas fa-images me-2"></i>Event Images
                        </a>
                        <a href="bookings.php" class="sidebar-link">
                            <i class="fas fa-ticket-alt me-2"></i>View Bookings
                        </a>
                        <a href="users.php" class="sidebar-link">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                        <a href="reports.php" class="sidebar-link">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="admin-title"><i class="fas fa-tachometer-alt me-2" style="color: var(--cameroon-green);"></i>Admin Dashboard</h2>
                    <div>
                        <span class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 🇨🇲</span>
                    </div>
                </div>

                <?php 
                $flash = getFlashMessage();
                if ($flash): 
                ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $flash['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-5">
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <i class="fas fa-calendar fa-2x mb-3"></i>
                            <h3 class="mb-1"><?php echo $total_events; ?></h3>
                            <p class="mb-0">Total Events</p>
                            <small class="opacity-75"><?php echo $active_events; ?> active</small>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <i class="fas fa-ticket-alt fa-2x mb-3"></i>
                            <h3 class="mb-1"><?php echo $total_bookings; ?></h3>
                            <p class="mb-0">Total Bookings</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card users">
                            <i class="fas fa-users fa-2x mb-3"></i>
                            <h3 class="mb-1"><?php echo $total_users; ?></h3>
                            <p class="mb-0">Registered Users</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card revenue">
                            <i class="fas fa-coins fa-2x mb-3"></i>
                            <h3 class="mb-1"><?php echo number_format($total_revenue, 0); ?> FCFA</h3>
                            <p class="mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row g-3 mb-5">
                    <div class="col-md-3">
                        <a href="events.php?action=add" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Add New Event
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="events.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-calendar me-2"></i>Manage Events
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="bookings.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-ticket-alt me-2"></i>View Bookings
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="reports.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-chart-bar me-2"></i>View Reports
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Bookings -->
                    <div class="col-lg-7">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Bookings</h5>
                                <a href="bookings.php" class="btn btn-outline-primary btn-sm">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if ($recent_bookings): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>User</th>
                                                    <th>Event</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_bookings as $booking): ?>
                                                    <tr>
                                                        <td>
                                                            <div>
                                                                <div class="fw-bold"><?php echo htmlspecialchars($booking['user_name']); ?></div>
                                                                <small class="text-muted"><?php echo htmlspecialchars($booking['user_email']); ?></small>
                                                            </div>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($booking['event_name']); ?></td>
                                                        <td><?php echo number_format($booking['total_amount'], 0); ?> FCFA</td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : 'secondary'; ?>">
                                                                <?php echo ucfirst($booking['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('M d', strtotime($booking['created_at'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-ticket-alt fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">No bookings yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="col-lg-5">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Upcoming Events</h5>
                                <a href="events.php" class="btn btn-outline-primary btn-sm">Manage</a>
                            </div>
                            <div class="card-body">
                                <?php if ($upcoming_events): ?>
                                    <?php foreach ($upcoming_events as $event): ?>
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($event['name']); ?></div>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($event['date'])); ?> • 
                                                    <?php echo $event['available_tickets']; ?> tickets left
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold" style="color: var(--african-gold);"><?php echo number_format($event['price'], 0); ?> FCFA</div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">No upcoming events</p>
                                        <a href="events.php?action=add" class="btn btn-primary btn-sm">Add Event</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
