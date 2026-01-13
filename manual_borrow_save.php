<?php
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $room_no = mysqli_real_escape_string($conn, $_POST['room_no']);
    $return_date = mysqli_real_escape_string($conn, $_POST['return_date']);
    
    // Tools Array from Cart
    if(!isset($_POST['tool_ids'])) {
        header("Location: admin_requests.php?error=No tools selected.");
        exit();
    }
    $tool_ids = $_POST['tool_ids'];

    // --- GENERATE CONTROL NUMBER (Format: YYYY-MM-DD-Sequence) ---
    $today_date = date('Y-m-d'); // Example: 2026-01-14

    // 1. Count how many distinct transactions exist for TODAY
    // We check 'borrow_date' because that's what we are inserting right now.
    $count_sql = "SELECT COUNT(DISTINCT control_no) as total 
                  FROM transactions 
                  WHERE DATE(borrow_date) = '$today_date'";
    
    $count_res = mysqli_query($conn, $count_sql);
    $count_row = mysqli_fetch_assoc($count_res);
    
    $sequence = $count_row['total'] + 1; // Increment count by 1
    
    // 2. Combine to form Control No (e.g., 2026-01-14-1)
    $control_no = $today_date . "-" . $sequence;

    // -------------------------------------------------------------

    // Validate Student Exists
    $check_stu = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$student_id'");
    if(mysqli_num_rows($check_stu) == 0) {
        header("Location: admin_requests.php?error=Student ID not found.");
        exit();
    }

    // Process Logic (Approved Status)
    $processed_by = $_SESSION['name'];
    $success_count = 0;

    foreach ($tool_ids as $tid) {
        $tid = mysqli_real_escape_string($conn, $tid);
        
        $sql = "INSERT INTO transactions (user_id, tool_id, subject, room_no, borrow_date, return_date, status, control_no, processed_by) 
                VALUES ('$student_id', '$tid', '$subject', '$room_no', NOW(), '$return_date', 'Approved', '$control_no', '$processed_by')";
        
        if(mysqli_query($conn, $sql)) {
            // Update Tool Status to 'Pending' (Reserved)
            mysqli_query($conn, "UPDATE tools SET status='Pending' WHERE tool_id='$tid'");
            $success_count++;
        }
    }

    if ($success_count > 0) {
        header("Location: admin_requests.php?msg=Walk-in request successful! Control No: $control_no");
    } else {
        header("Location: admin_requests.php?error=Failed to process request.");
    }

} else {
    header("Location: admin_requests.php");
}
?>