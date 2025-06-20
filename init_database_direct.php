<?php
/**
 * Direct Railway Database Initialization
 * Using hardcoded Railway connection details
 */

echo "<h2>🚀 CameroonEvents Database Initialization (Direct)</h2>";
echo "<p>Setting up database using direct Railway connection...</p>";

// Direct Railway MySQL connection
$mysql_url = "mysql://root:JUbpLBZCskDyPLwEceEQffOspVXAiJOx@metro.proxy.rlwy.net:41333/railway";
$url = parse_url($mysql_url);

try {
    $pdo = new PDO(
        "mysql:host={$url['host']};port={$url['port']};dbname=" . ltrim($url['path'], '/') . ";charset=utf8mb4",
        $url['user'],
        $url['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ]
    );
    
    echo "<p>✅ Connected to Railway MySQL!</p>";
    
    // Create tables
    $tables = [
        // Users table
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Events table
        "CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            date DATE NOT NULL,
            time TIME NOT NULL,
            venue VARCHAR(200) NOT NULL,
            location VARCHAR(200) NOT NULL,
            organizer_contact VARCHAR(100),
            image VARCHAR(255),
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            ticket_types JSON,
            max_capacity INT DEFAULT 100,
            current_bookings INT DEFAULT 0,
            status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Cart table
        "CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            event_id INT NOT NULL,
            ticket_type VARCHAR(50) DEFAULT 'general',
            quantity INT NOT NULL DEFAULT 1,
            price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
        )",
        
        // Bookings table
        "CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            event_id INT NOT NULL,
            ticket_type VARCHAR(50) DEFAULT 'general',
            quantity INT NOT NULL DEFAULT 1,
            total_amount DECIMAL(10,2) NOT NULL,
            attendee_name VARCHAR(100) NOT NULL,
            booking_reference VARCHAR(20) UNIQUE NOT NULL,
            status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
        )",
        
        // Payments table
        "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            booking_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
            transaction_id VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
        )"
    ];
    
    foreach ($tables as $table_sql) {
        $pdo->exec($table_sql);
    }
    echo "<p>✅ Tables created successfully!</p>";
    
    // Create admin user
    $admin_exists = $pdo->query("SELECT id FROM users WHERE email = 'admin@cameroonevents.cm'")->fetch();
    
    if (!$admin_exists) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute(['Cameroon Admin', 'admin@cameroonevents.cm', $admin_password]);
        echo "<p>✅ Admin user created!</p>";
    }
    
    // Create sample users
    $user_password = password_hash('user123', PASSWORD_DEFAULT);
    $sample_users = [
        ['John Doe', 'john@example.com'],
        ['Jane Smith', 'jane@example.com'],
        ['Mike Johnson', 'mike@example.com']
    ];
    
    foreach ($sample_users as $user) {
        $existing = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $existing->execute([$user[1]]);
        if (!$existing->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$user[0], $user[1], $user_password]);
        }
    }
    echo "<p>✅ Sample users created!</p>";
    
    // Create sample events
    $sample_events = [
        [
            'Cameroon Music Festival 2024',
            'Annual music festival celebrating Cameroonian artists and culture.',
            '2024-07-15',
            '18:00:00',
            'Palais des Sports',
            'Yaoundé',
            'info@cameroonmusic.cm',
            'music-festival.jpg',
            15000,
            '{"general": 15000, "vip": 30000, "student": 8000}',
            5000
        ],
        [
            'Tech Innovation Summit Cameroon',
            'Leading technology conference in Central Africa.',
            '2024-08-20',
            '09:00:00',
            'Douala Conference Center',
            'Douala',
            'contact@techsummit.cm',
            'tech-summit.jpg',
            25000,
            '{"general": 25000, "premium": 50000, "student": 12000}',
            1000
        ],
        [
            'Cameroon Cultural Heritage Day',
            'Celebration of Cameroonian traditions and culture.',
            '2024-09-10',
            '10:00:00',
            'Bamenda Cultural Center',
            'Bamenda',
            'heritage@culture.cm',
            'cultural-day.jpg',
            5000,
            '{"general": 5000, "family": 18000, "child": 2000}',
            2000
        ]
    ];
    
    foreach ($sample_events as $event) {
        $existing = $pdo->prepare("SELECT id FROM events WHERE name = ?");
        $existing->execute([$event[0]]);
        if (!$existing->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO events (name, description, date, time, venue, location, organizer_contact, image, price, ticket_types, max_capacity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute($event);
        }
    }
    echo "<p>✅ Sample Cameroon events created!</p>";
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>🎉 Database Setup Complete!</h3>";
    echo "<p><strong>🔑 Admin Login:</strong></p>";
    echo "<p>Email: admin@cameroonevents.cm</p>";
    echo "<p>Password: admin123</p>";
    echo "<p><strong>👤 Sample User Login:</strong></p>";
    echo "<p>Email: john@example.com</p>";
    echo "<p>Password: user123</p>";
    echo "</div>";
    
    echo "<p><a href='index.php' style='background: #007A3D; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Go to Homepage</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
