<?php
require_once '../includes/db.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php', 'Please log in as admin to access this page.', 'error');
}

$message = '';
$message_type = '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    $event_id = $_POST['event_id'];
    $upload_dir = '../assets/images/events/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['event_image']['tmp_name'];
        $file_name = $_FILES['event_image']['name'];
        $file_size = $_FILES['event_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_ext, $allowed_types)) {
            $message = 'Invalid file type. Please upload JPG, PNG, GIF, or WebP images.';
            $message_type = 'error';
        } elseif ($file_size > 5 * 1024 * 1024) { // 5MB limit
            $message = 'File too large. Maximum size is 5MB.';
            $message_type = 'error';
        } else {
            // Generate unique filename
            $new_filename = 'event_' . $event_id . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Update database
                $result = executeQuery("UPDATE events SET image = ? WHERE id = ?", [$new_filename, $event_id]);
                if ($result) {
                    $message = 'Image uploaded successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Failed to update database.';
                    $message_type = 'error';
                }
            } else {
                $message = 'Failed to upload image.';
                $message_type = 'error';
            }
        }
    } else {
        $message = 'Please select an image to upload.';
        $message_type = 'error';
    }
}

// Get all events
$events = fetchAll("SELECT id, name, image, date, location FROM events ORDER BY date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Event Images - CameroonEvents Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/african-theme.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #fafafa;
        }
        
        .admin-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .admin-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 2rem;
        }
        
        .event-card {
            transition: transform 0.3s ease;
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            background: white;
            margin-bottom: 2rem;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
        }
        
        .current-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: var(--primary-gradient);
        }
        
        .no-image {
            width: 100%;
            height: 200px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .upload-section {
            padding: 1.5rem;
        }
    </style>
</head>
<body class="professional-pattern">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-calendar-alt me-2"></i>CameroonEvents Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <a href="../auth/logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <h1 class="admin-title">
                <i class="fas fa-images me-3" style="color: var(--secondary-blue);"></i>
                Event Image Management
            </h1>
            <p class="mb-0">Upload and manage images for your events</p>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="event-card">
                        <!-- Current Image -->
                        <?php if ($event['image'] && file_exists("../assets/images/events/" . $event['image'])): ?>
                            <img src="../assets/images/events/<?php echo htmlspecialchars($event['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($event['name']); ?>" 
                                 class="current-image">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Upload Section -->
                        <div class="upload-section">
                            <h5 class="text-primary mb-2"><?php echo htmlspecialchars($event['name']); ?></h5>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('M j, Y', strtotime($event['date'])); ?>
                                <br>
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($event['location']); ?>
                            </p>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                
                                <div class="mb-3">
                                    <input type="file" class="form-control" name="event_image" 
                                           accept="image/*" required>
                                    <div class="form-text">
                                        JPG, PNG, GIF, WebP (Max: 5MB)
                                    </div>
                                </div>
                                
                                <button type="submit" name="upload_image" class="btn btn-primary w-100">
                                    <i class="fas fa-upload me-2"></i>
                                    <?php echo $event['image'] ? 'Update Image' : 'Upload Image'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($events)): ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x mb-3" style="color: var(--african-terracotta);"></i>
                <h4 style="color: var(--text-primary);">No Events Found</h4>
                <p class="text-muted">Create some events first, then come back to add images.</p>
                <a href="events.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Event
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
