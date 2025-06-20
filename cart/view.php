<?php
require_once '../includes/db.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php', 'Please log in to view your cart.', 'info');
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

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Event Booking System</title>
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
        .cart-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            transition: box-shadow 0.3s ease;
        }
        .cart-item:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .event-image {
            width: 80px;
            height: 80px;
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
                            <?php if (isAdmin()): ?>
                                <li><a class="dropdown-item" href="../admin/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                                </a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="../user/dashboard.php">
                                    <i class="fas fa-user me-2"></i>My Dashboard
                                </a></li>
                            <?php endif; ?>
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

    <!-- Cart Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h2>
                        <a href="../events/list.php" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>Continue Shopping
                        </a>
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

                    <?php if ($cart_items): ?>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item p-4 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <div class="event-image" style="background-image: url('../assets/images/<?php echo $item['image']; ?>');">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($item['event_name']); ?></h5>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('M d, Y', strtotime($item['date'])); ?> at 
                                            <?php echo date('g:i A', strtotime($item['time'])); ?>
                                        </p>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($item['venue']); ?>
                                        </p>
                                        <small class="text-primary">
                                            <i class="fas fa-ticket-alt me-1"></i>
                                            <?php echo ucfirst(str_replace('_', ' ', $item['ticket_type'])); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-2">
                                        <form method="POST" action="update.php" class="d-inline">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                            <div class="input-group input-group-sm">
                                                <button type="button" class="btn btn-outline-secondary" onclick="decreaseQuantity(<?php echo $item['id']; ?>)">-</button>
                                                <input type="number" class="form-control text-center" name="quantity" 
                                                       value="<?php echo $item['quantity']; ?>" min="1" max="10" 
                                                       id="quantity_<?php echo $item['id']; ?>" onchange="updateQuantity(<?php echo $item['id']; ?>)">
                                                <button type="button" class="btn btn-outline-secondary" onclick="increaseQuantity(<?php echo $item['id']; ?>)">+</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="fw-bold"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></div>
                                            <small class="text-muted"><?php echo formatCurrency($item['price']); ?> each</small>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <form method="POST" action="remove.php" class="d-inline">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                    onclick="return confirm('Remove this item from cart?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h4>Your cart is empty</h4>
                            <p class="text-muted">Start browsing events to add tickets to your cart.</p>
                            <a href="../events/list.php" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Browse Events
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($cart_items): ?>
                    <div class="col-lg-4">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Items (<?php echo count($cart_items); ?>)</span>
                                    <span><?php echo formatCurrency($total); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Processing Fee</span>
                                    <span><?php echo formatCurrency(500); ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold h5">
                                    <span>Total</span>
                                    <span><?php echo formatCurrency($total + 500); ?></span>
                                </div>
                                
                                <a href="checkout.php" class="btn btn-primary w-100 mt-3">
                                    <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                                </a>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        Secure checkout with SSL encryption
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function increaseQuantity(cartId) {
            const input = document.getElementById('quantity_' + cartId);
            if (parseInt(input.value) < 10) {
                input.value = parseInt(input.value) + 1;
                updateQuantity(cartId);
            }
        }

        function decreaseQuantity(cartId) {
            const input = document.getElementById('quantity_' + cartId);
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateQuantity(cartId);
            }
        }

        function updateQuantity(cartId) {
            const form = document.getElementById('quantity_' + cartId).closest('form');
            form.submit();
        }
    </script>
</body>
</html>
