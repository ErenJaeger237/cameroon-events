<?php
require_once '../includes/db.php';

// Redirect if not logged in or is admin
if (!isLoggedIn() || isAdmin()) {
    redirect('../auth/login.php', 'Please log in to access your booking history.', 'info');
}

$user_id = getCurrentUserId();

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Build query
$where_conditions = ["b.user_id = ?"];
$params = [$user_id];

if (!empty($status_filter)) {
    $where_conditions[] = "b.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_filter)) {
    if ($date_filter === 'upcoming') {
        $where_conditions[] = "e.date >= CURDATE()";
    } elseif ($date_filter === 'past') {
        $where_conditions[] = "e.date < CURDATE()";
    }
}

$where_clause = implode(' AND ', $where_conditions);

// Get user bookings with event details
$bookings = fetchAll("
    SELECT b.*, e.name as event_name, e.date, e.time, e.venue, e.location, e.image
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE $where_clause
    ORDER BY b.created_at DESC
", $params);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History - CameroonEvents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/african-theme.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #fafafa;
        }

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 2rem;
        }

        .filter-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .qr-modal .modal-content {
            border-radius: 20px;
            border: none;
        }

        .qr-code-container {
            text-align: center;
            padding: 2rem;
        }

        .ticket-actions {
            gap: 0.5rem;
        }
    </style>
</head>
<body class="african-pattern">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-drum me-2"></i>CameroonEvents
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

    <!-- Booking History Content -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-history me-2"></i>Booking History</h2>
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" action="">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="">All Statuses</option>
                                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="date" class="form-label">Date</label>
                            <select class="form-select" name="date" id="date">
                                <option value="">All Dates</option>
                                <option value="upcoming" <?php echo $date_filter === 'upcoming' ? 'selected' : ''; ?>>Upcoming Events</option>
                                <option value="past" <?php echo $date_filter === 'past' ? 'selected' : ''; ?>>Past Events</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                            <a href="history.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Bookings List -->
            <?php if ($bookings): ?>
                <div class="row g-4">
                    <?php foreach ($bookings as $booking):
                        $is_upcoming = strtotime($booking['date']) >= strtotime('today');
                        $status_class = $booking['status'] === 'confirmed' ? 'success' :
                                      ($booking['status'] === 'cancelled' ? 'danger' : 'secondary');
                    ?>
                        <div class="col-lg-6">
                            <div class="card booking-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="event-image me-3" style="background-image: url('../assets/images/<?php echo $booking['image']; ?>');">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-2"><?php echo htmlspecialchars($booking['event_name']); ?></h5>
                                            <span class="badge bg-<?php echo $status_class; ?> status-badge mb-2">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                            <?php if ($is_upcoming): ?>
                                                <span class="badge bg-info status-badge">Upcoming</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted d-block">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('M d, Y', strtotime($booking['date'])); ?>
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('g:i A', strtotime($booking['time'])); ?>
                                            </small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo htmlspecialchars($booking['venue']); ?>
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-ticket-alt me-1"></i>
                                                <?php echo ucfirst(str_replace('_', ' ', $booking['ticket_type'])); ?> × <?php echo $booking['quantity']; ?>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold h6" style="color: var(--african-gold);"><?php echo number_format($booking['total_amount'], 0); ?> FCFA</div>
                                            <small class="text-muted">Ref: <?php echo $booking['booking_reference']; ?></small>
                                        </div>
                                        <div class="d-flex ticket-actions">
                                            <button class="btn btn-primary btn-sm" onclick="showQRCode('<?php echo $booking['id']; ?>', '<?php echo htmlspecialchars($booking['event_name']); ?>', '<?php echo $booking['booking_reference']; ?>')">
                                                <i class="fas fa-qrcode me-1"></i>QR Code
                                            </button>
                                            <a href="ticket.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-secondary btn-sm" target="_blank">
                                                <i class="fas fa-download me-1"></i>PDF
                                            </a>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Booked on <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
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
                    <h5>No bookings found</h5>
                    <p class="text-muted">
                        <?php if ($status_filter || $date_filter): ?>
                            Try adjusting your filters or <a href="history.php">view all bookings</a>.
                        <?php else: ?>
                            Start exploring events and make your first booking!
                        <?php endif; ?>
                    </p>
                    <a href="../events/list.php" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Browse Events
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- QR Code Modal -->
    <div class="modal fade qr-modal" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--primary-gradient); color: white;">
                    <h5 class="modal-title" id="qrModalLabel">
                        <i class="fas fa-qrcode me-2"></i>Your Event Ticket
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body qr-code-container">
                    <div id="qrCodeDisplay"></div>
                    <h6 class="mt-3" id="eventName"></h6>
                    <p class="text-muted" id="bookingRef"></p>
                    <div class="mt-3">
                        <button class="btn btn-secondary" onclick="downloadQRImage()">
                            <i class="fas fa-download me-2"></i>Save QR Code
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        function showQRCode(bookingId, eventName, bookingRef) {
            // Generate QR code data
            const qrData = {
                booking_id: bookingId,
                booking_reference: bookingRef,
                event_name: eventName,
                verification_url: window.location.origin + '/OnlineEventBookingSystem_FULL/verify.php?ref=' + bookingRef
            };

            // Clear previous QR code
            document.getElementById('qrCodeDisplay').innerHTML = '';

            // Generate QR code
            QRCode.toCanvas(document.getElementById('qrCodeDisplay'), JSON.stringify(qrData), {
                width: 256,
                margin: 2,
                color: {
                    dark: '#007A3D',  // Cameroon green
                    light: '#FFFFFF'
                }
            }, function (error) {
                if (error) console.error(error);
            });

            // Update modal content
            document.getElementById('eventName').textContent = eventName;
            document.getElementById('bookingRef').textContent = 'Booking Reference: ' + bookingRef;

            // Show modal
            new bootstrap.Modal(document.getElementById('qrModal')).show();
        }

        function downloadQRImage() {
            const canvas = document.querySelector('#qrCodeDisplay canvas');
            if (canvas) {
                const link = document.createElement('a');
                link.download = 'ticket-qr-code.png';
                link.href = canvas.toDataURL();
                link.click();
            }
        }
    </script>
</body>
</html>