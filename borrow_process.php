<?php
session_start();
include "db_conn.php";

if (isset($_POST['tool_id']) && isset($_POST['return_date'])) {
    
    $user_id = $_SESSION['user_id'];
    $tool_id = $_POST['tool_id'];
    $borrow_date = date('Y-m-d'); // Today
    $return_date = $_POST['return_date'];

    // 1. DOUBLE CHECK: Is user banned? (Security)
    $check_user = mysqli_query($conn, "SELECT penalty_points FROM users WHERE user_id='$user_id'");
    $user_data = mysqli_fetch_assoc($check_user);
    
    if ($user_data['penalty_points'] >= 60) {
        die("Error: Your account is restricted. You cannot borrow tools.");
    }

    // 2. INSERT TRANSACTION (Status = Pending)
    $sql = "INSERT INTO transactions (user_id, tool_id, borrow_date, return_date, status) 
            VALUES ('$user_id', '$tool_id', '$borrow_date', '$return_date', 'Pending')";

    if (mysqli_query($conn, $sql)) {
        header("Location: student_catalog.php?msg=Request Submitted! Waiting for Admin Approval.");
    } else {
        echo "Error: " . mysqli_error($conn);
    }

} else {
    header("Location: student_catalog.php");
}
?>