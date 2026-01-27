<?php
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['confirm_lost'])) {
    $transaction_id = mysqli_real_escape_string($conn, $_POST['transaction_id']);
    $tool_id = mysqli_real_escape_string($conn, $_POST['tool_id']);
    $control_no = mysqli_real_escape_string($conn, $_POST['control_no']);
    $processor = $_SESSION['name'];

    // 1. Fetch Transaction Details (To get User ID and Tool Name for the record)
    $trans_query = mysqli_query($conn, "SELECT t.user_id, tl.tool_name 
                                        FROM transactions t 
                                        JOIN tools tl ON t.tool_id = tl.tool_id 
                                        WHERE t.transaction_id = '$transaction_id' LIMIT 1");
    
    if (mysqli_num_rows($trans_query) == 0) {
        header("Location: scan_page.php?control_no=$control_no&error=Transaction not found.");
        exit();
    }

    $trans_data = mysqli_fetch_assoc($trans_query);
    $user_id = $trans_data['user_id'];
    $tool_name = $trans_data['tool_name'];

    // --- APPLY PENALTY LOGIC ---
    $penalty_points = 30; // Fixed penalty for lost item
    $reason = "Lost Item: $tool_name";

    // A. Insert Penalty Record
    $sql_penalty = "INSERT INTO penalties (user_id, points, reason) VALUES ('$user_id', '$penalty_points', '$reason')";
    mysqli_query($conn, $sql_penalty);

    // B. Update User Profile Points
    $sql_update_user = "UPDATE users SET penalty_points = penalty_points + $penalty_points WHERE user_id='$user_id'";
    mysqli_query($conn, $sql_update_user);

    // C. Check for Ban (Limit 60)
    $ban_msg = "";
    $user_check = mysqli_query($conn, "SELECT penalty_points FROM users WHERE user_id='$user_id'");
    $user_row = mysqli_fetch_assoc($user_check);

    if ($user_row['penalty_points'] >= 60) {
        mysqli_query($conn, "UPDATE users SET account_status = 'restricted' WHERE user_id='$user_id'");
        $ban_msg = " ⛔ ACCOUNT BANNED.";
    }
    // ---------------------------

    // 2. Update Transaction: Mark as 'Lost' and set return date (closing the transaction)
    $sql_trans = "UPDATE transactions 
                  SET status = 'Lost', 
                      actual_return_date = NOW(), 
                      processed_by = '$processor' 
                  WHERE transaction_id = '$transaction_id'";

    // 3. Update Tool: Mark as 'Lost' (Removes it from Available pool)
    $sql_tool = "UPDATE tools SET status = 'Lost' WHERE tool_id = '$tool_id'";

    if (mysqli_query($conn, $sql_trans) && mysqli_query($conn, $sql_tool)) {
        $msg = "Tool marked as LOST. 30 Penalty Points applied.$ban_msg";
        header("Location: scan_page.php?control_no=$control_no&msg=$msg");
    } else {
        $error = "Error updating database: " . mysqli_error($conn);
        header("Location: scan_page.php?control_no=$control_no&error=$error");
    }
} else {
    header("Location: dashboard.php");
}
?>