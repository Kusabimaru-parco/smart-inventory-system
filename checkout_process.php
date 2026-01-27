<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$subject = mysqli_real_escape_string($conn, $_POST['subject']);
$room_no = mysqli_real_escape_string($conn, $_POST['room_no']);
$return_date = mysqli_real_escape_string($conn, $_POST['return_date']);

// --- FIX: UNIFIED ROBUST CONTROL NUMBER GENERATION ---
// Logic: Find the highest ID used today (regardless of deletions) and increment it.

$today_str = date('Y-m-d');

// Get the very last transaction created today based on the Control Number string pattern
$last_sql = "SELECT control_no FROM transactions 
             WHERE control_no LIKE '$today_str-%' 
             ORDER BY transaction_id DESC 
             LIMIT 1";
             
$last_res = mysqli_query($conn, $last_sql);

$next_sequence = 1; // Default if no transactions today

if (mysqli_num_rows($last_res) > 0) {
    $last_row = mysqli_fetch_assoc($last_res);
    $last_control = $last_row['control_no'];
    
    // Extract the sequence number (The part after the last dash)
    $parts = explode('-', $last_control);
    $last_seq = end($parts);
    
    if (is_numeric($last_seq)) {
        $next_sequence = intval($last_seq) + 1;
    }
}

// Generate New Unique ID (e.g., 2026-01-27-5)
$control_no = $today_str . '-' . $next_sequence;
// -----------------------------------------------------

$success_count = 0;

// 2. Loop through cart items
foreach ($_SESSION['cart'] as $tool_name => $details) {
    $qty_needed = intval($details['qty']);
    $safe_name = mysqli_real_escape_string($conn, $tool_name);

    // 3. Find SPECIFIC available tool IDs
    $sql = "SELECT tool_id FROM tools 
            WHERE tool_name = '$safe_name' 
            AND status = 'Available' 
            AND tool_id NOT IN (
                SELECT tool_id FROM transactions WHERE status IN ('Pending', 'Approved', 'Borrowed')
            )
            LIMIT $qty_needed";
    
    $result = mysqli_query($conn, $sql);

    // 4. Create Transactions
    while ($row = mysqli_fetch_assoc($result)) {
        $t_id = $row['tool_id'];
        
        $insert = "INSERT INTO transactions 
                   (user_id, tool_id, borrow_date, return_date, status, control_no, subject, room_no, date_requested) 
                   VALUES 
                   ('$user_id', '$t_id', NULL, '$return_date', 'Pending', '$control_no', '$subject', '$room_no', NOW())";
        
        if (mysqli_query($conn, $insert)) {
            $success_count++;
        }
    }
}

// 5. Cleanup
if ($success_count > 0) {
    unset($_SESSION['cart']);
    header("Location: dashboard.php?msg=Request Submitted! Control No: $control_no");
} else {
    header("Location: student_catalog.php?error=Failed to process request. Items might be unavailable.");
}
?>