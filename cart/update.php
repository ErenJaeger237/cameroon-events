<?php
require_once '../includes/db.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php', 'Please log in to update your cart.', 'info');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $user_id = getCurrentUserId();
    
    // Validate input
    if ($cart_id > 0 && $quantity > 0 && $quantity <= 10) {
        // Verify cart item belongs to user
        $cart_item = fetchOne("SELECT id FROM cart WHERE id = ? AND user_id = ?", [$cart_id, $user_id]);
        
        if ($cart_item) {
            // Update quantity
            $result = executeQuery("UPDATE cart SET quantity = ? WHERE id = ?", [$quantity, $cart_id]);
            
            if ($result) {
                redirect('view.php', 'Cart updated successfully!', 'success');
            } else {
                redirect('view.php', 'Failed to update cart.', 'error');
            }
        } else {
            redirect('view.php', 'Cart item not found.', 'error');
        }
    } else {
        redirect('view.php', 'Invalid quantity.', 'error');
    }
} else {
    redirect('view.php');
}
?>
