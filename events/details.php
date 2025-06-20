<?php
require_once '../includes/db.php';

// Get event ID
$event_id = (int)($_GET['id'] ?? 0);

if (!$event_id) {
    redirect('list.php', 'Event not found.', 'error');
}

// Get event details
$event = fetchOne("SELECT * FROM events WHERE id = ? AND status = 'active'", [$event_id]);

if (!$event) {
    redirect('list.php', 'Event not found.', 'error');
}

// Parse ticket types
$ticket_types = json_decode($event['ticket_types'], true) ?? ['general' => $event['price']];

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $ticket_type = sanitizeInput($_POST['ticket_type'] ?? 'general');
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if ($quantity > 0 && isset($ticket_types[$ticket_type])) {
        $price = $ticket_types[$ticket_type];
        $user_id = getCurrentUserId();
        
        // Check if item already in cart
        $existing = fetchOne("SELECT id, quantity FROM cart WHERE user_id = ? AND event_id = ? AND ticket_type = ?", 
                           [$user_id, $event_id, $ticket_type]);
        
        if ($existing) {
            // Update quantity
            $new_quantity = $existing['quantity'] + $quantity;
            executeQuery("UPDATE cart SET quantity = ?, price = ? WHERE id = ?", 
                        [$new_quantity, $price, $existing['id']]);
        } else {
            // Add new item
            executeQuery("INSERT INTO cart (user_id, event_id, ticket_type, quantity, price) VALUES (?, ?, ?, ?, ?)",
                        [$user_id, $event_id, $ticket_type, $quantity, $price]);
        }
        
        redirect('../cart/view.php', 'Tickets added to cart successfully!', 'success');
    } else {
        $error = 'Invalid ticket selection.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['name']); ?> - Event Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .event-hero {
            height: 400px;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), 
                        linear-gradient(45deg, #667eea, #764ba2);
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            color: white;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .ticket-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .ticket-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        .ticket-card.selected {
            border-color: #667eea;
            background-color: #f8f9ff;
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
                        <a class="nav-link" href="list.php">Events</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo $_SESSION['user_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="../admin/dashboard.php">
                                        <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                                    </a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="../user/dashboard.php">
                                        <i class="fas fa-user me-2"></i>My Dashboard
                                    </a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="../cart/view.php">
                                    <i class="fas fa-shopping-cart me-2"></i>My Cart
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="../auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Event Hero -->
    <section class="event-hero" style="background-image: url('../assets/images/<?php echo $event['image']; ?>');">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($event['name']); ?></h1>
                    <p class="lead mb-4">
                        <i class="fas fa-calendar me-2"></i>
                        <?php echo date('l, F j, Y', strtotime($event['date'])); ?> at 
                        <?php echo date('g:i A', strtotime($event['time'])); ?>
                    </p>
                    <p class="lead">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <?php echo htmlspecialchars($event['venue'] . ', ' . $event['location']); ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Event Details -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-4">
                        <a href="list.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Back to Events
                        </a>
                    </div>
                    
                    <h3 class="mb-4">About This Event</h3>
                    <p class="lead"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    
                    <div class="row mt-5">
                        <div class="col-md-6">
                            <h5><i class="fas fa-info-circle me-2 text-primary"></i>Event Details</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['date'])); ?>
                                </li>
                                <li class="mb-2">
                                    <strong>Time:</strong> <?php echo date('g:i A', strtotime($event['time'])); ?>
                                </li>
                                <li class="mb-2">
                                    <strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?>
                                </li>
                                <li class="mb-2">
                                    <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                                </li>
                                <li class="mb-2">
                                    <strong>Capacity:</strong> <?php echo $event['max_capacity']; ?> people
                                </li>
                                <li class="mb-2">
                                    <strong>Available:</strong> <?php echo ($event['max_capacity'] - $event['current_bookings']); ?> tickets
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-user me-2 text-primary"></i>Organizer</h5>
                            <p><?php echo htmlspecialchars($event['organizer_contact']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Book Tickets</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!isLoggedIn()): ?>
                                <div class="text-center">
                                    <p class="text-muted">Please log in to book tickets</p>
                                    <a href="../auth/login.php" class="btn btn-primary w-100">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login to Book
                                    </a>
                                </div>
                            <?php elseif ($event['current_bookings'] >= $event['max_capacity']): ?>
                                <div class="text-center">
                                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                                    <h6>Event Sold Out</h6>
                                    <p class="text-muted">This event has reached maximum capacity.</p>
                                </div>
                            <?php else: ?>
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label">Select Ticket Type</label>
                                        <?php foreach ($ticket_types as $type => $price): ?>
                                            <div class="ticket-card p-3 mb-2" onclick="selectTicket('<?php echo $type; ?>')">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="ticket_type" 
                                                           value="<?php echo $type; ?>" id="ticket_<?php echo $type; ?>" required>
                                                    <label class="form-check-label w-100" for="ticket_<?php echo $type; ?>">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="fw-bold text-capitalize"><?php echo str_replace('_', ' ', $type); ?></span>
                                                            <span class="text-primary fw-bold"><?php echo formatCurrency($price); ?></span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <select class="form-select" name="quantity" id="quantity" required>
                                            <?php for ($i = 1; $i <= min(10, $event['max_capacity'] - $event['current_bookings']); $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectTicket(type) {
            // Remove selected class from all cards
            document.querySelectorAll('.ticket-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            document.getElementById('ticket_' + type).checked = true;
        }
    </script>
</body>
</html>
