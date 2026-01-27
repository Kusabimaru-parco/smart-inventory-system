<?php
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $room_no = mysqli_real_escape_string($conn, $_POST['room_no']);
    $return_date = mysqli_real_escape_string($conn, $_POST['return_date']);
    $tools_requested = isset($_POST['tools']) ? $_POST['tools'] : [];

    if (empty($tools_requested)) {
        header("Location: admin_requests.php?error=No tools selected for walk-in.");
        exit();
    }

    // --- FIX: ROBUST CONTROL NUMBER GENERATION ---
    // Logic: Find the highest ID used today, and increment it.
    
    $today_str = date('Y-m-d');
    
    // Get the very last transaction created today
    // We order by transaction_id DESC to get the latest one
    $last_sql = "SELECT control_no FROM transactions 
                 WHERE DATE(date_requested) = '$today_str' 
                 ORDER BY transaction_id DESC 
                 LIMIT 1";
                 
    $last_res = mysqli_query($conn, $last_sql);
    
    $next_sequence = 1; // Default if no transactions today

    if (mysqli_num_rows($last_res) > 0) {
        $last_row = mysqli_fetch_assoc($last_res);
        $last_control = $last_row['control_no'];
        
        // Extract the sequence number (The part after the last dash)
        // Format is YYYY-MM-DD-SEQ
        $parts = explode('-', $last_control);
        $last_seq = end($parts);
        
        if (is_numeric($last_seq)) {
            $next_sequence = intval($last_seq) + 1;
        }
    }

    // Generate New Unique ID
    $control_no = $today_str . '-' . $next_sequence;
    // ---------------------------------------------

    $success_count = 0;

    // 2. Loop through requested tools
    foreach ($tools_requested as $tool_name => $qty) {
        $safe_name = mysqli_real_escape_string($conn, $tool_name);
        $qty_needed = intval($qty);

        // 3. Find Available Tools
        $sql = "SELECT tool_id FROM tools 
                WHERE tool_name = '$safe_name' 
                AND status = 'Available' 
                AND tool_id NOT IN (SELECT tool_id FROM transactions WHERE status IN ('Pending', 'Approved', 'Borrowed'))
                LIMIT $qty_needed";
        
        $result = mysqli_query($conn, $sql);

        // 4. Create Transactions (Status: APPROVED immediately)
        while ($row = mysqli_fetch_assoc($result)) {
            $t_id = $row['tool_id'];
            
            $insert = "INSERT INTO transactions 
                       (user_id, tool_id, borrow_date, return_date, status, control_no, subject, room_no, date_requested) 
                       VALUES 
                       ('$user_id', '$t_id', NULL, '$return_date', 'Approved', '$control_no', '$subject', '$room_no', NOW())";
            
            if (mysqli_query($conn, $insert)) {
                $success_count++;
            }
        }
    }

    if ($success_count > 0) {
        header("Location: admin_requests.php?msg=Walk-in request processed! Control No: $control_no");
    } else {
        header("Location: admin_requests.php?error=Failed to process. Items might be unavailable.");
    }

} else {
    header("Location: admin_requests.php");
}
?>