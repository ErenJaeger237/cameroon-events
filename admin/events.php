<?php
require_once '../includes/db.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php', 'Admin access required.', 'error');
}

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_event'])) {
        // Add new event
        $name = sanitizeInput($_POST['name'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        $venue = sanitizeInput($_POST['venue'] ?? '');
        $location = sanitizeInput($_POST['location'] ?? '');
        $organizer_contact = sanitizeInput($_POST['organizer_contact'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $max_capacity = (int)($_POST['max_capacity'] ?? 100);
        
        // Validation
        if (empty($name) || empty($date) || empty($time) || empty($venue) || empty($location)) {
            $error = 'Please fill in all required fields.';
        } elseif ($price < 0) {
            $error = 'Price must be a positive number.';
        } elseif ($max_capacity < 1) {
            $error = 'Capacity must be at least 1.';
        } else {
            // Create ticket types JSON
            $ticket_types = json_encode([
                'general' => $price,
                'vip' => $price * 2,
                'student' => $price * 0.7
            ]);
            
            $sql = "INSERT INTO events (name, description, date, time, venue, location, organizer_contact, price, ticket_types, max_capacity, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            $result = executeQuery($sql, [$name, $description, $date, $time, $venue, $location, $organizer_contact, $price, $ticket_types, $max_capacity]);
            
            if ($result) {
                $success = 'Event added successfully!';
            } else {
                $error = 'Failed to add event. Please try again.';
            }
        }
    } elseif (isset($_POST['delete_event'])) {
        // Delete event
        $event_id = (int)($_POST['event_id'] ?? 0);
        if ($event_id > 0) {
            $result = executeQuery("DELETE FROM events WHERE id = ?", [$event_id]);
            if ($result) {
                $success = 'Event deleted successfully!';
            } else {
                $error = 'Failed to delete event.';
            }
        }
    } elseif (isset($_POST['update_status'])) {
        // Update event status
        $event_id = (int)($_POST['event_id'] ?? 0);
        $status = sanitizeInput($_POST['status'] ?? '');
        if ($event_id > 0 && in_array($status, ['active', 'cancelled', 'completed'])) {
            $result = executeQuery("UPDATE events SET status = ? WHERE id = ?", [$status, $event_id]);
            if ($result) {
                $success = 'Event status updated successfully!';
            } else {
                $error = 'Failed to update event status.';
            }
        }
    }
}

// Get all events
$events = fetchAll("SELECT *, (max_capacity - current_bookings) as available_tickets FROM events ORDER BY date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin Panel</title>
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
        .event-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .event-card:hover {
            transform: translateY(-2px);
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
                        <a href="events.php" class="sidebar-link active">
                            <i class="fas fa-calendar me-2"></i>Manage Events
                        </a>
                        <a href="bookings.php" class="sidebar-link">
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
                    <h2><i class="fas fa-calendar me-2"></i>Manage Events</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                        <i class="fas fa-plus me-2"></i>Add New Event
                    </button>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Events List -->
                <?php if ($events): ?>
                    <div class="row g-4">
                        <?php foreach ($events as $event): 
                            $status_class = $event['status'] === 'active' ? 'success' : 
                                          ($event['status'] === 'cancelled' ? 'danger' : 'secondary');
                            $is_past = strtotime($event['date']) < strtotime('today');
                        ?>
                            <div class="col-lg-6">
                                <div class="card event-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h5>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($event['status']); ?>
                                            </span>
                                        </div>
                                        
                                        <p class="card-text text-muted mb-2">
                                            <i class="fas fa-calendar me-2"></i>
                                            <?php echo date('M d, Y', strtotime($event['date'])); ?> at 
                                            <?php echo date('g:i A', strtotime($event['time'])); ?>
                                            <?php if ($is_past): ?>
                                                <span class="badge bg-warning ms-2">Past Event</span>
                                            <?php endif; ?>
                                        </p>
                                        
                                        <p class="card-text text-muted mb-2">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            <?php echo htmlspecialchars($event['venue'] . ', ' . $event['location']); ?>
                                        </p>
                                        
                                        <p class="card-text mb-3">
                                            <?php echo substr(htmlspecialchars($event['description']), 0, 100) . '...'; ?>
                                        </p>
                                        
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Price: <strong><?php echo formatCurrency($event['price']); ?></strong></small>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Capacity: <strong><?php echo $event['max_capacity']; ?></strong></small>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Booked: <strong><?php echo $event['current_bookings']; ?></strong></small>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Available: <strong><?php echo $event['available_tickets']; ?></strong></small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <div class="dropdown">
                                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Status
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                            <input type="hidden" name="status" value="active">
                                                            <button type="submit" name="update_status" class="dropdown-item">
                                                                <i class="fas fa-check me-2 text-success"></i>Active
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                            <input type="hidden" name="status" value="cancelled">
                                                            <button type="submit" name="update_status" class="dropdown-item">
                                                                <i class="fas fa-times me-2 text-danger"></i>Cancelled
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" name="update_status" class="dropdown-item">
                                                                <i class="fas fa-flag-checkered me-2 text-info"></i>Completed
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                <button type="submit" name="delete_event" class="btn btn-outline-danger btn-sm" 
                                                        onclick="return confirm('Are you sure you want to delete this event?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                        <h4>No events found</h4>
                        <p class="text-muted">Start by adding your first event!</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                            <i class="fas fa-plus me-2"></i>Add New Event
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Event Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Base Price (FCFA) *</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="price" name="price" step="1" min="0" required>
                                        <span class="input-group-text">FCFA</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date *</label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="time" class="form-label">Time *</label>
                                    <input type="time" class="form-control" id="time" name="time" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="venue" class="form-label">Venue *</label>
                                    <input type="text" class="form-control" id="venue" name="venue" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location *</label>
                                    <select class="form-control" id="location" name="location" required>
                                        <option value="">Select City</option>
                                        <?php foreach (getCameroonianCities() as $city): ?>
                                            <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="organizer_contact" class="form-label">Organizer Contact</label>
                                    <input type="email" class="form-control" id="organizer_contact" name="organizer_contact" placeholder="email@example.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_capacity" class="form-label">Max Capacity *</label>
                                    <input type="number" class="form-control" id="max_capacity" name="max_capacity" min="1" value="100" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Ticket Types:</strong> The system will automatically create three ticket types:
                            <ul class="mb-0 mt-2">
                                <li><strong>General:</strong> Base price</li>
                                <li><strong>VIP:</strong> 2x base price</li>
                                <li><strong>Student:</strong> 0.7x base price</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_event" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set minimum date to today
        document.getElementById('date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
