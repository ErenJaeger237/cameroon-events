<?php
/**
 * Add Sample Images to Events
 * This script downloads sample images and assigns them to events
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

echo "<h2>🖼️ Adding Sample Images to Events</h2>";

// Create images directory if it doesn't exist
$images_dir = 'assets/images/events/';
if (!file_exists($images_dir)) {
    mkdir($images_dir, 0777, true);
    echo "<p>✅ Created images directory</p>";
}

// Sample images mapping (using placeholder services)
$sample_images = [
    'Cameroon Music Festival 2024' => [
        'filename' => 'music_festival.jpg',
        'url' => 'https://picsum.photos/800/600?random=1'
    ],
    'Tech Innovation Summit Cameroon' => [
        'filename' => 'tech_summit.jpg',
        'url' => 'https://picsum.photos/800/600?random=2'
    ],
    'Cameroon Cultural Heritage Day' => [
        'filename' => 'cultural_heritage.jpg',
        'url' => 'https://picsum.photos/800/600?random=3'
    ],
    'Douala Business Expo 2025' => [
        'filename' => 'business_expo.jpg',
        'url' => 'https://picsum.photos/800/600?random=4'
    ],
    'Yaoundé Food Festival' => [
        'filename' => 'food_festival.jpg',
        'url' => 'https://picsum.photos/800/600?random=5'
    ],
    'Bamenda Arts & Crafts Fair' => [
        'filename' => 'arts_crafts.jpg',
        'url' => 'https://picsum.photos/800/600?random=6'
    ]
];

try {
    // Get all events
    $events = $pdo->query("SELECT id, name FROM events")->fetchAll();
    
    echo "<h3>Processing Events:</h3>";
    
    foreach ($events as $event) {
        $event_name = $event['name'];
        $event_id = $event['id'];
        
        if (isset($sample_images[$event_name])) {
            $image_info = $sample_images[$event_name];
            $filename = $image_info['filename'];
            $image_url = $image_info['url'];
            $local_path = $images_dir . $filename;
            
            echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
            echo "<h5>" . htmlspecialchars($event_name) . "</h5>";
            
            // Check if image already exists
            if (file_exists($local_path)) {
                echo "<p>ℹ️ Image already exists: $filename</p>";
            } else {
                // Download image
                echo "<p>📥 Downloading image from: $image_url</p>";
                
                $image_data = @file_get_contents($image_url);
                if ($image_data !== false) {
                    if (file_put_contents($local_path, $image_data)) {
                        echo "<p>✅ Downloaded: $filename</p>";
                    } else {
                        echo "<p>❌ Failed to save: $filename</p>";
                        continue;
                    }
                } else {
                    echo "<p>❌ Failed to download from: $image_url</p>";
                    continue;
                }
            }
            
            // Update database
            $stmt = $pdo->prepare("UPDATE events SET image = ? WHERE id = ?");
            if ($stmt->execute([$filename, $event_id])) {
                echo "<p>✅ Database updated for event ID: $event_id</p>";
            } else {
                echo "<p>❌ Failed to update database for event ID: $event_id</p>";
            }
            
            echo "</div>";
        } else {
            echo "<div style='border: 1px solid #ffc107; padding: 15px; margin: 10px 0; border-radius: 8px; background: #fff3cd;'>";
            echo "<h5>" . htmlspecialchars($event_name) . "</h5>";
            echo "<p>⚠️ No sample image defined for this event</p>";
            echo "</div>";
        }
    }
    
    // Show summary
    $events_with_images = $pdo->query("SELECT COUNT(*) as count FROM events WHERE image IS NOT NULL AND image != ''")->fetch();
    $total_events = $pdo->query("SELECT COUNT(*) as count FROM events")->fetch();
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>📊 Summary</h3>";
    echo "<p><strong>Total Events:</strong> " . $total_events['count'] . "</p>";
    echo "<p><strong>Events with Images:</strong> " . $events_with_images['count'] . "</p>";
    echo "<p><strong>Images Directory:</strong> $images_dir</p>";
    echo "</div>";
    
    // Show events with images
    $events_with_images_list = $pdo->query("SELECT name, image FROM events WHERE image IS NOT NULL AND image != ''")->fetchAll();
    
    if (!empty($events_with_images_list)) {
        echo "<h3>🖼️ Events with Images:</h3>";
        echo "<div class='row'>";
        foreach ($events_with_images_list as $event) {
            echo "<div class='col-md-4 mb-3'>";
            echo "<div style='border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>";
            if (file_exists($images_dir . $event['image'])) {
                echo "<img src='" . $images_dir . $event['image'] . "' style='width: 100%; height: 150px; object-fit: cover;'>";
            }
            echo "<div style='padding: 10px;'>";
            echo "<h6>" . htmlspecialchars($event['name']) . "</h6>";
            echo "<small>Image: " . htmlspecialchars($event['image']) . "</small>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
    }
    
    echo "<div style='margin: 30px 0;'>";
    echo "<a href='index.php' style='background: #007A3D; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🏠 View Homepage</a>";
    echo "<a href='events/list.php' style='background: #CE1126; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🎪 View Events</a>";
    echo "<a href='admin/upload_images.php' style='background: #FECB00; color: #2C1810; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📸 Manage Images</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
