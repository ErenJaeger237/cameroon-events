<?php
/**
 * Database Connection Configuration
 * Online Event Booking System
 */

// Database configuration - Railway compatible
// Check if we're on Railway (production) or local development
if (isset($_ENV['MYSQL_URL'])) {
    // Railway production environment - parse MYSQL_URL
    $url = parse_url($_ENV['MYSQL_URL']);
    define('DB_HOST', $url['host']);
    define('DB_NAME', ltrim($url['path'], '/'));
    define('DB_USER', $url['user']);
    define('DB_PASS', $url['pass']);
    define('DB_PORT', $url['port'] ?? '3306');
} elseif (isset($_ENV['MYSQLHOST'])) {
    // Alternative Railway format
    define('DB_HOST', $_ENV['MYSQLHOST']);
    define('DB_NAME', $_ENV['MYSQLDATABASE'] ?? 'railway');
    define('DB_USER', $_ENV['MYSQLUSER'] ?? 'root');
    define('DB_PASS', $_ENV['MYSQLPASSWORD'] ?? '');
    define('DB_PORT', $_ENV['MYSQLPORT'] ?? '3306');
} else {
    // Local development environment
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'event_booking');
    define('DB_USER', 'root');
    define('DB_PASS', '@G00db0y');
    define('DB_PORT', '3306');
}

// Create database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to get database connection
function getDB() {
    global $pdo;
    return $pdo;
}

// Function to execute prepared statements safely
function executeQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        return false;
    }
}

// Function to get single record
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

// Function to get multiple records
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : false;
}

// Function to get count of records
function getCount($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchColumn() : 0;
}

// Function to get last inserted ID
function getLastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Helper function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Helper function to get current user role
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

// Helper function to redirect with message
function redirect($url, $message = '', $type = 'info') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit();
}

// Helper function to display flash messages
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Helper function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Helper function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Helper function to generate booking reference
function generateBookingReference() {
    return 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

// Helper function to format FCFA currency
function formatCurrency($amount) {
    return number_format($amount, 0) . ' FCFA';
}

// Helper function to get Cameroonian cities
function getCameroonianCities() {
    return [
        'Yaoundé', 'Douala', 'Bamenda', 'Bafoussam', 'Garoua', 'Maroua',
        'Ngaoundéré', 'Bertoua', 'Ebolowa', 'Kumba', 'Edéa', 'Limbe',
        'Dschang', 'Kribi', 'Buea', 'Foumban', 'Mbalmayo', 'Sangmélima'
    ];
}
?>