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

// --- 1. GENERATE SEQUENTIAL CONTROL NUMBER (YYYY-MM-DD-COUNT) ---

$today = date('Y-m-d');

// Count how many UNIQUE requests (control numbers) exist for TODAY
$count_sql = "SELECT COUNT(DISTINCT control_no) as daily_count 
              FROM transactions 
              WHERE DATE(date_requested) = '$today'";

$count_res = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_res);

// Increment by 1
$next_sequence = $count_row['daily_count'] + 1;

// Format: 2026-01-27-1
$control_no = $today . '-' . $next_sequence;

// ---------------------------------------------------------------

$success_count = 0;

// 2. Loop through cart items
foreach ($_SESSION['cart'] as $tool_name => $details) {
    $qty_needed = intval($details['qty']);
    $safe_name = mysqli_real_escape_string($conn, $tool_name);

    // 3. Find SPECIFIC available tool IDs for this name
    // Logic: Get X tools that are NOT currently Pending/Approved/Borrowed
    $sql = "SELECT tool_id FROM tools 
            WHERE tool_name = '$safe_name' 
            AND status = 'Available' 
            AND tool_id NOT IN (
                SELECT tool_id FROM transactions WHERE status IN ('Pending', 'Approved', 'Borrowed')
            )
            LIMIT $qty_needed";
    
    $result = mysqli_query($conn, $sql);

    // 4. Create a Transaction for EACH found tool
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
    // If tools ran out while browsing
    header("Location: student_catalog.php?error=Failed to process request. Items might be unavailable.");
}
?>