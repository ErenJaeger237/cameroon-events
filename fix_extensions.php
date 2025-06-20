<?php
echo "<h2>PHP Extensions Fix Guide</h2>";

echo "<h3>Current PHP Configuration:</h3>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Configuration File:</strong> " . php_ini_loaded_file() . "</p>";

echo "<h3>Extension Status:</h3>";
$extensions = ['pdo', 'pdo_mysql', 'mysqli'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    $color = $loaded ? 'green' : 'red';
    $status = $loaded ? '✓ LOADED' : '✗ NOT LOADED';
    echo "<p style='color: $color;'>$ext: $status</p>";
}

echo "<h3>What to Add to php.ini:</h3>";
echo "<div style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
echo "<p>Add these lines to your php.ini file in the extensions section:</p>";
echo "<pre>";
echo "extension=pdo_mysql\n";
echo "extension=mysqli\n";
echo "</pre>";
echo "</div>";

echo "<h3>Steps to Fix:</h3>";
echo "<ol>";
echo "<li>Open XAMPP Control Panel</li>";
echo "<li>Click 'Config' next to Apache → 'PHP (php.ini)'</li>";
echo "<li>Search for 'extension=' (find any existing extension line)</li>";
echo "<li>Add the two lines above near other extension lines</li>";
echo "<li>Save the file</li>";
echo "<li>Stop and Start Apache in XAMPP</li>";
echo "<li>Refresh this page to check if it worked</li>";
echo "</ol>";

echo "<h3>Alternative Method:</h3>";
echo "<p>If you can't find the right place, look for these sections in php.ini:</p>";
echo "<ul>";
echo "<li>[PHP] section</li>";
echo "<li>Dynamic Extensions section</li>";
echo "<li>Windows Extensions section</li>";
echo "</ul>";

echo "<h3>Files to Check:</h3>";
$php_dir = dirname(php_ini_loaded_file());
$ext_dir = $php_dir . DIRECTORY_SEPARATOR . 'ext';
echo "<p>Extension directory should be: <code>$ext_dir</code></p>";

if (is_dir($ext_dir)) {
    echo "<p style='color: green;'>✓ Extension directory exists</p>";
    $mysql_files = glob($ext_dir . DIRECTORY_SEPARATOR . '*mysql*');
    if ($mysql_files) {
        echo "<p>MySQL extension files found:</p>";
        echo "<ul>";
        foreach ($mysql_files as $file) {
            echo "<li>" . basename($file) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>No MySQL extension files found</p>";
    }
} else {
    echo "<p style='color: red;'>Extension directory not found</p>";
}

echo "<hr>";
echo "<p><a href='test.php'>Test Database Connection</a></p>";
?>
