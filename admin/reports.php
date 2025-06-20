<?php
require_once '../includes/db.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php', 'Please log in as admin to access this page.', 'error');
}

// Get date range for reports
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today

// Revenue statistics
$total_revenue = fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE status = 'confirmed'")['total'];
$period_revenue = fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE status = 'confirmed' AND DATE(created_at) BETWEEN ? AND ?", [$date_from, $date_to])['total'];

// Booking statistics
$total_bookings = getCount("SELECT COUNT(*) FROM bookings");
$period_bookings = getCount("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) BETWEEN ? AND ?", [$date_from, $date_to]);
$confirmed_bookings = getCount("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'");
$cancelled_bookings = getCount("SELECT COUNT(*) FROM bookings WHERE status = 'cancelled'");

// Event statistics
$total_events = getCount("SELECT COUNT(*) FROM events");
$active_events = getCount("SELECT COUNT(*) FROM events WHERE status = 'active' AND date >= CURDATE()");
$past_events = getCount("SELECT COUNT(*) FROM events WHERE date < CURDATE()");

// User statistics
$total_users = getCount("SELECT COUNT(*) FROM users WHERE role = 'user'");
$new_users_period = getCount("SELECT COUNT(*) FROM users WHERE role = 'user' AND DATE(created_at) BETWEEN ? AND ?", [$date_from, $date_to]);

// Top events by bookings
$top_events = fetchAll("
    SELECT e.name, e.location, COUNT(b.id) as booking_count, SUM(b.total_amount) as revenue
    FROM events e
    LEFT JOIN bookings b ON e.id = b.event_id AND b.status = 'confirmed'
    GROUP BY e.id, e.name, e.location
    ORDER BY booking_count DESC
    LIMIT 10
");

// Recent bookings
$recent_bookings = fetchAll("
    SELECT b.*, e.name as event_name, u.name as user_name
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    JOIN users u ON b.user_id = u.id
    ORDER BY b.created_at DESC
    LIMIT 10
");

// Monthly revenue trend (last 6 months)
$monthly_revenue = fetchAll("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(total_amount) as revenue,
        COUNT(*) as bookings
    FROM bookings 
    WHERE status = 'confirmed' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - CameroonEvents Admin</title>
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
        
        .report-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .metric-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 1rem;
        }
        
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .metric-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
    </style>
</head>
<body class="african-pattern">
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
                <i class="fas fa-chart-bar me-3" style="color: var(--cameroon-yellow);"></i>
                Reports & Analytics
            </h1>
            <p class="mb-0">Business insights and performance metrics</p>
        </div>
    </div>

    <div class="container">
        <!-- Date Range Filter -->
        <div class="report-card">
            <h5 class="mb-3">
                <i class="fas fa-calendar-alt me-2"></i>Report Period
            </h5>
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Key Metrics -->
        <div class="row">
            <div class="col-md-3">
                <div class="metric-card stat-card revenue">
                    <div class="metric-value"><?php echo number_format($total_revenue, 0); ?> FCFA</div>
                    <div class="metric-label">Total Revenue</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card stat-card">
                    <div class="metric-value"><?php echo $total_bookings; ?></div>
                    <div class="metric-label">Total Bookings</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card stat-card secondary">
                    <div class="metric-value"><?php echo $active_events; ?></div>
                    <div class="metric-label">Active Events</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card stat-card accent">
                    <div class="metric-value"><?php echo $total_users; ?></div>
                    <div class="metric-label">Total Users</div>
                </div>
            </div>
        </div>

        <!-- Period Metrics -->
        <div class="report-card">
            <h5 class="mb-3">
                <i class="fas fa-calendar-week me-2"></i>Period Performance 
                <small class="text-muted">(<?php echo date('M j', strtotime($date_from)); ?> - <?php echo date('M j, Y', strtotime($date_to)); ?>)</small>
            </h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                        <div>
                            <h6 class="mb-0">Revenue</h6>
                            <small class="text-muted">Period total</small>
                        </div>
                        <div class="text-end">
                            <h5 class="mb-0" style="color: var(--african-gold);"><?php echo number_format($period_revenue, 0); ?> FCFA</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                        <div>
                            <h6 class="mb-0">Bookings</h6>
                            <small class="text-muted">Period total</small>
                        </div>
                        <div class="text-end">
                            <h5 class="mb-0 text-primary"><?php echo $period_bookings; ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Events -->
            <div class="col-md-6">
                <div class="report-card">
                    <h5 class="mb-3">
                        <i class="fas fa-trophy me-2"></i>Top Events by Bookings
                    </h5>
                    <?php if (!empty($top_events)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Bookings</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($top_events, 0, 5) as $event): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($event['name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($event['location']); ?></small>
                                            </td>
                                            <td><span class="badge bg-primary"><?php echo $event['booking_count']; ?></span></td>
                                            <td><small><?php echo number_format($event['revenue'], 0); ?> FCFA</small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No booking data available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Booking Status -->
            <div class="col-md-6">
                <div class="report-card">
                    <h5 class="mb-3">
                        <i class="fas fa-chart-pie me-2"></i>Booking Status
                    </h5>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="p-3">
                                <h3 class="text-success"><?php echo $confirmed_bookings; ?></h3>
                                <small class="text-muted">Confirmed</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3">
                                <h3 class="text-danger"><?php echo $cancelled_bookings; ?></h3>
                                <small class="text-muted">Cancelled</small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($total_bookings > 0): ?>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: <?php echo ($confirmed_bookings / $total_bookings) * 100; ?>%"></div>
                            <div class="progress-bar bg-danger" style="width: <?php echo ($cancelled_bookings / $total_bookings) * 100; ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-success"><?php echo round(($confirmed_bookings / $total_bookings) * 100, 1); ?>% Confirmed</small>
                            <small class="text-danger"><?php echo round(($cancelled_bookings / $total_bookings) * 100, 1); ?>% Cancelled</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="report-card">
            <h5 class="mb-3">
                <i class="fas fa-clock me-2"></i>Recent Bookings
            </h5>
            <?php if (!empty($recent_bookings)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
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
                                    <td><code><?php echo htmlspecialchars($booking['booking_reference']); ?></code></td>
                                    <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['event_name']); ?></td>
                                    <td><?php echo number_format($booking['total_amount'], 0); ?> FCFA</td>
                                    <td>
                                        <span class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td><small><?php echo date('M j, Y', strtotime($booking['created_at'])); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No recent bookings found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
