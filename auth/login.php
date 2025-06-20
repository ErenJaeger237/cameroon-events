<?php
require_once '../includes/db.php';

$error = '';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getCurrentUserRole();
    if ($role === 'admin') {
        redirect('../admin/dashboard.php');
    } else {
        redirect('../user/dashboard.php');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check user credentials
        $user = fetchOne("SELECT id, name, email, password, role FROM users WHERE email = ?", [$email]);

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect('../admin/dashboard.php', 'Welcome back, ' . $user['name'] . '!', 'success');
            } else {
                redirect('../user/dashboard.php', 'Welcome back, ' . $user['name'] . '!', 'success');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CameroonEvents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/african-theme.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg,
                var(--primary-blue) 0%,
                var(--secondary-blue) 50%,
                var(--accent-blue) 100%
            );
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Inter', sans-serif;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .demo-credentials {
            background: linear-gradient(135deg, rgba(0, 122, 61, 0.1), rgba(255, 203, 0, 0.1));
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(0, 122, 61, 0.2);
        }

        .welcome-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: var(--text-primary);
        }

        .african-icon {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-calendar-alt fa-3x mb-3" style="color: var(--primary-blue);"></i>
                        <h2 class="welcome-title">Welcome to CameroonEvents</h2>
                        <p class="text-muted">Sign in to your account</p>
                    </div>

                    <!-- Demo Credentials -->
                    <div class="demo-credentials">
                        <h6 class="fw-bold mb-2" style="color: var(--primary-blue);">
                            <i class="fas fa-info-circle me-2"></i>Demo Credentials
                        </h6>
                        <small style="color: var(--text-secondary);">
                            <strong>Admin:</strong> admin@cameroonevents.cm / admin123<br>
                            <strong>User:</strong> john@example.com / user123
                        </small>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php
                    $flash = getFlashMessage();
                    if ($flash):
                    ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label" style="color: var(--text-primary);">
                                <i class="fas fa-envelope me-2" style="color: var(--primary-blue);"></i>Email Address
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label" style="color: var(--text-primary);">
                                <i class="fas fa-lock me-2" style="color: var(--secondary-blue);"></i>Password
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">Don't have an account?
                            <a href="register.php" class="text-decoration-none" style="color: var(--primary-blue); font-weight: 600;">Create one here</a>
                        </p>
                        <a href="../index.php" class="text-muted text-decoration-none mt-2 d-inline-block">
                            <i class="fas fa-arrow-left me-1"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>