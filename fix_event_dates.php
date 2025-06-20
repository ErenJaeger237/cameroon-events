<?php
/**
 * Fix Event Dates - Update to Current/Future Dates
 */

// Direct Railway connection
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'railway.app') !== false) {
    // Railway environment
    $mysql_url = "mysql://root:JUbpLBZCskDyPLwEceEQffOspVXAiJOx@metro.proxy.rlwy.net:41333/railway";
    $url = parse_url($mysql_url);
    
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
} else {
    // Local environment
    require_once 'includes/db.php';
    global $pdo;
}

echo "<h2>🔧 Fixing Event Dates</h2>";
echo "<p>Updating events to have current/future dates...</p>";

try {
    // Get current date
    $today = date('Y-m-d');
    echo "<p>Today's date: $today</p>";
    
    // Check existing events
    $events = $pdo->query("SELECT id, name, date FROM events ORDER BY date")->fetchAll();
    
    echo "<h3>Current Events:</h3>";
    echo "<ul>";
    foreach ($events as $event) {
        echo "<li><strong>" . htmlspecialchars($event['name']) . "</strong> - " . $event['date'] . "</li>";
    }
    echo "</ul>";
    
    // Update events with future dates
    $updates = [
        [
            'name' => 'Cameroon Music Festival 2024',
            'new_date' => date('Y-m-d', strtotime('+30 days')), // 30 days from now
            'new_time' => '18:00:00'
        ],
        [
            'name' => 'Tech Innovation Summit Cameroon',
            'new_date' => date('Y-m-d', strtotime('+45 days')), // 45 days from now
            'new_time' => '09:00:00'
        ],
        [
            'name' => 'Cameroon Cultural Heritage Day',
            'new_date' => date('Y-m-d', strtotime('+60 days')), // 60 days from now
            'new_time' => '10:00:00'
        ]
    ];
    
    echo "<h3>Updating Events:</h3>";
    
    foreach ($updates as $update) {
        $stmt = $pdo->prepare("UPDATE events SET date = ?, time = ? WHERE name = ?");
        $result = $stmt->execute([$update['new_date'], $update['new_time'], $update['name']]);
        
        if ($result) {
            echo "<p>✅ Updated <strong>" . htmlspecialchars($update['name']) . "</strong> to " . $update['new_date'] . " at " . $update['new_time'] . "</p>";
        } else {
            echo "<p>❌ Failed to update " . htmlspecialchars($update['name']) . "</p>";
        }
    }
    
    // Add some additional future events
    $new_events = [
        [
            'Douala Business Expo 2025',
            'Major business exhibition showcasing Cameroonian enterprises and international partnerships.',
            date('Y-m-d', strtotime('+15 days')),
            '08:30:00',
            'Douala International Conference Center',
            'Douala',
            'expo@douala-business.cm',
            'business-expo.jpg',
            20000,
            '{"general": 20000, "business": 35000, "student": 10000}',
            3000
        ],
        [
            'Yaoundé Food Festival',
            'Celebration of Cameroonian cuisine featuring traditional dishes from all regions.',
            date('Y-m-d', strtotime('+20 days')),
            '11:00:00',
            'Yaoundé Municipal Stadium',
            'Yaoundé',
            'food@yaounde-festival.cm',
            'food-festival.jpg',
            8000,
            '{"general": 8000, "family": 25000, "child": 3000}',
            5000
        ],
        [
            'Bamenda Arts & Crafts Fair',
            'Showcase of traditional Cameroonian arts, crafts, and cultural performances.',
            date('Y-m-d', strtotime('+75 days')),
            '09:00:00',
            'Bamenda Commercial Avenue',
            'Bamenda',
            'arts@bamenda-crafts.cm',
            'arts-fair.jpg',
            12000,
            '{"general": 12000, "artisan": 8000, "student": 5000}',
            2500
        ]
    ];
    
    echo "<h3>Adding New Future Events:</h3>";
    
    foreach ($new_events as $event) {
        // Check if event already exists
        $existing = $pdo->prepare("SELECT id FROM events WHERE name = ?");
        $existing->execute([$event[0]]);
        
        if (!$existing->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO events (name, description, date, time, venue, location, organizer_contact, image, price, ticket_types, max_capacity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute($event);
            
            if ($result) {
                echo "<p>✅ Added <strong>" . htmlspecialchars($event[0]) . "</strong> on " . $event[2] . "</p>";
            } else {
                echo "<p>❌ Failed to add " . htmlspecialchars($event[0]) . "</p>";
            }
        } else {
            echo "<p>ℹ️ Event <strong>" . htmlspecialchars($event[0]) . "</strong> already exists</p>";
        }
    }
    
    // Show updated events list
    $updated_events = $pdo->query("SELECT id, name, date, time, location, price FROM events WHERE date >= CURDATE() ORDER BY date")->fetchAll();
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>✅ Updated Events List (Future Events Only):</h3>";
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #007A3D; color: white;'>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Event Name</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Date</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Time</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Location</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Price</th>";
    echo "</tr>";
    
    foreach ($updated_events as $event) {
        echo "<tr>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($event['name']) . "</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . date('M j, Y', strtotime($event['date'])) . "</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . date('g:i A', strtotime($event['time'])) . "</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($event['location']) . "</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . number_format($event['price'], 0) . " FCFA</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    echo "<p><a href='index.php' style='background: #007A3D; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Go to Homepage</a></p>";
    echo "<p><a href='events/list.php' style='background: #CE1126; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🎪 View Events Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
