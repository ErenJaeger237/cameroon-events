<?php
/**
 * Fix Image URLs - Use External URLs for Railway Compatibility
 * Railway has ephemeral filesystem, so uploaded files don't persist
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

echo "<h2>🔧 Fixing Event Images with External URLs</h2>";
echo "<p>Using external image URLs for Railway compatibility...</p>";

// High-quality event images from Unsplash (free to use)
$event_images = [
    'Cameroon Music Festival 2024' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&h=600&fit=crop&crop=center',
    'Tech Innovation Summit Cameroon' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=600&fit=crop&crop=center',
    'Cameroon Cultural Heritage Day' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800&h=600&fit=crop&crop=center',
    'Douala Business Expo 2025' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&h=600&fit=crop&crop=center',
    'Yaoundé Food Festival' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&h=600&fit=crop&crop=center',
    'Bamenda Arts & Crafts Fair' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop&crop=center'
];

// Additional generic event images
$generic_images = [
    'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&h=600&fit=crop&crop=center', // Concert
    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&h=600&fit=crop&crop=center', // Conference
    'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=800&h=600&fit=crop&crop=center', // Festival
    'https://images.unsplash.com/photo-1505236858219-8359eb29e329?w=800&h=600&fit=crop&crop=center', // Workshop
    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop&crop=center', // Business
    'https://images.unsplash.com/photo-1414016642750-7fdd78dc33d9?w=800&h=600&fit=crop&crop=center'  // Cultural
];

try {
    // Get all events
    $events = $pdo->query("SELECT id, name FROM events ORDER BY name")->fetchAll();
    
    echo "<h3>📸 Updating Event Images:</h3>";
    
    $updated_count = 0;
    
    foreach ($events as $event) {
        $event_name = $event['name'];
        $event_id = $event['id'];
        
        // Check if we have a specific image for this event
        if (isset($event_images[$event_name])) {
            $image_url = $event_images[$event_name];
        } else {
            // Use a random generic image
            $image_url = $generic_images[array_rand($generic_images)];
        }
        
        // Update the database with the external URL
        $stmt = $pdo->prepare("UPDATE events SET image = ? WHERE id = ?");
        if ($stmt->execute([$image_url, $event_id])) {
            echo "<div style='border: 1px solid #d4edda; padding: 15px; margin: 10px 0; border-radius: 8px; background: #d4edda;'>";
            echo "<h5>✅ " . htmlspecialchars($event_name) . "</h5>";
            echo "<p><strong>Image URL:</strong> <a href='$image_url' target='_blank'>$image_url</a></p>";
            echo "<img src='$image_url' style='max-width: 200px; max-height: 150px; border-radius: 8px; object-fit: cover;' alt='Event Image'>";
            echo "</div>";
            $updated_count++;
        } else {
            echo "<div style='border: 1px solid #f8d7da; padding: 15px; margin: 10px 0; border-radius: 8px; background: #f8d7da;'>";
            echo "<h5>❌ Failed to update: " . htmlspecialchars($event_name) . "</h5>";
            echo "</div>";
        }
    }
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>🎉 Update Complete!</h3>";
    echo "<p><strong>Events Updated:</strong> $updated_count out of " . count($events) . "</p>";
    echo "<p><strong>Image Source:</strong> High-quality images from Unsplash</p>";
    echo "<p><strong>Benefits:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Images persist after logout/restart</li>";
    echo "<li>✅ Fast loading from CDN</li>";
    echo "<li>✅ Professional quality photos</li>";
    echo "<li>✅ No storage space used</li>";
    echo "</ul>";
    echo "</div>";
    
    // Show updated events
    $updated_events = $pdo->query("SELECT name, image FROM events WHERE image IS NOT NULL AND image != '' ORDER BY name")->fetchAll();
    
    echo "<h3>🖼️ Updated Events Preview:</h3>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    foreach (array_slice($updated_events, 0, 6) as $event) {
        echo "<div style='border: 1px solid #ddd; border-radius: 10px; overflow: hidden; background: white;'>";
        echo "<img src='" . htmlspecialchars($event['image']) . "' style='width: 100%; height: 150px; object-fit: cover;' alt='Event Image'>";
        echo "<div style='padding: 15px;'>";
        echo "<h6>" . htmlspecialchars($event['name']) . "</h6>";
        echo "</div>";
        echo "</div>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Update the image display logic explanation
echo "<div style='margin: 30px 0; padding: 20px; background: #e7f3ff; border-radius: 10px;'>";
echo "<h4>🔧 How This Fix Works:</h4>";
echo "<ol>";
echo "<li><strong>External URLs:</strong> Images are now stored as URLs pointing to external services</li>";
echo "<li><strong>Persistent:</strong> URLs don't disappear when Railway restarts</li>";
echo "<li><strong>Fast:</strong> Images load from optimized CDNs</li>";
echo "<li><strong>Professional:</strong> High-quality stock photos from Unsplash</li>";
echo "</ol>";
echo "<p><strong>Note:</strong> The homepage and events pages will now show these external images consistently.</p>";
echo "</div>";

echo "<div style='margin: 30px 0;'>";
echo "<a href='index.php' style='background: #007A3D; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🏠 View Homepage</a>";
echo "<a href='events/list.php' style='background: #CE1126; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🎪 View Events</a>";
echo "<a href='debug_images.php' style='background: #FECB00; color: #2C1810; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Debug Images</a>";
echo "</div>";
?>
