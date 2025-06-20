<?php
require_once '../includes/db.php';

// Destroy session and redirect
session_destroy();
redirect('../index.php', 'You have been logged out successfully.', 'success');
?>
