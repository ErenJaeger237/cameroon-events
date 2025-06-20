<?php
require_once 'includes/db.php';

echo "<h2>Login Debug Tool</h2>";

// Check all users
echo "<h3>All Users in Database:</h3>";
$users = fetchAll("SELECT id, name, email, role FROM users");

if ($users) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
    foreach ($users as $user) {
        $style = $user['role'] === 'admin' ? 'background-color: #ffffcc;' : '';
        echo "<tr style='$style'>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td><strong>" . $user['role'] . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No users found!</p>";
}

// Check specifically for admin users
echo "<h3>Admin Users:</h3>";
$admins = fetchAll("SELECT id, name, email, role FROM users WHERE role = 'admin'");

if ($admins) {
    foreach ($admins as $admin) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px;'>";
        echo "<strong>Admin Found:</strong><br>";
        echo "Email: " . htmlspecialchars($admin['email']) . "<br>";
        echo "Name: " . htmlspecialchars($admin['name']) . "<br>";
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>No admin users found!</p>";
    echo "<p>Creating admin user...</p>";

    // Create admin user
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $result = executeQuery("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')",
                          ['Cameroon Admin', 'admin@cameroonevents.cm', $hashed_password]);

    if ($result) {
        echo "<p style='color: green;'>Admin user created successfully!</p>";
        echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb;'>";
        echo "<strong>Login Credentials:</strong><br>";
        echo "<strong>Email:</strong> admin@cameroonevents.cm<br>";
        echo "<strong>Password:</strong> admin123";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>Failed to create admin user!</p>";
    }
}

// Test password verification
echo "<h3>Password Test:</h3>";
$test_email = 'admin@cameroonevents.cm';
$test_password = 'admin123';

$user = fetchOne("SELECT id, name, email, password, role FROM users WHERE email = ?", [$test_email]);

if ($user) {
    echo "<p>User found: " . htmlspecialchars($user['email']) . "</p>";
    echo "<p>Role: " . $user['role'] . "</p>";

    if (password_verify($test_password, $user['password'])) {
        echo "<p style='color: green;'>Password verification: SUCCESS</p>";
    } else {
        echo "<p style='color: red;'>Password verification: FAILED</p>";
        echo "<p>Stored hash: " . substr($user['password'], 0, 50) . "...</p>";

        // Try to fix the password
        echo "<p>Attempting to fix password...</p>";
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $update_result = executeQuery("UPDATE users SET password = ? WHERE email = ?", [$new_hash, $test_email]);

        if ($update_result) {
            echo "<p style='color: green;'>Password updated successfully! Try logging in again.</p>";
        } else {
            echo "<p style='color: red;'>Failed to update password.</p>";
        }
    }
} else {
    echo "<p style='color: red;'>User not found with email: $test_email</p>";
}

// Check session functionality
echo "<h3>Session Test:</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color: green;'>Sessions are working</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
} else {
    echo "<p style='color: red;'>Sessions not working</p>";
}

// Force password reset for admin
echo "<h3>Force Password Reset:</h3>";
if (isset($_GET['reset_admin']) && $_GET['reset_admin'] === 'yes') {
    echo "<p>Forcing admin password reset...</p>";
    $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $update_result = executeQuery("UPDATE users SET password = ? WHERE email = ?",
                                 [$new_hash, 'admin@cameroonevents.cm']);

    if ($update_result) {
        echo "<p style='color: green; font-weight: bold;'>✅ Admin password has been reset!</p>";

        // Verify it worked
        $verify_user = fetchOne("SELECT password FROM users WHERE email = ?", ['admin@cameroonevents.cm']);
        if ($verify_user && password_verify('admin123', $verify_user['password'])) {
            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ Login Ready!</h4>";
            echo "<strong>Email:</strong> admin@cameroonevents.cm<br>";
            echo "<strong>Password:</strong> admin123<br>";
            echo "<p><a href='auth/login.php' style='color: #155724; font-weight: bold;'>→ Try Login Now</a></p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Password verification still failed!</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Failed to update password!</p>";
    }
} else {
    echo "<p><a href='?reset_admin=yes' style='background: #dc3545; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔧 Reset Admin Password</a></p>";
}

echo "<hr>";
echo "<p><a href='auth/login.php'>Go to Login Page</a></p>";
echo "<p><a href='index.php'>Go to Home Page</a></p>";
?>
