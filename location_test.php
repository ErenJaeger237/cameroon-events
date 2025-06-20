<?php
echo "<h1>File Location Test</h1>";
echo "<p><strong>Current file path:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Current directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Document root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

if (strpos(__FILE__, 'htdocs') !== false) {
    echo "<p style='color: green;'>✓ You are accessing files from htdocs (CORRECT)</p>";
} else {
    echo "<p style='color: red;'>✗ You are NOT accessing files from htdocs</p>";
    echo "<p>You need to copy files to: " . $_SERVER['DOCUMENT_ROOT'] . "/OnlineEventBookingSystem_FULL/</p>";
}

echo "<h2>Database Test</h2>";
try {
    require_once 'includes/db.php';
    echo "<p style='color: green;'>✓ Database connection successful from this location</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}
?>
