<?php
echo "<h1>PHP Test</h1>";
echo "<p>If you can see this, PHP is working!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test database connection
try {
    require_once 'includes/db.php';
    echo "<p style='color: green;'>Database connection: SUCCESS</p>";
    
    // Test query
    $result = fetchOne("SELECT COUNT(*) as count FROM users");
    echo "<p>Users in database: " . $result['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database connection: FAILED</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
