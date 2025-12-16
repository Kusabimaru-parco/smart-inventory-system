<?php
session_start();
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    
    $user_id = $_SESSION['user_id'];
    $return_date = $_POST['return_date'];
    $borrow_date = date('Y-m-d');
    $cart_items = $_SESSION['cart'];

    // 1. Security Check: Is User Banned?
    $check_user = mysqli_query($conn, "SELECT penalty_points FROM users WHERE user_id='$user_id'");
    $user_data = mysqli_fetch_assoc($check_user);
    if ($user_data['penalty_points'] >= 60) {
        die("Account Restricted. Checkout failed.");
    }

    // 2. Loop Insert
    foreach ($cart_items as $tool_id) {
        // Double check status is still 'Available' (in case someone else borrowed it just now)
        $check_tool = mysqli_query($conn, "SELECT status FROM tools WHERE tool_id='$tool_id'");
        $tool_data = mysqli_fetch_assoc($check_tool);
        
        if ($tool_data['status'] == 'Available') {
            $sql = "INSERT INTO transactions (user_id, tool_id, borrow_date, return_date, status) 
                    VALUES ('$user_id', '$tool_id', '$borrow_date', '$return_date', 'Pending')";
            mysqli_query($conn, $sql);
        }
    }

    // 3. Clear Cart & Redirect
    unset($_SESSION['cart']);
    header("Location: dashboard.php?msg=Batch Request Submitted Successfully!");
    exit();

} else {
    header("Location: cart.php");
}
?>