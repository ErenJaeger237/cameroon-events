<?php
require_once 'includes/db.php';

$booking_ref = $_GET['ref'] ?? '';
$verification_result = null;
$booking_details = null;

if (!empty($booking_ref)) {
    // Verify booking reference
    $booking_details = fetchOne("
        SELECT b.*, e.name as event_name, e.date, e.time, e.venue, e.location,
               u.name as user_name
        FROM bookings b
        JOIN events e ON b.event_id = e.id
        JOIN users u ON b.user_id = u.id
        WHERE b.booking_reference = ?
    ", [$booking_ref]);
    
    if ($booking_details) {
        $verification_result = 'valid';
        
        // Check if event has passed
        $event_date = strtotime($booking_details['date'] . ' ' . $booking_details['time']);
        $current_time = time();
        
        if ($current_time > $event_date) {
            $verification_result = 'expired';
        } elseif ($booking_details['status'] !== 'confirmed') {
            $verification_result = 'cancelled';
        }
    } else {
        $verification_result = 'invalid';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Verification - CameroonEvents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/african-theme.css" rel="stylesheet">
    <style>
        .verification-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-gradient);
        }
        
        .verification-card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            margin: 2rem;
        }
        
        .verification-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 2rem;
        }
        
        .icon-valid {
            background: var(--cameroon-green);
            color: white;
        }
        
        .icon-invalid {
            background: var(--cameroon-red);
            color: white;
        }
        
        .icon-expired {
            background: var(--african-terracotta);
            color: white;
        }
        
        .icon-cancelled {
            background: #6c757d;
            color: white;
        }
        
        .verification-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .event-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .detail-item:last-child {
            border-bottom: none;
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
    <div class="verification-container">
        <div class="verification-card">
            <?php if (empty($booking_ref)): ?>
                <!-- No reference provided -->
                <div class="verification-icon icon-invalid">
                    <i class="fas fa-question"></i>
                </div>
                <h2 class="verification-title text-danger">No Booking Reference</h2>
                <p class="text-center text-muted">Please provide a booking reference to verify the ticket.</p>
                
                <form method="GET" action="" class="mt-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="ref" placeholder="Enter booking reference" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-2"></i>Verify
                        </button>
                    </div>
                </form>
                
            <?php elseif ($verification_result === 'valid'): ?>
                <!-- Valid ticket -->
                <div class="verification-icon icon-valid">
                    <i class="fas fa-check"></i>
                </div>
                <h2 class="verification-title text-success">✅ Valid Ticket</h2>
                <p class="text-center text-muted">This ticket is valid and confirmed for entry.</p>
                
                <div class="event-details">
                    <h5 class="text-primary mb-3"><?php echo htmlspecialchars($booking_details['event_name']); ?></h5>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-user me-2" style="color: var(--cameroon-green);"></i>Attendee
                        </span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking_details['attendee_name']); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-calendar me-2" style="color: var(--cameroon-green);"></i>Date
                        </span>
                        <span class="detail-value"><?php echo date('M j, Y', strtotime($booking_details['date'])); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-clock me-2" style="color: var(--cameroon-red);"></i>Time
                        </span>
                        <span class="detail-value"><?php echo date('g:i A', strtotime($booking_details['time'])); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-map-marker-alt me-2" style="color: var(--cameroon-red);"></i>Venue
                        </span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking_details['venue']); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-ticket-alt me-2" style="color: var(--cameroon-yellow);"></i>Tickets
                        </span>
                        <span class="detail-value"><?php echo $booking_details['quantity']; ?> × <?php echo ucfirst(str_replace('_', ' ', $booking_details['ticket_type'])); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-hashtag me-2" style="color: var(--african-gold);"></i>Reference
                        </span>
                        <span class="detail-value"><?php echo $booking_details['booking_reference']; ?></span>
                    </div>
                </div>
                
            <?php elseif ($verification_result === 'expired'): ?>
                <!-- Expired ticket -->
                <div class="verification-icon icon-expired">
                    <i class="fas fa-clock"></i>
                </div>
                <h2 class="verification-title text-warning">⏰ Expired Ticket</h2>
                <p class="text-center text-muted">This ticket was valid but the event has already passed.</p>
                
                <div class="event-details">
                    <h5 class="text-muted mb-3"><?php echo htmlspecialchars($booking_details['event_name']); ?></h5>
                    <p class="text-center">
                        Event Date: <?php echo date('M j, Y \a\t g:i A', strtotime($booking_details['date'] . ' ' . $booking_details['time'])); ?>
                    </p>
                </div>
                
            <?php elseif ($verification_result === 'cancelled'): ?>
                <!-- Cancelled ticket -->
                <div class="verification-icon icon-cancelled">
                    <i class="fas fa-times"></i>
                </div>
                <h2 class="verification-title text-secondary">❌ Cancelled Ticket</h2>
                <p class="text-center text-muted">This ticket has been cancelled and is not valid for entry.</p>
                
                <div class="event-details">
                    <h5 class="text-muted mb-3"><?php echo htmlspecialchars($booking_details['event_name']); ?></h5>
                    <p class="text-center">
                        Status: <span class="badge bg-secondary"><?php echo ucfirst($booking_details['status']); ?></span>
                    </p>
                </div>
                
            <?php else: ?>
                <!-- Invalid ticket -->
                <div class="verification-icon icon-invalid">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="verification-title text-danger">❌ Invalid Ticket</h2>
                <p class="text-center text-muted">This booking reference is not valid or does not exist in our system.</p>
                
                <div class="text-center mt-4">
                    <p class="text-muted">Reference: <code><?php echo htmlspecialchars($booking_ref); ?></code></p>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
                <?php if (!empty($booking_ref)): ?>
                    <a href="verify.php" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-search me-2"></i>Verify Another
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Powered by CameroonEvents Security System
                </small>
            </div>
        </div>
    </div>
</body>
</html>
