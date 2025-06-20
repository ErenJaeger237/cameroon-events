<?php
require_once '../includes/db.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php', 'Please log in to checkout.', 'info');
}

$user_id = getCurrentUserId();

// Get cart items with event details
$cart_items = fetchAll("
    SELECT c.*, e.name as event_name, e.date, e.time, e.venue, e.location, e.image
    FROM cart c 
    JOIN events e ON c.event_id = e.id 
    WHERE c.user_id = ? 
    ORDER BY c.created_at DESC
", [$user_id]);

// Redirect if cart is empty
if (!$cart_items) {
    redirect('view.php', 'Your cart is empty.', 'info');
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$processing_fee = 2.50;
$total = $subtotal + $processing_fee;

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendee_name = sanitizeInput($_POST['attendee_name'] ?? '');
    $payment_method = sanitizeInput($_POST['payment_method'] ?? '');
    
    // Validation
    if (empty($attendee_name)) {
        $error = 'Attendee name is required.';
    } elseif (empty($payment_method)) {
        $error = 'Payment method is required.';
    } else {
        // Start transaction
        try {
            $pdo = getDB();
            $pdo->beginTransaction();
            
            // Process each cart item
            foreach ($cart_items as $item) {
                // Generate booking reference
                $booking_reference = generateBookingReference();
                
                // Create booking
                $booking_sql = "INSERT INTO bookings (user_id, event_id, ticket_type, quantity, total_amount, attendee_name, booking_reference) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $booking_result = executeQuery($booking_sql, [
                    $user_id,
                    $item['event_id'],
                    $item['ticket_type'],
                    $item['quantity'],
                    $item['price'] * $item['quantity'],
                    $attendee_name,
                    $booking_reference
                ]);
                
                if (!$booking_result) {
                    throw new Exception('Failed to create booking');
                }
                
                $booking_id = getLastInsertId();
                
                // Create payment record
                $payment_sql = "INSERT INTO payments (booking_id, amount, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, 'completed', ?)";
                $transaction_id = 'TXN' . time() . rand(1000, 9999);
                $payment_result = executeQuery($payment_sql, [
                    $booking_id,
                    $item['price'] * $item['quantity'],
                    $payment_method,
                    $transaction_id
                ]);
                
                if (!$payment_result) {
                    throw new Exception('Failed to process payment');
                }
                
                // Update event booking count
                executeQuery("UPDATE events SET current_bookings = current_bookings + ? WHERE id = ?", 
                           [$item['quantity'], $item['event_id']]);
            }
            
            // Clear cart
            executeQuery("DELETE FROM cart WHERE user_id = ?", [$user_id]);
            
            // Commit transaction
            $pdo->commit();
            
            redirect('../user/dashboard.php', 'Booking confirmed successfully! Check your dashboard for details.', 'success');
            
        } catch (Exception $e) {
            // Rollback transaction
            $pdo->rollback();
            $error = 'Booking failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Event Booking System</title>
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
        .checkout-step {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .step-number {
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
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
                            <li><a class="dropdown-item" href="../user/dashboard.php">
                                <i class="fas fa-user me-2"></i>My Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="view.php">
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

    <!-- Checkout Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center mb-4">
                        <a href="view.php" class="btn btn-outline-secondary me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h2><i class="fas fa-credit-card me-2"></i>Checkout</h2>
                    </div>

                    <!-- Progress Steps -->
                    <div class="checkout-step">
                        <div class="d-flex align-items-center">
                            <div class="step-number me-3">1</div>
                            <div>
                                <h6 class="mb-0">Review Items</h6>
                                <small class="text-muted">Verify your selected tickets</small>
                            </div>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Order Items -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['event_name']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo ucfirst(str_replace('_', ' ', $item['ticket_type'])); ?> × <?php echo $item['quantity']; ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Checkout Form -->
                    <div class="checkout-step">
                        <div class="d-flex align-items-center mb-3">
                            <div class="step-number me-3">2</div>
                            <div>
                                <h6 class="mb-0">Attendee Information</h6>
                                <small class="text-muted">Enter attendee details</small>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="attendee_name" class="form-label">
                                        <i class="fas fa-user me-2"></i>Attendee Name *
                                    </label>
                                    <input type="text" class="form-control" id="attendee_name" name="attendee_name" 
                                           value="<?php echo htmlspecialchars($_POST['attendee_name'] ?? $_SESSION['user_name']); ?>" required>
                                    <div class="form-text">This name will appear on the tickets</div>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-step">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-number me-3">3</div>
                                <div>
                                    <h6 class="mb-0">Payment Method</h6>
                                    <small class="text-muted">Select payment option</small>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check p-3 border rounded">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   value="credit_card" id="credit_card" required>
                                            <label class="form-check-label w-100" for="credit_card">
                                                <i class="fas fa-credit-card me-2 text-primary"></i>
                                                <strong>Credit Card</strong>
                                                <div class="text-muted small">Visa, MasterCard, American Express</div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check p-3 border rounded">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   value="paypal" id="paypal" required>
                                            <label class="form-check-label w-100" for="paypal">
                                                <i class="fab fa-paypal me-2 text-primary"></i>
                                                <strong>PayPal</strong>
                                                <div class="text-muted small">Pay with your PayPal account</div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Demo Mode:</strong> This is a simulation. No actual payment will be processed.
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-lock me-2"></i>Complete Booking - $<?php echo number_format($total, 2); ?>
                        </button>
                    </form>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow sticky-top">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Total</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Processing Fee</span>
                                <span>$<?php echo number_format($processing_fee, 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold h5">
                                <span>Total</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <div class="mt-4">
                                <div class="d-flex align-items-center text-success mb-2">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <small>SSL Secured Checkout</small>
                                </div>
                                <div class="d-flex align-items-center text-success mb-2">
                                    <i class="fas fa-undo me-2"></i>
                                    <small>Free Cancellation</small>
                                </div>
                                <div class="d-flex align-items-center text-success">
                                    <i class="fas fa-mobile-alt me-2"></i>
                                    <small>Mobile Tickets</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
