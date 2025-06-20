<?php
require_once '../includes/db.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php', 'Please log in to modify your cart.', 'info');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    $user_id = getCurrentUserId();
    
    if ($cart_id > 0) {
        // Verify cart item belongs to user
        $cart_item = fetchOne("SELECT id FROM cart WHERE id = ? AND user_id = ?", [$cart_id, $user_id]);
        
        if ($cart_item) {
            // Remove item
            $result = executeQuery("DELETE FROM cart WHERE id = ?", [$cart_id]);
            
            if ($result) {
                redirect('view.php', 'Item removed from cart.', 'success');
            } else {
                redirect('view.php', 'Failed to remove item.', 'error');
            }
        } else {
            redirect('view.php', 'Cart item not found.', 'error');
        }
    } else {
        redirect('view.php', 'Invalid request.', 'error');
    }
} else {
    redirect('view.php');
}
?>
