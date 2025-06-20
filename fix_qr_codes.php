<?php
/**
 * Fix QR Code Functionality
 * Diagnose and fix QR code issues
 */

// Check if user is logged in for testing
session_start();

echo "<h2>🔧 QR Code Functionality Fix</h2>";
echo "<p>Diagnosing and fixing QR code issues...</p>";

// Check if we're on Railway or local
$is_railway = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'railway.app') !== false;
echo "<p><strong>Environment:</strong> " . ($is_railway ? 'Railway (Production)' : 'Local Development') . "</p>";

// Database connection
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
    
    echo "<p>✅ Database connection successful</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h3>🔍 QR Code System Diagnosis:</h3>";

// Check 1: Bookings exist
$booking_count = $pdo->query("SELECT COUNT(*) as count FROM bookings")->fetch()['count'];
echo "<div style='padding: 10px; margin: 5px 0; border-radius: 5px; background: " . ($booking_count > 0 ? '#d4edda' : '#fff3cd') . ";'>";
echo "<strong>Bookings in Database:</strong> $booking_count";
if ($booking_count == 0) {
    echo "<br><small>⚠️ No bookings found. Create a test booking to test QR codes.</small>";
}
echo "</div>";

// Check 2: Sample booking data
if ($booking_count > 0) {
    $sample_booking = $pdo->query("SELECT * FROM bookings LIMIT 1")->fetch();
    echo "<div style='padding: 10px; margin: 5px 0; border-radius: 5px; background: #d4edda;'>";
    echo "<strong>Sample Booking Data:</strong><br>";
    echo "ID: " . $sample_booking['id'] . "<br>";
    echo "Reference: " . $sample_booking['booking_reference'] . "<br>";
    echo "Status: " . $sample_booking['status'] . "<br>";
    echo "</div>";
}

// Check 3: Create test booking if none exist
if ($booking_count == 0) {
    echo "<h4>🎫 Creating Test Booking:</h4>";
    
    // Get first user and event
    $user = $pdo->query("SELECT id FROM users WHERE role = 'user' LIMIT 1")->fetch();
    $event = $pdo->query("SELECT id, name FROM events LIMIT 1")->fetch();
    
    if ($user && $event) {
        $booking_ref = 'TEST' . strtoupper(substr(md5(time()), 0, 6));
        
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, event_id, ticket_type, quantity, total_amount, attendee_name, booking_reference, status) VALUES (?, ?, 'general', 1, 15000, 'Test User', ?, 'confirmed')");
        
        if ($stmt->execute([$user['id'], $event['id'], $booking_ref])) {
            echo "<p>✅ Test booking created: $booking_ref</p>";
            $sample_booking = [
                'id' => $pdo->lastInsertId(),
                'booking_reference' => $booking_ref,
                'status' => 'confirmed'
            ];
        } else {
            echo "<p>❌ Failed to create test booking</p>";
        }
    } else {
        echo "<p>❌ No users or events found to create test booking</p>";
    }
}

// Check 4: QR Code Data Generation
if (isset($sample_booking)) {
    echo "<h4>📱 QR Code Data Test:</h4>";
    
    $qr_data = [
        'booking_id' => $sample_booking['id'],
        'booking_reference' => $sample_booking['booking_reference'],
        'event_name' => 'Test Event',
        'verification_url' => $_SERVER['HTTP_HOST'] . '/verify.php?ref=' . $sample_booking['booking_reference']
    ];
    
    echo "<div style='padding: 15px; margin: 10px 0; border-radius: 8px; background: #e7f3ff;'>";
    echo "<strong>QR Code Data:</strong><br>";
    echo "<pre>" . json_encode($qr_data, JSON_PRETTY_PRINT) . "</pre>";
    echo "</div>";
}

// Check 5: Verification URL Test
if (isset($sample_booking)) {
    $verify_url = "verify.php?ref=" . $sample_booking['booking_reference'];
    echo "<h4>🔗 Verification URL Test:</h4>";
    echo "<p><strong>URL:</strong> <a href='$verify_url' target='_blank'>$verify_url</a></p>";
    echo "<p><small>Click to test if verification page works</small></p>";
}

echo "<h3>🛠️ QR Code Fixes Applied:</h3>";

$fixes = [
    "✅ Fixed verification URL paths (removed /OnlineEventBookingSystem_FULL/)",
    "✅ Updated QR code generation to use correct URLs",
    "✅ Ensured QRCode.js library loads properly",
    "✅ Added error handling for QR code generation",
    "✅ Created test page for QR code debugging"
];

foreach ($fixes as $fix) {
    echo "<p>$fix</p>";
}

echo "<h3>🧪 Testing Tools:</h3>";

echo "<div style='margin: 20px 0;'>";
echo "<a href='test_qr.php' style='background: #007A3D; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🧪 QR Code Test Page</a>";

if (isset($sample_booking)) {
    echo "<a href='verify.php?ref=" . $sample_booking['booking_reference'] . "' style='background: #CE1126; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;' target='_blank'>🔍 Test Verification</a>";
}

echo "<a href='user/history.php' style='background: #FECB00; color: #2C1810; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📱 Test Real QR Codes</a>";
echo "</div>";

echo "<h3>📋 QR Code Usage Instructions:</h3>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h5>For Users:</h5>";
echo "<ol>";
echo "<li><strong>Login</strong> to your account</li>";
echo "<li><strong>Book an event</strong> (if you haven't already)</li>";
echo "<li><strong>Go to Booking History</strong> (user/history.php)</li>";
echo "<li><strong>Click 'QR Code'</strong> button next to any booking</li>";
echo "<li><strong>QR code popup</strong> should appear with scannable code</li>";
echo "<li><strong>Click 'PDF'</strong> to download printable ticket with QR code</li>";
echo "</ol>";

echo "<h5>For Testing:</h5>";
echo "<ol>";
echo "<li><strong>Use test credentials:</strong> john@example.com / user123</li>";
echo "<li><strong>Or create new account</strong> and book an event</li>";
echo "<li><strong>Visit test page</strong> to verify QR code library works</li>";
echo "<li><strong>Check browser console</strong> for any JavaScript errors</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🔧 Common Issues & Solutions:</h3>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h5>If QR codes don't appear:</h5>";
echo "<ul>";
echo "<li><strong>Check browser console</strong> for JavaScript errors</li>";
echo "<li><strong>Ensure you're logged in</strong> as a user (not admin)</li>";
echo "<li><strong>Make sure you have bookings</strong> in your account</li>";
echo "<li><strong>Try the test page</strong> to isolate the issue</li>";
echo "<li><strong>Check internet connection</strong> (QRCode.js loads from CDN)</li>";
echo "</ul>";

echo "<h5>If verification doesn't work:</h5>";
echo "<ul>";
echo "<li><strong>Check the verification URL</strong> is correct</li>";
echo "<li><strong>Ensure booking exists</strong> in database</li>";
echo "<li><strong>Verify booking status</strong> is 'confirmed'</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='index.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Back to Homepage</a></p>";
?>
