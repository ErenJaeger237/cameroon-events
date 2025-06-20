<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Test - CameroonEvents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/african-theme.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #fafafa;
            padding: 2rem 0;
        }
        
        .test-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .qr-container {
            text-align: center;
            padding: 2rem;
            border: 2px dashed #ddd;
            border-radius: 15px;
            margin: 1rem 0;
        }
        
        .test-result {
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
        }
        
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h1 style="color: var(--text-primary);">
                <i class="fas fa-qrcode me-3"></i>QR Code Functionality Test
            </h1>
            <p class="text-muted">Testing QR code generation and display</p>
        </div>

        <!-- Test 1: Basic QR Code Generation -->
        <div class="test-card">
            <h3>Test 1: Basic QR Code Generation</h3>
            <p>Testing if QRCode.js library loads and generates a simple QR code.</p>
            
            <div class="qr-container">
                <div id="test1-qr"></div>
                <p class="mt-2">Simple Text QR Code</p>
            </div>
            
            <div id="test1-result" class="test-result"></div>
            
            <button class="btn btn-primary" onclick="runTest1()">
                <i class="fas fa-play me-2"></i>Run Test 1
            </button>
        </div>

        <!-- Test 2: Booking Data QR Code -->
        <div class="test-card">
            <h3>Test 2: Booking Data QR Code</h3>
            <p>Testing QR code with booking data (similar to actual implementation).</p>
            
            <div class="qr-container">
                <div id="test2-qr"></div>
                <p class="mt-2">Booking Data QR Code</p>
            </div>
            
            <div id="test2-result" class="test-result"></div>
            
            <button class="btn btn-primary" onclick="runTest2()">
                <i class="fas fa-play me-2"></i>Run Test 2
            </button>
        </div>

        <!-- Test 3: Modal QR Code (Like History Page) -->
        <div class="test-card">
            <h3>Test 3: Modal QR Code Display</h3>
            <p>Testing QR code in a modal popup (like the booking history page).</p>
            
            <button class="btn btn-success" onclick="showTestModal()">
                <i class="fas fa-qrcode me-2"></i>Show QR Code Modal
            </button>
            
            <div id="test3-result" class="test-result"></div>
        </div>

        <!-- Test 4: Library Loading Check -->
        <div class="test-card">
            <h3>Test 4: Library Status Check</h3>
            <p>Checking if all required libraries are loaded correctly.</p>
            
            <div id="library-status"></div>
            
            <button class="btn btn-info" onclick="checkLibraries()">
                <i class="fas fa-check me-2"></i>Check Libraries
            </button>
        </div>

        <!-- Navigation -->
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-secondary me-2">
                <i class="fas fa-home me-2"></i>Back to Home
            </a>
            <a href="user/history.php" class="btn btn-primary">
                <i class="fas fa-history me-2"></i>Test Real Booking History
            </a>
        </div>
    </div>

    <!-- Test Modal -->
    <div class="modal fade" id="testModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--primary-gradient); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-qrcode me-2"></i>Test QR Code
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="modal-qr"></div>
                    <h6 class="mt-3">Test Event</h6>
                    <p class="text-muted">Booking Reference: TEST123</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    
    <script>
        // Test 1: Basic QR Code
        function runTest1() {
            const container = document.getElementById('test1-qr');
            const result = document.getElementById('test1-result');
            
            container.innerHTML = '';
            result.innerHTML = '';
            
            try {
                QRCode.toCanvas(container, 'Hello CameroonEvents!', {
                    width: 200,
                    margin: 2,
                    color: {
                        dark: '#1e3a8a',
                        light: '#FFFFFF'
                    }
                }, function (error) {
                    if (error) {
                        result.className = 'test-result error';
                        result.innerHTML = '❌ Error: ' + error.message;
                    } else {
                        result.className = 'test-result success';
                        result.innerHTML = '✅ Basic QR code generated successfully!';
                    }
                });
            } catch (e) {
                result.className = 'test-result error';
                result.innerHTML = '❌ Exception: ' + e.message;
            }
        }

        // Test 2: Booking Data QR Code
        function runTest2() {
            const container = document.getElementById('test2-qr');
            const result = document.getElementById('test2-result');
            
            container.innerHTML = '';
            result.innerHTML = '';
            
            const bookingData = {
                booking_id: 123,
                booking_reference: 'TEST123',
                event_name: 'Test Event',
                verification_url: window.location.origin + '/verify.php?ref=TEST123'
            };
            
            try {
                QRCode.toCanvas(container, JSON.stringify(bookingData), {
                    width: 200,
                    margin: 2,
                    color: {
                        dark: '#1e3a8a',
                        light: '#FFFFFF'
                    }
                }, function (error) {
                    if (error) {
                        result.className = 'test-result error';
                        result.innerHTML = '❌ Error: ' + error.message;
                    } else {
                        result.className = 'test-result success';
                        result.innerHTML = '✅ Booking data QR code generated successfully!<br><small>Data: ' + JSON.stringify(bookingData) + '</small>';
                    }
                });
            } catch (e) {
                result.className = 'test-result error';
                result.innerHTML = '❌ Exception: ' + e.message;
            }
        }

        // Test 3: Modal QR Code
        function showTestModal() {
            const container = document.getElementById('modal-qr');
            const result = document.getElementById('test3-result');
            
            container.innerHTML = '';
            result.innerHTML = '';
            
            const testData = {
                booking_id: 456,
                booking_reference: 'MODAL123',
                event_name: 'Modal Test Event',
                verification_url: window.location.origin + '/verify.php?ref=MODAL123'
            };
            
            try {
                QRCode.toCanvas(container, JSON.stringify(testData), {
                    width: 256,
                    margin: 2,
                    color: {
                        dark: '#1e3a8a',
                        light: '#FFFFFF'
                    }
                }, function (error) {
                    if (error) {
                        result.className = 'test-result error';
                        result.innerHTML = '❌ Modal QR Error: ' + error.message;
                    } else {
                        result.className = 'test-result success';
                        result.innerHTML = '✅ Modal QR code generated successfully!';
                    }
                });
                
                // Show modal
                new bootstrap.Modal(document.getElementById('testModal')).show();
                
            } catch (e) {
                result.className = 'test-result error';
                result.innerHTML = '❌ Modal Exception: ' + e.message;
            }
        }

        // Test 4: Library Check
        function checkLibraries() {
            const status = document.getElementById('library-status');
            let html = '<h5>Library Status:</h5><ul>';
            
            // Check Bootstrap
            if (typeof bootstrap !== 'undefined') {
                html += '<li class="text-success">✅ Bootstrap 5 loaded</li>';
            } else {
                html += '<li class="text-danger">❌ Bootstrap 5 not loaded</li>';
            }
            
            // Check QRCode.js
            if (typeof QRCode !== 'undefined') {
                html += '<li class="text-success">✅ QRCode.js loaded</li>';
            } else {
                html += '<li class="text-danger">❌ QRCode.js not loaded</li>';
            }
            
            // Check Canvas support
            const canvas = document.createElement('canvas');
            if (canvas.getContext && canvas.getContext('2d')) {
                html += '<li class="text-success">✅ Canvas support available</li>';
            } else {
                html += '<li class="text-danger">❌ Canvas support not available</li>';
            }
            
            html += '</ul>';
            status.innerHTML = html;
        }

        // Auto-run library check on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkLibraries();
        });
    </script>
</body>
</html>
