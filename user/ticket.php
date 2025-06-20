<?php
require_once '../includes/db.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php', 'Please log in to access your tickets.', 'info');
}

$user_id = getCurrentUserId();
$booking_id = $_GET['booking_id'] ?? '';

if (empty($booking_id)) {
    redirect('history.php', 'Invalid booking ID.', 'error');
}

// Get booking details with event information
$booking = fetchOne("
    SELECT b.*, e.name as event_name, e.date, e.time, e.venue, e.location, e.description,
           u.name as user_name, u.email as user_email
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ? AND b.user_id = ?
", [$booking_id, $user_id]);

if (!$booking) {
    redirect('history.php', 'Booking not found or access denied.', 'error');
}

// Generate QR code data
$qr_data = json_encode([
    'booking_id' => $booking['id'],
    'booking_reference' => $booking['booking_reference'],
    'event_name' => $booking['event_name'],
    'user_name' => $booking['user_name'],
    'verification_url' => $_SERVER['HTTP_HOST'] . '/OnlineEventBookingSystem_FULL/verify.php?ref=' . $booking['booking_reference']
]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Ticket - CameroonEvents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/african-theme.css" rel="stylesheet">
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .ticket-container { 
                box-shadow: none !important; 
                border: 2px solid #007A3D !important;
                page-break-inside: avoid;
            }
        }
        
        .ticket-container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 3px solid var(--cameroon-green);
        }
        
        .ticket-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .ticket-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .ticket-body {
            padding: 2rem;
        }
        
        .event-details {
            border-left: 4px solid var(--cameroon-yellow);
            padding-left: 1rem;
            margin-bottom: 2rem;
        }
        
        .qr-section {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 15px;
            margin: 2rem 0;
        }
        
        .ticket-footer {
            background: var(--cameroon-yellow);
            color: var(--text-primary);
            padding: 1rem 2rem;
            text-align: center;
            font-weight: 600;
        }
        
        .detail-row {
            margin-bottom: 1rem;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .detail-value {
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="container-fluid no-print" style="background: var(--primary-gradient); padding: 1rem 0;">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center text-white">
                <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Event Ticket</h5>
                <div>
                    <button onclick="window.print()" class="btn btn-light me-2">
                        <i class="fas fa-print me-2"></i>Print Ticket
                    </button>
                    <a href="history.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to History
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="ticket-container">
        <!-- Ticket Header -->
        <div class="ticket-header">
            <div class="ticket-title">🇨🇲 CameroonEvents</div>
            <p class="mb-0">Official Event Ticket</p>
        </div>

        <!-- Ticket Body -->
        <div class="ticket-body">
            <div class="row">
                <div class="col-md-8">
                    <!-- Event Information -->
                    <div class="event-details">
                        <h3 class="text-primary mb-3"><?php echo htmlspecialchars($booking['event_name']); ?></h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="fas fa-calendar me-2" style="color: var(--cameroon-green);"></i>Date
                                    </div>
                                    <div class="detail-value"><?php echo date('l, F j, Y', strtotime($booking['date'])); ?></div>
                                </div>
                                
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="fas fa-clock me-2" style="color: var(--cameroon-red);"></i>Time
                                    </div>
                                    <div class="detail-value"><?php echo date('g:i A', strtotime($booking['time'])); ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="fas fa-map-marker-alt me-2" style="color: var(--cameroon-red);"></i>Venue
                                    </div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['venue']); ?></div>
                                </div>
                                
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="fas fa-location-dot me-2" style="color: var(--cameroon-red);"></i>Location
                                    </div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['location']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendee Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="fas fa-user me-2" style="color: var(--cameroon-green);"></i>Attendee
                                </div>
                                <div class="detail-value"><?php echo htmlspecialchars($booking['attendee_name']); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="fas fa-ticket-alt me-2" style="color: var(--cameroon-yellow);"></i>Ticket Type
                                </div>
                                <div class="detail-value"><?php echo ucfirst(str_replace('_', ' ', $booking['ticket_type'])); ?></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="fas fa-hashtag me-2" style="color: var(--cameroon-green);"></i>Quantity
                                </div>
                                <div class="detail-value"><?php echo $booking['quantity']; ?> ticket(s)</div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">
                                    <i class="fas fa-coins me-2" style="color: var(--african-gold);"></i>Total Amount
                                </div>
                                <div class="detail-value fw-bold" style="color: var(--african-gold);">
                                    <?php echo number_format($booking['total_amount'], 0); ?> FCFA
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- QR Code Section -->
                    <div class="qr-section">
                        <h6 class="mb-3">Scan for Verification</h6>
                        <div id="qrcode"></div>
                        <small class="text-muted mt-2 d-block">
                            Ref: <?php echo $booking['booking_reference']; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Footer -->
        <div class="ticket-footer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small>Booked on <?php echo date('M j, Y', strtotime($booking['created_at'])); ?></small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>Status: <strong><?php echo ucfirst($booking['status']); ?></strong></small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        // Generate QR code
        const qrData = <?php echo json_encode($qr_data); ?>;
        
        QRCode.toCanvas(document.getElementById('qrcode'), qrData, {
            width: 150,
            margin: 2,
            color: {
                dark: '#007A3D',  // Cameroon green
                light: '#FFFFFF'
            }
        }, function (error) {
            if (error) {
                console.error(error);
                document.getElementById('qrcode').innerHTML = '<p class="text-danger">QR Code generation failed</p>';
            }
        });
    </script>
</body>
</html>
