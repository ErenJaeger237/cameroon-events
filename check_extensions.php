<?php
echo "<h2>PHP Extensions Check</h2>";

echo "<h3>PDO Extensions:</h3>";
if (extension_loaded('pdo')) {
    echo "<p style='color: green;'>✓ PDO is loaded</p>";
    
    if (extension_loaded('pdo_mysql')) {
        echo "<p style='color: green;'>✓ PDO MySQL is loaded</p>";
    } else {
        echo "<p style='color: red;'>✗ PDO MySQL is NOT loaded</p>";
    }
} else {
    echo "<p style='color: red;'>✗ PDO is NOT loaded</p>";
}

echo "<h3>MySQLi Extension:</h3>";
if (extension_loaded('mysqli')) {
    echo "<p style='color: green;'>✓ MySQLi is loaded</p>";
} else {
    echo "<p style='color: red;'>✗ MySQLi is NOT loaded</p>";
}

echo "<h3>Available PDO Drivers:</h3>";
if (extension_loaded('pdo')) {
    $drivers = PDO::getAvailableDrivers();
    if (in_array('mysql', $drivers)) {
        echo "<p style='color: green;'>✓ MySQL driver available</p>";
    } else {
        echo "<p style='color: red;'>✗ MySQL driver NOT available</p>";
    }
    echo "<p>Available drivers: " . implode(', ', $drivers) . "</p>";
} else {
    echo "<p style='color: red;'>PDO not loaded, cannot check drivers</p>";
}

echo "<h3>PHP Info (MySQL section):</h3>";
echo "<p><a href='phpinfo.php' target='_blank'>Click here to view full PHP info</a></p>";
?>

<!-- Create phpinfo file -->
<?php
file_put_contents('phpinfo.php', '<?php phpinfo(); ?>');
echo "<p style='color: green;'>Created phpinfo.php file</p>";
?>
