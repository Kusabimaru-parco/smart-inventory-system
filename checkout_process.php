<?php
session_start();
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    
    $user_id = $_SESSION['user_id'];
    $return_date = $_POST['return_date'];
    
    // --- 1. NEW: CAPTURE SUBJECT AND ROOM ---
    // We use mysqli_real_escape_string to prevent SQL injection issues with special characters
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $room_no = mysqli_real_escape_string($conn, $_POST['room_no']);
    // ----------------------------------------
    
    // Use the current date and time for the request
    $borrow_date = date('Y-m-d'); 
    $date_requested = date('Y-m-d H:i:s'); // Exact timestamp for ordering

    $cart_items = $_SESSION['cart'];

    // 2. Security Check: Is User Banned?
    $check_user = mysqli_query($conn, "SELECT penalty_points FROM users WHERE user_id='$user_id'");
    $user_data = mysqli_fetch_assoc($check_user);
    if ($user_data['penalty_points'] >= 60) {
        die("Account Restricted. Checkout failed.");
    }

    // --- 3. GENERATE UNIQUE CONTROL NUMBER (Year-Month-Day-Sequence) ---
    // Format: 2026-01-08-1 (Reset sequence daily)
    
    $today_str = date('Y-m-d'); 

    $count_sql = "SELECT COUNT(*) as total FROM transactions WHERE DATE(date_requested) = '$today_str'";
    $count_res = mysqli_query($conn, $count_sql);
    $count_row = mysqli_fetch_assoc($count_res);

    // Increment by 1 for the new transaction
    $new_sequence = $count_row['total'] + 1;

    // Create the Control No string
    $control_no = $today_str . "-" . $new_sequence;
    // -------------------------------------------------------------------

    // 4. Loop Insert
    foreach ($cart_items as $tool_id) {
        
        // Double check status is still 'Available' logically (not taken by pending/approved)
        $check_tool_sql = "SELECT tool_id FROM transactions 
                           WHERE tool_id = '$tool_id' 
                           AND status IN ('Pending', 'Approved')";
        $check_tool = mysqli_query($conn, $check_tool_sql);
        
        if (mysqli_num_rows($check_tool) == 0) {
            
            // --- UPDATED SQL QUERY ---
            // Added 'subject' and 'room_no' to columns and values
            $sql = "INSERT INTO transactions (user_id, tool_id, borrow_date, return_date, status, date_requested, control_no, subject, room_no) 
                    VALUES ('$user_id', '$tool_id', '$borrow_date', '$return_date', 'Pending', '$date_requested', '$control_no', '$subject', '$room_no')";
            
            if (!mysqli_query($conn, $sql)) {
                // Optional: Handle error (e.g., log it)
            }
        }
    }

    // 5. Clear Cart & Redirect
    unset($_SESSION['cart']);
    header("Location: dashboard.php?msg=Request Submitted! Control No: $control_no");
    exit();

} else {
    header("Location: cart.php");
}
?>