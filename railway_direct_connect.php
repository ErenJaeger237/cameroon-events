<?php
/**
 * Direct Railway MySQL Connection Test
 * Using your specific connection details
 */

echo "<h2>🚀 Direct Railway MySQL Connection</h2>";

// Your Railway MySQL connection details
$mysql_url = "mysql://root:JUbpLBZCskDyPLwEceEQffOspVXAiJOx@metro.proxy.rlwy.net:41333/railway";

echo "<p><strong>Using Public URL:</strong> $mysql_url</p>";

// Parse the URL
$url = parse_url($mysql_url);
$host = $url['host'];
$dbname = ltrim($url['path'], '/');
$username = $url['user'];
$password = $url['pass'];
$port = $url['port'];

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
    echo "<p>Successfully connected to Railway MySQL database.</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "<p><strong>MySQL Version:</strong> " . $result['version'] . "</p>";
    
    // Create a simple test table
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_table (id INT AUTO_INCREMENT PRIMARY KEY, message VARCHAR(100))");
    $pdo->exec("INSERT INTO test_table (message) VALUES ('Railway connection working!')");
    
    $stmt = $pdo->query("SELECT * FROM test_table LIMIT 1");
    $test_result = $stmt->fetch();
    echo "<p><strong>Test Query:</strong> " . $test_result['message'] . "</p>";
    
    echo "<p><a href='init_database_direct.php' style='background: #007A3D; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Initialize Full Database</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ Connection Failed</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
    echo "</div>";
}

echo "<p><a href='index.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Back to Homepage</a></p>";
?>
