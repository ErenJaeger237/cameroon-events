<?php
require_once '../includes/db.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php', 'Admin access required.', 'error');
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$event_filter = $_GET['event'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "b.status = ?";
    $params[] = $status_filter;
}

if (!empty($event_filter)) {
    $where_conditions[] = "b.event_id = ?";
    $params[] = $event_filter;
}

if (!empty($date_filter)) {
    if ($date_filter === 'today') {
        $where_conditions[] = "DATE(b.created_at) = CURDATE()";
    } elseif ($date_filter === 'week') {
        $where_conditions[] = "b.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($date_filter === 'month') {
        $where_conditions[] = "b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get bookings with user and event details
$bookings = fetchAll("
    SELECT b.*, e.name as event_name, e.date as event_date, e.time as event_time,
           u.name as user_name, u.email as user_email
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    JOIN users u ON b.user_id = u.id
    $where_clause
    ORDER BY b.created_at DESC
", $params);

// Get events for filter dropdown
$events = fetchAll("SELECT id, name FROM events ORDER BY name");

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $new_status = sanitizeInput($_POST['new_status'] ?? '');

    if ($booking_id > 0 && in_array($new_status, ['confirmed', 'cancelled', 'completed'])) {
        $result = executeQuery("UPDATE bookings SET status = ? WHERE id = ?", [$new_status, $booking_id]);

        if ($result) {
            redirect('bookings.php', 'Booking status updated successfully!', 'success');
        } else {
            $error = 'Failed to update booking status.';
        }
    } else {
        $error = 'Invalid booking or status.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand text-primary" href="../index.php">
                <i class="fas fa-calendar-alt me-2"></i>EventBooking Admin
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
                        <a href="dashboard.php" class="sidebar-link">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="events.php" class="sidebar-link">
                            <i class="fas fa-calendar me-2"></i>Manage Events
                        </a>
                        <a href="bookings.php" class="sidebar-link active">
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
                    <h2><i class="fas fa-ticket-alt me-2"></i>Manage Bookings</h2>
                    <div>
                        <span class="badge bg-primary"><?php echo count($bookings); ?> bookings found</span>
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

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="filter-section">
                    <form method="GET" action="">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" name="status" id="status">
                                    <option value="">All Statuses</option>
                                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="event" class="form-label">Event</label>
                                <select class="form-select" name="event" id="event">
                                    <option value="">All Events</option>
                                    <?php foreach ($events as $event): ?>
                                        <option value="<?php echo $event['id']; ?>" <?php echo $event_filter == $event['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($event['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date" class="form-label">Date Range</label>
                                <select class="form-select" name="date" id="date">
                                    <option value="">All Time</option>
                                    <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                                    <option value="week" <?php echo $date_filter === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                                    <option value="month" <?php echo $date_filter === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-2"></i>Apply Filters
                                </button>
                                <a href="bookings.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Bookings Table -->
                <?php if ($bookings): ?>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Booking Ref</th>
                                            <th>User</th>
                                            <th>Event</th>
                                            <th>Tickets</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Booked On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking):
                                            $status_class = $booking['status'] === 'confirmed' ? 'success' :
                                                          ($booking['status'] === 'cancelled' ? 'danger' : 'secondary');
                                        ?>
                                            <tr>
                                                <td>
                                                    <code><?php echo $booking['booking_reference']; ?></code>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($booking['user_name']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($booking['user_email']); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($booking['event_name']); ?></div>
                                                        <small class="text-muted">
                                                            <?php echo date('M d, Y', strtotime($booking['event_date'])); ?> at
                                                            <?php echo date('g:i A', strtotime($booking['event_time'])); ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div><?php echo ucfirst(str_replace('_', ' ', $booking['ticket_type'])); ?></div>
                                                        <small class="text-muted">Qty: <?php echo $booking['quantity']; ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-bold">$<?php echo number_format($booking['total_amount'], 2); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $status_class; ?> status-badge">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            Actions
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <form method="POST" action="" class="d-inline">
                                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                                    <input type="hidden" name="new_status" value="confirmed">
                                                                    <button type="submit" name="update_status" class="dropdown-item">
                                                                        <i class="fas fa-check me-2 text-success"></i>Confirm
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" action="" class="d-inline">
                                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                                    <input type="hidden" name="new_status" value="cancelled">
                                                                    <button type="submit" name="update_status" class="dropdown-item"
                                                                            onclick="return confirm('Cancel this booking?')">
                                                                        <i class="fas fa-times me-2 text-danger"></i>Cancel
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" action="" class="d-inline">
                                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                                    <input type="hidden" name="new_status" value="completed">
                                                                    <button type="submit" name="update_status" class="dropdown-item">
                                                                        <i class="fas fa-flag-checkered me-2 text-info"></i>Mark Complete
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                        <h5>No bookings found</h5>
                        <p class="text-muted">
                            <?php if ($status_filter || $event_filter || $date_filter): ?>
                                Try adjusting your filters or <a href="bookings.php">view all bookings</a>.
                            <?php else: ?>
                                Bookings will appear here once users start making reservations.
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