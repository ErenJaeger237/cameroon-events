<?php
echo "<h1>Complete PHP/MySQL Diagnostic</h1>";

echo "<h2>1. PHP Configuration</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>PHP INI File:</strong> " . php_ini_loaded_file() . "</p>";
echo "<p><strong>Extension Dir:</strong> " . ini_get('extension_dir') . "</p>";

echo "<h2>2. Extension Status</h2>";
$extensions = ['pdo', 'pdo_mysql', 'mysqli', 'mysql'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    $color = $loaded ? 'green' : 'red';
    $status = $loaded ? '✓ LOADED' : '✗ NOT LOADED';
    echo "<p style='color: $color;'><strong>$ext:</strong> $status</p>";
}

echo "<h2>3. All Loaded Extensions</h2>";
$loaded_extensions = get_loaded_extensions();
sort($loaded_extensions);
echo "<p>Total extensions loaded: " . count($loaded_extensions) . "</p>";
echo "<details><summary>Click to see all loaded extensions</summary>";
echo "<ul>";
foreach ($loaded_extensions as $ext) {
    echo "<li>$ext</li>";
}
echo "</ul></details>";

echo "<h2>4. PDO Drivers</h2>";
if (extension_loaded('pdo')) {
    $drivers = PDO::getAvailableDrivers();
    if (empty($drivers)) {
        echo "<p style='color: red;'>No PDO drivers available</p>";
    } else {
        echo "<p style='color: green;'>Available PDO drivers:</p>";
        echo "<ul>";
        foreach ($drivers as $driver) {
            echo "<li>$driver</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color: red;'>PDO extension not loaded</p>";
}

echo "<h2>5. Extension Files Check</h2>";
$ext_dir = ini_get('extension_dir');
if ($ext_dir === './') {
    $ext_dir = dirname(php_ini_loaded_file()) . '/ext';
}
echo "<p><strong>Looking in:</strong> $ext_dir</p>";

$mysql_files = [
    'php_pdo_mysql.dll',
    'php_mysqli.dll',
    'pdo_mysql.dll',
    'mysqli.dll',
    'php_pdo_mysql.so',
    'php_mysqli.so',
    'pdo_mysql.so',
    'mysqli.so'
];

foreach ($mysql_files as $file) {
    $path = $ext_dir . DIRECTORY_SEPARATOR . $file;
    $exists = file_exists($path);
    $color = $exists ? 'green' : 'red';
    $status = $exists ? '✓ EXISTS' : '✗ NOT FOUND';
    echo "<p style='color: $color;'>$file: $status</p>";
}

echo "<h2>6. Direct Database Test</h2>";
echo "<h3>Testing MySQLi:</h3>";
if (function_exists('mysqli_connect')) {
    echo "<p style='color: green;'>✓ mysqli_connect function exists</p>";
    
    // Test connection
    $connection = @mysqli_connect('localhost', 'root', '@G00db0y', 'event_booking');
    if ($connection) {
        echo "<p style='color: green;'>✓ MySQLi connection successful</p>";
        mysqli_close($connection);
    } else {
        echo "<p style='color: red;'>✗ MySQLi connection failed: " . mysqli_connect_error() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ mysqli_connect function not available</p>";
}

echo "<h3>Testing PDO:</h3>";
if (class_exists('PDO')) {
    echo "<p style='color: green;'>✓ PDO class exists</p>";
    
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=event_booking', 'root', '@G00db0y');
        echo "<p style='color: green;'>✓ PDO connection successful</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ PDO connection failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ PDO class not available</p>";
}

echo "<h2>7. PHP Info</h2>";
echo "<p><a href='phpinfo_full.php' target='_blank'>View Complete PHP Info</a></p>";

// Create phpinfo file
file_put_contents('phpinfo_full.php', '<?php phpinfo(); ?>');
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
details { margin: 10px 0; }
</style>
