<?php
require_once '../includes/db.php';

// Get search parameters
$search = sanitizeInput($_GET['search'] ?? '');
$location = sanitizeInput($_GET['location'] ?? '');
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$where_conditions = ["status = 'active'", "date >= CURDATE()"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "name LIKE ?";
    $params[] = "%$search%";
}

if (!empty($location)) {
    $where_conditions[] = "location LIKE ?";
    $params[] = "%$location%";
}

if (!empty($date_from)) {
    $where_conditions[] = "date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = "date <= ?";
    $params[] = $date_to;
}

$where_clause = implode(' AND ', $where_conditions);
$sql = "SELECT * FROM events WHERE $where_clause ORDER BY date ASC";
$events = fetchAll($sql, $params);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Events - CameroonEvents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/african-theme.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #fafafa;
        }

        .search-section {
            background: var(--primary-gradient);
            color: white;
            padding: 3rem 0;
        }

        .search-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 2.2rem;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            color: var(--text-primary);
            font-weight: 600;
        }
    </style>
</head>
<body class="pink-pattern">
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
                        <a class="nav-link active" href="list.php">Events</a>
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

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="text-center mb-4 search-title">Find Your Perfect Event</h2>
                    <form method="GET" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="search"
                                       placeholder="Search events..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="location"
                                       placeholder="Location..." value="<?php echo htmlspecialchars($location); ?>">
                            </div>
                            <div class="col-md-4">
                                <input type="date" class="form-control" name="date_from"
                                       value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>
                            <div class="col-md-4">
                                <input type="date" class="form-control" name="date_to"
                                       value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-accent w-100">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Events List -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="section-title">
                    <?php if ($search || $location || $date_from || $date_to): ?>
                        Résultats de Recherche (<?php echo count($events); ?> événements trouvés)
                    <?php else: ?>
                        Tous les Événements (<?php echo count($events); ?> événements)
                    <?php endif; ?>
                </h3>

                <?php if ($search || $location || $date_from || $date_to): ?>
                    <a href="list.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Effacer les Filtres
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if ($events): ?>
                <div class="row g-4">
                    <?php foreach ($events as $event): ?>
                        <div class="col-md-6 col-lg-4 fade-in-up">
                            <div class="card event-card h-100">
                                <?php if ($event['image']): ?>
                                    <?php
                                    // Check if it's an external URL or local file
                                    $image_src = (filter_var($event['image'], FILTER_VALIDATE_URL))
                                        ? $event['image']
                                        : "../assets/images/events/" . $event['image'];
                                    ?>
                                    <img src="<?php echo htmlspecialchars($image_src); ?>"
                                         class="card-img-top event-image"
                                         alt="<?php echo htmlspecialchars($event['name']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="event-image d-flex align-items-center justify-content-center"
                                         style="background: var(--primary-gradient); color: white; height: 200px;">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-alt fa-3x mb-2"></i>
                                            <p class="mb-0">Event Image</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
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
                                        <a href="details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">
                                            Voir Détails
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x mb-3" style="color: var(--african-terracotta);"></i>
                    <h4 style="color: var(--text-primary);">Aucun événement trouvé</h4>
                    <p class="text-muted">Essayez d'ajuster vos critères de recherche ou revenez plus tard pour de nouveaux événements.</p>
                    <a href="list.php" class="btn btn-primary">Voir Tous les Événements</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
