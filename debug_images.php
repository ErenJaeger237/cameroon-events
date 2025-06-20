<?php
/**
 * Debug Image Issues
 * Check image paths, permissions, and existence
 */

echo "<h2>🖼️ Image Debug Tool</h2>";

// Check if we're on Railway or local
$is_railway = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'railway.app') !== false;
echo "<p><strong>Environment:</strong> " . ($is_railway ? 'Railway (Production)' : 'Local Development') . "</p>";

// Check directories
$directories_to_check = [
    'assets/',
    'assets/images/',
    'assets/images/events/'
];

echo "<h3>📁 Directory Check:</h3>";
foreach ($directories_to_check as $dir) {
    $exists = file_exists($dir);
    $writable = $exists ? is_writable($dir) : false;
    
    echo "<div style='padding: 10px; margin: 5px 0; border-radius: 5px; background: " . ($exists ? '#d4edda' : '#f8d7da') . ";'>";
    echo "<strong>$dir</strong><br>";
    echo "Exists: " . ($exists ? '✅ Yes' : '❌ No') . "<br>";
    if ($exists) {
        echo "Writable: " . ($writable ? '✅ Yes' : '❌ No') . "<br>";
        echo "Permissions: " . substr(sprintf('%o', fileperms($dir)), -4) . "<br>";
    }
    echo "</div>";
}

// Create directories if they don't exist
echo "<h3>🔧 Creating Missing Directories:</h3>";
foreach ($directories_to_check as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "<p>✅ Created: $dir</p>";
        } else {
            echo "<p>❌ Failed to create: $dir</p>";
        }
    }
}

// Check database connection and get events with images
try {
    if ($is_railway) {
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
        require_once 'includes/db.php';
        global $pdo;
    }
    
    echo "<h3>🗄️ Database Events with Images:</h3>";
    $events = $pdo->query("SELECT id, name, image FROM events WHERE image IS NOT NULL AND image != ''")->fetchAll();
    
    if (empty($events)) {
        echo "<p>❌ No events with images found in database</p>";
    } else {
        echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='padding: 10px; border: 1px solid #ddd;'>Event Name</th>";
        echo "<th style='padding: 10px; border: 1px solid #ddd;'>Image Filename</th>";
        echo "<th style='padding: 10px; border: 1px solid #ddd;'>File Exists</th>";
        echo "<th style='padding: 10px; border: 1px solid #ddd;'>File Size</th>";
        echo "</tr>";
        
        foreach ($events as $event) {
            $image_path = "assets/images/events/" . $event['image'];
            $file_exists = file_exists($image_path);
            $file_size = $file_exists ? filesize($image_path) : 0;
            
            echo "<tr>";
            echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($event['name']) . "</td>";
            echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($event['image']) . "</td>";
            echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . ($file_exists ? '✅ Yes' : '❌ No') . "</td>";
            echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . ($file_exists ? number_format($file_size) . ' bytes' : 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}

// List actual files in the events directory
echo "<h3>📂 Files in events/ directory:</h3>";
$events_dir = 'assets/images/events/';
if (file_exists($events_dir)) {
    $files = scandir($events_dir);
    $image_files = array_filter($files, function($file) {
        return !in_array($file, ['.', '..', '.gitkeep']) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
    });
    
    if (empty($image_files)) {
        echo "<p>❌ No image files found in directory</p>";
    } else {
        echo "<ul>";
        foreach ($image_files as $file) {
            $file_path = $events_dir . $file;
            $file_size = filesize($file_path);
            echo "<li><strong>$file</strong> (" . number_format($file_size) . " bytes)</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p>❌ Events directory does not exist</p>";
}

// Test image URL accessibility
echo "<h3>🌐 Image URL Test:</h3>";
if (!empty($events)) {
    $test_event = $events[0];
    $image_url = "assets/images/events/" . $test_event['image'];
    echo "<p><strong>Testing URL:</strong> $image_url</p>";
    
    if (file_exists($image_url)) {
        echo "<p>✅ File exists on server</p>";
        echo "<img src='$image_url' style='max-width: 200px; max-height: 150px; border: 1px solid #ddd;' alt='Test Image'>";
    } else {
        echo "<p>❌ File does not exist on server</p>";
    }
}

// Create sample image if none exist
echo "<h3>🎨 Create Sample Image:</h3>";
if (file_exists('assets/images/events/')) {
    $sample_image_path = 'assets/images/events/sample_event.jpg';
    
    if (!file_exists($sample_image_path)) {
        // Create a simple colored rectangle as a sample image
        $width = 400;
        $height = 300;
        $image = imagecreate($width, $height);
        
        // Colors (Cameroon flag colors)
        $green = imagecolorallocate($image, 0, 122, 61);
        $red = imagecolorallocate($image, 206, 17, 38);
        $yellow = imagecolorallocate($image, 254, 203, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        
        // Fill background
        imagefill($image, 0, 0, $green);
        
        // Add some design
        imagefilledrectangle($image, 0, 100, $width, 200, $red);
        imagefilledrectangle($image, 0, 200, $width, $height, $yellow);
        
        // Add text
        imagestring($image, 5, 120, 140, 'Sample Event', $white);
        
        if (imagejpeg($image, $sample_image_path, 90)) {
            echo "<p>✅ Created sample image: $sample_image_path</p>";
            echo "<img src='$sample_image_path' style='max-width: 200px; border: 1px solid #ddd;' alt='Sample Image'>";
        } else {
            echo "<p>❌ Failed to create sample image</p>";
        }
        
        imagedestroy($image);
    } else {
        echo "<p>ℹ️ Sample image already exists</p>";
    }
}

echo "<div style='margin: 30px 0; padding: 20px; background: #e7f3ff; border-radius: 10px;'>";
echo "<h4>🔧 Troubleshooting Steps:</h4>";
echo "<ol>";
echo "<li><strong>If on Railway:</strong> Images uploaded via admin panel may not persist due to ephemeral filesystem</li>";
echo "<li><strong>Solution:</strong> Use external image hosting (Cloudinary, AWS S3) or update images via code deployment</li>";
echo "<li><strong>For now:</strong> Use the sample image generator or update database with placeholder URLs</li>";
echo "</ol>";
echo "</div>";

echo "<p><a href='add_sample_images.php' style='background: #007A3D; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🖼️ Add Sample Images</a></p>";
echo "<p><a href='index.php' style='background: #CE1126; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Back to Homepage</a></p>";
?>
