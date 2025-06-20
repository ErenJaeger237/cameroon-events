<?php
require_once 'includes/db.php';

// Get featured events (limit to 6)
$featured_events = fetchAll("SELECT * FROM events WHERE status = 'active' AND date >= CURDATE() ORDER BY date ASC LIMIT 6");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CameroonEvents - Découvrez et Réservez vos Événements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/african-theme.css" rel="stylesheet">
    <style>
        /* Additional custom styles for homepage */
        body {
            font-family: 'Inter', sans-serif;
            background: #fafafa;
        }

        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 3.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero-content p {
            font-size: 1.2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 3rem;
        }

        .features-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body class="african-pattern">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-drum me-2"></i>CameroonEvents
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="events/list.php">Events</a>
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
                                    <li><a class="dropdown-item" href="admin/dashboard.php">
                                        <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                                    </a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="user/dashboard.php">
                                        <i class="fas fa-user me-2"></i>My Dashboard
                                    </a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="cart/view.php">
                                    <i class="fas fa-shopping-cart me-2"></i>My Cart
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 hero-content">
                    <h1 class="mb-4">Discover Amazing Events in Cameroon</h1>
                    <p class="lead mb-4">Book tickets for concerts, conferences, festivals and much more.
                    Your next unforgettable experience awaits in the heart of Africa.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="events/list.php" class="btn btn-light btn-lg">
                            <i class="fas fa-search me-2"></i>Browse Events
                        </a>
                        <?php if (!isLoggedIn()): ?>
                            <a href="auth/register.php" class="btn btn-accent btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Join Now
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Flash Messages -->
    <?php 
    $flash = getFlashMessage();
    if ($flash): 
    ?>
        <div class="container mt-4">
            <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Featured Events -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title african-heading">Featured Events</h2>
                <p class="text-muted">Don't miss out on these amazing upcoming events</p>
            </div>
            
            <?php if ($featured_events): ?>
                <div class="row g-4">
                    <?php foreach ($featured_events as $event): ?>
                        <div class="col-md-6 col-lg-4 fade-in-up">
                            <div class="card event-card h-100">
                                <div class="event-image" style="background-image: url('assets/images/<?php echo $event['image']; ?>');">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title" style="color: var(--text-primary);"><?php echo htmlspecialchars($event['name']); ?></h5>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-calendar me-2" style="color: var(--cameroon-green);"></i>
                                        <?php echo date('j M Y', strtotime($event['date'])); ?> à
                                        <?php echo date('H:i', strtotime($event['time'])); ?>
                                    </p>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-map-marker-alt me-2" style="color: var(--cameroon-red);"></i>
                                        <?php echo htmlspecialchars($event['venue'] . ', ' . $event['location']); ?>
                                    </p>
                                    <p class="card-text"><?php echo substr(htmlspecialchars($event['description']), 0, 100) . '...'; ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h5 mb-0" style="color: var(--african-gold);"><?php echo formatCurrency($event['price']); ?></span>
                                        <a href="events/details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-5">
                    <a href="events/list.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-eye me-2"></i>View All Events
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h4>No events available</h4>
                    <p class="text-muted">Check back later for exciting events!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 features-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title african-heading">Why Choose CameroonEvents?</h2>
                <p class="text-muted">Experience the best in event booking in Cameroon</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4 text-center fade-in-up">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <h5 style="color: var(--text-primary);">Easy Booking</h5>
                    <p class="text-muted">Simple and secure ticket booking process with instant confirmation</p>
                </div>
                <div class="col-md-4 text-center fade-in-up">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 style="color: var(--text-primary);">Secure Payments</h5>
                    <p class="text-muted">Your payment information is protected with industry-standard security</p>
                </div>
                <div class="col-md-4 text-center fade-in-up">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5 style="color: var(--text-primary);">24/7 Support</h5>
                    <p class="text-muted">Get help whenever you need it with our dedicated support team</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-african text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <i class="fas fa-drum me-2" style="color: var(--cameroon-yellow);"></i>CameroonEvents
                    </h5>
                    <p class="text-light">Votre destination de choix pour découvrir et réserver des événements extraordinaires au Cameroun.
                    Des concerts aux conférences, nous avons tout ce qu'il vous faut.</p>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Liens Rapides</h6>
                    <ul class="list-unstyled">
                        <li><a href="events/list.php" class="text-light text-decoration-none">Parcourir les Événements</a></li>
                        <li><a href="auth/register.php" class="text-light text-decoration-none">Créer un Compte</a></li>
                        <li><a href="auth/login.php" class="text-light text-decoration-none">Connexion</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Contact</h6>
                    <p class="text-light mb-1">
                        <i class="fas fa-envelope me-2" style="color: var(--cameroon-yellow);"></i>info@cameroonevents.cm
                    </p>
                    <p class="text-light mb-1">
                        <i class="fas fa-phone me-2" style="color: var(--cameroon-yellow);"></i>+237 6XX XXX XXX
                    </p>
                    <div class="mt-3">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-whatsapp fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.2);">
            <div class="text-center">
                <p class="text-light mb-0">&copy; 2024 CameroonEvents. Tous droits réservés. 🇨🇲</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
