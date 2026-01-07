<?php
session_start();
include "db_conn.php";

if (isset($_POST['barcode'])) {
    
    $barcode = mysqli_real_escape_string($conn, $_POST['barcode']);

    // --- 0. IDENTIFY PROCESSOR (Who is scanning?) ---
    $processor_name = "System"; // Default fallback

    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == 'admin') {
            $processor_name = "Admin"; // Per your request: Record Admin as just "Admin"
        } elseif ($_SESSION['role'] == 'student_assistant') {
            // For SAs, record their actual Full Name
            $processor_name = mysqli_real_escape_string($conn, $_SESSION['name']); 
        }
    }
    // ------------------------------------------------

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
        // ADDED: processed_by='$processor_name'
        $sql_issue = "UPDATE transactions 
                      SET status='Borrowed', actual_borrow_date=NOW(), processed_by='$processor_name' 
                      WHERE transaction_id='$trans_id'";
        
        mysqli_query($conn, $sql_issue);

        // Update Tool Inventory Status
        mysqli_query($conn, "UPDATE tools SET status='Borrowed' WHERE tool_id='$tool_id'");

        header("Location: scan_page.php?success=ISSUED: $tool_name to Student (By $processor_name).");
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

        // Only calculate penalty if today is strictly GREATER than due date
        if ($today > $due_date) {
            // 1. Calculate the difference in seconds
            $diff = strtotime($today) - strtotime($due_date);
    
            // 2. Convert to days
            $days_late = ceil($diff / (60 * 60 * 24));
    
            // 3. Calculate Points (5 points per day)
            $points = $days_late * 5; 

            // 4. Add Penalty Record
            $reason = "Late Return ($days_late days)";
            $sql_penalty = "INSERT INTO penalties (user_id, points, reason) VALUES ('$user_id', '$points', '$reason')";
            
            if (!mysqli_query($conn, $sql_penalty)) {
                 header("Location: scan_page.php?error=Penalty Error: " . mysqli_error($conn));
                 exit();
            }

            // 5. Update User Profile
            $sql_update = "UPDATE users SET penalty_points = penalty_points + $points WHERE user_id='$user_id'";
            mysqli_query($conn, $sql_update);

            // 6. Check for Ban (Immediate restriction)
            $sql_check_ban = "UPDATE users SET account_status = 'restricted' WHERE user_id='$user_id' AND penalty_points >= 60";
            mysqli_query($conn, $sql_check_ban);

            $msg = "LATE RETURN! $days_late days late. $points Penalty Points added.";
        }

        // Finalize Return Updates
        // ADDED: processed_by='$processor_name'
        $sql_return = "UPDATE transactions 
                       SET status='Returned', actual_return_date=NOW(), processed_by='$processor_name' 
                       WHERE transaction_id='$trans_id'";
                       
        mysqli_query($conn, $sql_return);
        
        mysqli_query($conn, "UPDATE tools SET status='Available' WHERE tool_id='$tool_id'");

        header("Location: scan_page.php?success=$msg (Processed by $processor_name)");
        exit();
    }

} else {
    header("Location: scan_page.php");
}
?>