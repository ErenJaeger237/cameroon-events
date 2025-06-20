<?php
/**
 * Railway MySQL Connection Test
 */

echo "<h2>🔧 Railway MySQL Connection Test</h2>";

// Display environment variables
echo "<h3>Environment Variables:</h3>";
echo "<ul>";
echo "<li><strong>MYSQL_URL:</strong> " . ($_ENV['MYSQL_URL'] ?? 'NOT SET') . "</li>";
echo "<li><strong>MYSQL_DATABASE:</strong> " . ($_ENV['MYSQL_DATABASE'] ?? 'NOT SET') . "</li>";
echo "<li><strong>MYSQL_ROOT_PASSWORD:</strong> " . (isset($_ENV['MYSQL_ROOT_PASSWORD']) ? '[SET]' : 'NOT SET') . "</li>";
echo "<li><strong>MYSQL_PUBLIC_URL:</strong> " . ($_ENV['MYSQL_PUBLIC_URL'] ?? 'NOT SET') . "</li>";
echo "</ul>";

// Check if Railway environment
if (isset($_ENV['MYSQL_URL'])) {
    echo "<p>✅ Railway environment detected (MYSQL_URL format)</p>";

    // Parse Railway MYSQL_URL
    $url = parse_url($_ENV['MYSQL_URL']);
    $host = $url['host'];
    $dbname = ltrim($url['path'], '/');
    $username = $url['user'];
    $password = $url['pass'];
    $port = $url['port'] ?? '3306';

} elseif (isset($_ENV['MYSQLHOST'])) {
    echo "<p>✅ Railway environment detected (individual variables)</p>";

    $host = $_ENV['MYSQLHOST'];
    $dbname = $_ENV['MYSQLDATABASE'] ?? 'railway';
    $username = $_ENV['MYSQLUSER'] ?? 'root';
    $password = $_ENV['MYSQLPASSWORD'] ?? '';
    $port = $_ENV['MYSQLPORT'] ?? '3306';

} else {
    echo "<p>❌ Railway environment NOT detected - using local settings</p>";

    $host = 'localhost';
    $dbname = 'event_booking';
    $username = 'root';
    $password = '@G00db0y';
    $port = '3306';
}

echo "<h3>Connection Details:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> $host</li>";
echo "<li><strong>Database:</strong> $dbname</li>";
echo "<li><strong>Username:</strong> $username</li>";
echo "<li><strong>Port:</strong> $port</li>";
echo "</ul>";

// Test connection
echo "<h3>Connection Test:</h3>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    echo "<p>Trying to connect with DSN: $dsn</p>";
    
    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ]
    );
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ Connection Successful!</h4>";
    echo "<p>Successfully connected to MySQL database.</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "<p><strong>MySQL Version:</strong> " . $result['version'] . "</p>";
    
    echo "<p><a href='init_database.php' style='background: #007A3D; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Initialize Database</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ Connection Failed</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
    echo "</div>";
    
    echo "<h3>🔧 Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li>Check if MySQL service is running in Railway dashboard</li>";
    echo "<li>Verify environment variables are set in your PHP app service</li>";
    echo "<li>Wait 2-3 minutes for MySQL to fully start</li>";
    echo "<li>Try redeploying your PHP application</li>";
    echo "</ol>";
}

echo "<p><a href='index.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Back to Homepage</a></p>";
?>
