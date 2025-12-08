<?php
session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    
    $tool_id = $_GET['id'];
    $action = $_GET['action'];

    if ($action == 'add') {
        // Check if already in cart
        if (!in_array($tool_id, $_SESSION['cart'])) {
            $_SESSION['cart'][] = $tool_id;
            $msg = "Tool added to cart!";
        } else {
            $msg = "Tool is already in your cart.";
        }
    }

    if ($action == 'remove') {
        // Find position and remove
        $key = array_search($tool_id, $_SESSION['cart']);
        if ($key !== false) {
            unset($_SESSION['cart'][$key]);
            // Re-index array
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
        $msg = "Item removed.";
        header("Location: cart.php?msg=$msg");
        exit();
    }
}

// Redirect back to catalog
header("Location: student_catalog.php?msg=$msg");
?>