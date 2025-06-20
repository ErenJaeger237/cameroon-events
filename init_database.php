<?php
/**
 * Database Initialization Script for Railway Deployment
 * Run this once after deploying to Railway to set up the database
 */

require_once 'includes/db.php';

echo "<h2>🚀 CameroonEvents Database Initialization</h2>";
echo "<p>Setting up database for Railway deployment...</p>";

try {
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
        executeQuery($table_sql);
    }
    echo "<p>✅ Tables created successfully!</p>";
    
    // Create indexes
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_events_date ON events(date)",
        "CREATE INDEX IF NOT EXISTS idx_events_location ON events(location)",
        "CREATE INDEX IF NOT EXISTS idx_events_status ON events(status)",
        "CREATE INDEX IF NOT EXISTS idx_bookings_user ON bookings(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_bookings_event ON bookings(event_id)",
        "CREATE INDEX IF NOT EXISTS idx_bookings_reference ON bookings(booking_reference)",
        "CREATE INDEX IF NOT EXISTS idx_cart_user ON cart(user_id)"
    ];
    
    foreach ($indexes as $index_sql) {
        executeQuery($index_sql);
    }
    echo "<p>✅ Indexes created successfully!</p>";
    
    // Check if admin user exists
    $admin_exists = fetchOne("SELECT id FROM users WHERE email = 'admin@cameroonevents.cm'");
    
    if (!$admin_exists) {
        // Insert admin user
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        executeQuery("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')",
                    ['Cameroon Admin', 'admin@cameroonevents.cm', $admin_password]);
        echo "<p>✅ Admin user created!</p>";
    } else {
        echo "<p>ℹ️ Admin user already exists.</p>";
    }
    
    // Check if sample users exist
    $user_count = getCount("SELECT COUNT(*) FROM users WHERE role = 'user'");
    
    if ($user_count < 3) {
        // Insert sample users
        $user_password = password_hash('user123', PASSWORD_DEFAULT);
        $sample_users = [
            ['John Doe', 'john@example.com'],
            ['Jane Smith', 'jane@example.com'],
            ['Mike Johnson', 'mike@example.com']
        ];
        
        foreach ($sample_users as $user) {
            $existing = fetchOne("SELECT id FROM users WHERE email = ?", [$user[1]]);
            if (!$existing) {
                executeQuery("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')",
                           [$user[0], $user[1], $user_password]);
            }
        }
        echo "<p>✅ Sample users created!</p>";
    }
    
    // Check if sample events exist
    $event_count = getCount("SELECT COUNT(*) FROM events");
    
    if ($event_count < 3) {
        // Insert sample Cameroon events
        $sample_events = [
            [
                'Cameroon Music Festival 2024',
                'Annual music festival celebrating Cameroonian artists and culture with traditional and modern performances.',
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
                'Leading technology conference bringing together innovators, entrepreneurs, and tech enthusiasts across Central Africa.',
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
                'Celebration of Cameroonian traditions, crafts, food, and cultural performances from all regions.',
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
            executeQuery("INSERT INTO events (name, description, date, time, venue, location, organizer_contact, image, price, ticket_types, max_capacity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $event);
        }
        echo "<p>✅ Sample Cameroon events created!</p>";
    }
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>🎉 Database Setup Complete!</h3>";
    echo "<p><strong>Admin Login:</strong></p>";
    echo "<p>Email: admin@cameroonevents.cm</p>";
    echo "<p>Password: admin123</p>";
    echo "<p><strong>Sample User Login:</strong></p>";
    echo "<p>Email: john@example.com</p>";
    echo "<p>Password: user123</p>";
    echo "</div>";
    
    echo "<p><a href='index.php' style='background: #007A3D; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Go to Homepage</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
