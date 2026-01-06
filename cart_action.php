<?php
session_start();
include "db_conn.php"; // <--- ADDED: Required for the security check

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    
    $tool_id = $_GET['id'];
    $action = $_GET['action'];

    if ($action == 'add') {
        
        // --- SECURITY CHECK: CONCURRENCY (NEW) ---
        // Check if the tool was just taken by someone else 
        // (while this user was looking at the catalog).
        $check_sql = "SELECT tool_id FROM transactions 
                      WHERE tool_id = '$tool_id' 
                      AND status IN ('Pending', 'Approved')";
                      
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            // Tool is logically unavailable
            header("Location: student_catalog.php?error=Sorry! That tool was just requested by another student.");
            exit();
        }
        // -----------------------------------------

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