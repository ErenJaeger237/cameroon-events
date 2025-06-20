<?php
require_once 'includes/db.php';

echo "<h2>Admin Password Fix Tool</h2>";

// Check if admin exists
$admin = fetchOne("SELECT id, name, email, password, role FROM users WHERE email = ?", ['admin@cameroonevents.cm']);

if ($admin) {
    echo "<p>Admin user found: " . htmlspecialchars($admin['email']) . "</p>";
    
    // Test current password
    if (password_verify('admin123', $admin['password'])) {
        echo "<p style='color: green;'>Current password is correct! Login should work.</p>";
    } else {
        echo "<p style='color: red;'>Current password is incorrect. Fixing...</p>";
        
        // Generate new password hash
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        
        // Update password
        $result = executeQuery("UPDATE users SET password = ? WHERE email = ?", 
                              [$new_hash, 'admin@cameroonevents.cm']);
        
        if ($result) {
            echo "<p style='color: green;'>✅ Password updated successfully!</p>";
            
            // Verify the fix worked
            $updated_admin = fetchOne("SELECT password FROM users WHERE email = ?", ['admin@cameroonevents.cm']);
            if (password_verify('admin123', $updated_admin['password'])) {
                echo "<p style='color: green;'>✅ Password verification confirmed working!</p>";
                echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>✅ Admin Login Fixed!</h4>";
                echo "<strong>Email:</strong> admin@cameroonevents.cm<br>";
                echo "<strong>Password:</strong> admin123<br>";
                echo "<p><a href='auth/login.php' style='color: #155724; font-weight: bold;'>→ Go to Login Page</a></p>";
                echo "</div>";
            } else {
                echo "<p style='color: red;'>❌ Password verification still failed after update!</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Failed to update password in database!</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Admin user not found!</p>";
    echo "<p>Creating new admin user...</p>";
    
    // Create admin user
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $result = executeQuery("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')",
                          ['Cameroon Admin', 'admin@cameroonevents.cm', $hashed_password]);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Admin user created successfully!</p>";
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ Admin User Created!</h4>";
        echo "<strong>Email:</strong> admin@cameroonevents.cm<br>";
        echo "<strong>Password:</strong> admin123<br>";
        echo "<p><a href='auth/login.php' style='color: #155724; font-weight: bold;'>→ Go to Login Page</a></p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create admin user!</p>";
    }
}

echo "<hr>";
echo "<p><a href='debug_login.php'>← Back to Debug Page</a></p>";
echo "<p><a href='auth/login.php'>→ Go to Login Page</a></p>";
?>
