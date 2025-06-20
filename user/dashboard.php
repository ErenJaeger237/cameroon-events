<?php
require_once '../includes/db.php';

// Redirect if not logged in or is admin
if (!isLoggedIn() || isAdmin()) {
    redirect('../auth/login.php', 'Please log in to access your dashboard.', 'info');
}

$user_id = getCurrentUserId();

// Get user bookings with event details
$bookings = fetchAll("
    SELECT b.*, e.name as event_name, e.date, e.time, e.venue, e.location, e.image
    FROM bookings b 
    JOIN events e ON b.event_id = e.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
", [$user_id]);

// Get booking statistics
$total_bookings = count($bookings);
$upcoming_events = 0;
$total_spent = 0;

foreach ($bookings as $booking) {
    $total_spent += $booking['total_amount'];
    if (strtotime($booking['date']) >= strtotime('today')) {
        $upcoming_events++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Tableau de Bord - CameroonEvents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/african-theme.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #fafafa;
        }

        .dashboard-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .dashboard-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 2rem;
        }
        .booking-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .booking-card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .event-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            background: linear-gradient(45deg, #667eea, #764ba2);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand text-primary" href="../index.php">
                <i class="fas fa-calendar-alt me-2"></i>EventBooking
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../events/list.php">Events</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="dashboard.php">
                                <i class="fas fa-user me-2"></i>My Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="../cart/view.php">
                                <i class="fas fa-shopping-cart me-2"></i>My Cart
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

    <!-- Dashboard Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-4">
                        <i class="fas fa-tachometer-alt me-2"></i>My Dashboard
                        <small class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</small>
                    </h2>

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
                        <div class="col-md-4">
                            <div class="stat-card text-center">
                                <i class="fas fa-ticket-alt fa-2x mb-3"></i>
                                <h3 class="mb-1"><?php echo $total_bookings; ?></h3>
                                <p class="mb-0">Total Bookings</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card text-center">
                                <i class="fas fa-calendar-check fa-2x mb-3"></i>
                                <h3 class="mb-1"><?php echo $upcoming_events; ?></h3>
                                <p class="mb-0">Upcoming Events</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card text-center">
                                <i class="fas fa-dollar-sign fa-2x mb-3"></i>
                                <h3 class="mb-1">$<?php echo number_format($total_spent, 2); ?></h3>
                                <p class="mb-0">Total Spent</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row g-3 mb-5">
                        <div class="col-md-3">
                            <a href="../events/list.php" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Browse Events
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="../cart/view.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-shopping-cart me-2"></i>View Cart
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="history.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-history me-2"></i>Booking History
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="profile.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-user-edit me-2"></i>Edit Profile
                            </a>
                        </div>
                    </div>

                    <!-- Recent Bookings -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4><i class="fas fa-clock me-2"></i>Recent Bookings</h4>
                        <?php if ($bookings): ?>
                            <a href="history.php" class="btn btn-outline-primary btn-sm">View All</a>
                        <?php endif; ?>
                    </div>

                    <?php if ($bookings): ?>
                        <div class="row g-4">
                            <?php 
                            $recent_bookings = array_slice($bookings, 0, 6); // Show only first 6
                            foreach ($recent_bookings as $booking): 
                                $is_upcoming = strtotime($booking['date']) >= strtotime('today');
                                $status_class = $booking['status'] === 'confirmed' ? 'success' : 
                                              ($booking['status'] === 'cancelled' ? 'danger' : 'secondary');
                            ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card booking-card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="event-image me-3" style="background-image: url('../assets/images/<?php echo $booking['image']; ?>');">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($booking['event_name']); ?></h6>
                                                    <span class="badge bg-<?php echo $status_class; ?> status-badge">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo date('M d, Y', strtotime($booking['date'])); ?> at 
                                                    <?php echo date('g:i A', strtotime($booking['time'])); ?>
                                                </small>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo htmlspecialchars($booking['venue']); ?>
                                                </small>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-ticket-alt me-1"></i>
                                                    <?php echo ucfirst(str_replace('_', ' ', $booking['ticket_type'])); ?> × <?php echo $booking['quantity']; ?>
                                                </small>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-primary">$<?php echo number_format($booking['total_amount'], 2); ?></span>
                                                <div>
                                                    <button class="btn btn-outline-primary btn-sm" onclick="downloadTicket('<?php echo $booking['booking_reference']; ?>')">
                                                        <i class="fas fa-download me-1"></i>Ticket
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    Ref: <?php echo $booking['booking_reference']; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                            <h5>No bookings yet</h5>
                            <p class="text-muted">Start exploring events and make your first booking!</p>
                            <a href="../events/list.php" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Browse Events
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function downloadTicket(bookingRef) {
            // Simulate ticket download
            alert('Downloading ticket for booking: ' + bookingRef + '\n\nIn a real application, this would generate and download a PDF ticket.');
        }
    </script>
</body>
</html>
