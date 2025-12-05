<?php
session_start();
include "db_conn.php";

if (isset($_POST['barcode'])) {
    
    $barcode = mysqli_real_escape_string($conn, $_POST['barcode']);

    // 1. FIND THE TOOL ID from the Barcode
    $tool_query = mysqli_query($conn, "SELECT tool_id, tool_name FROM tools WHERE barcode = '$barcode'");
    
    if (mysqli_num_rows($tool_query) === 0) {
        header("Location: scan_page.php?error=Barcode Not Found: $barcode");
        exit();
    }

    $tool = mysqli_fetch_assoc($tool_query);
    $tool_id = $tool['tool_id'];
    $tool_name = $tool['tool_name'];

    // 2. FIND ACTIVE TRANSACTION for this tool
    // We look for 'Approved' (ready to pick up) OR 'Borrowed' (ready to return)
    $sql = "SELECT transaction_id, status, return_date, user_id FROM transactions 
            WHERE tool_id = '$tool_id' AND status IN ('Approved', 'Borrowed') 
            ORDER BY transaction_id DESC LIMIT 1";
    
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 0) {
        header("Location: scan_page.php?error=No active request found for: $tool_name");
        exit();
    }

    $trans = mysqli_fetch_assoc($result);
    $trans_id = $trans['transaction_id'];
    $current_status = $trans['status'];
    $user_id = $trans['user_id'];

    // ---------------------------------------------------------
    // LOGIC A: ISSUING THE TOOL (Approved -> Borrowed)
    // ---------------------------------------------------------
    if ($current_status == 'Approved') {
        
        // Update Transaction
        mysqli_query($conn, "UPDATE transactions SET status='Borrowed' WHERE transaction_id='$trans_id'");
        // Update Tool Inventory Status
        mysqli_query($conn, "UPDATE tools SET status='Borrowed' WHERE tool_id='$tool_id'");

        header("Location: scan_page.php?success=ISSUED: $tool_name to Student.");
        exit();
    }

    // ---------------------------------------------------------
    // LOGIC B: RETURNING THE TOOL (Borrowed -> Returned)
    // ---------------------------------------------------------
    if ($current_status == 'Borrowed') {
        
        $msg = "RETURNED: $tool_name.";
        
        // *** PENALTY CHECK ***
        $due_date = $trans['return_date'];
        $today = date('Y-m-d');

        if ($today > $due_date) {
            // Calculate Days Late
            $diff = strtotime($today) - strtotime($due_date);
            $days_late = round($diff / (60 * 60 * 24));
            $points = $days_late * 5; // e.g., 5 points per day

            // 1. Add Penalty Record
            $reason = "Late Return ($days_late days)";
            mysqli_query($conn, "INSERT INTO penalties (user_id, points_added, reason) VALUES ('$user_id', '$points', '$reason')");

            // 2. Add Points to User Profile
            mysqli_query($conn, "UPDATE users SET penalty_points = penalty_points + $points WHERE user_id='$user_id'");

            $msg = "LATE RETURN! $days_late days late. $points Penalty Points added.";
        }

        // Finalize Return Updates
        mysqli_query($conn, "UPDATE transactions SET status='Returned', actual_return_date=NOW() WHERE transaction_id='$trans_id'");
        mysqli_query($conn, "UPDATE tools SET status='Available' WHERE tool_id='$tool_id'");

        header("Location: scan_page.php?success=$msg");
        exit();
    }

} else {
    header("Location: scan_page.php");
}
?>