<?php
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    
    $trans_id = $_GET['id'];
    $action = $_GET['action'];
    $new_status = "";

    if ($action == 'approve') {
        $new_status = "Approved";
    } else if ($action == 'decline') {
        $new_status = "Declined";
    } else {
        die("Invalid Action");
    }

    // Update the Transaction Table
    $sql = "UPDATE transactions SET status='$new_status' WHERE transaction_id='$trans_id'";

    if (mysqli_query($conn, $sql)) {
        // (Optional) Here is where you would send the Email Notification logic later
        header("Location: admin_requests.php?msg=Request has been $new_status.");
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }

} else {
    header("Location: admin_requests.php");
}
?>